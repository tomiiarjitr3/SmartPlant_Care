<?php
// ═══════════════════════════════════════════════════════════════
//  ai_assistant.php — SmartPlant CARE
//  Controlador para la integración con Gemini API
// ═══════════════════════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// INGRESÁ TU API KEY DE GEMINI ACÁ:
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
$GEMINI_API_KEY = "AIzaSyDB1CN6hiFKeVp1hZeL4tFEXsubFUvk6dI";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::connect();
    $usuario_id = (int)$_SESSION['usuario_id'];
    $planta_id = isset($_POST['planta_id']) ? (int)$_POST['planta_id'] : 0;
    $mensaje_usuario = trim($_POST['mensaje'] ?? '');

    if (empty($mensaje_usuario) && empty($_FILES['imagen']['name'])) {
        echo json_encode(['error' => 'El mensaje no puede estar vacío']);
        exit;
    }

    // ── 1. Obtener contexto de la planta ────────────────────────────
    $contexto = "";
    if ($planta_id) {
        $stmt = $db->prepare("SELECT * FROM plantas WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $planta_id, $usuario_id);
        $stmt->execute();
        $planta = $stmt->get_result()->fetch_assoc();

        if ($planta) {
            $stmt = $db->prepare("
                SELECT l.* FROM lecturas_sensores l
                WHERE l.planta_id = ?
                ORDER BY l.creada_en DESC LIMIT 1
            ");
            $stmt->bind_param("i", $planta_id);
            $stmt->execute();
            $ultima = $stmt->get_result()->fetch_assoc();

            $contexto = "Eres un experto bot de cuidado de plantas de SmartPlant CARE. Estás asistiendo a un usuario con su planta llamada '{$planta['nombre']}' (Especie: {$planta['especie']}).\n";
            if ($ultima) {
                $contexto .= "Estado actual en vivo de los sensores de la planta:\n";
                $contexto .= "- Humedad del suelo: {$ultima['humedad_suelo']}%\n";
                $contexto .= "- Temperatura: {$ultima['temperatura']}°C\n";
                $contexto .= "- Luz ambiental: {$ultima['luz_ambiental']} lx\n";
                $contexto .= "- Nivel del tanque de agua: {$ultima['nivel_tanque']}%\n";
                $contexto .= "- Batería del dispositivo: {$ultima['bateria']}%\n";
            } else {
                $contexto .= "Aún no hay lecturas de sensores para esta planta.\n";
            }
            $contexto .= "El usuario te hará una pregunta. Responde de forma concisa, amigable, con tono experto pero muy accesible y cercano. Usa emojis relevantes. NO uses formato markdown complejo (ni negritas ni asteriscos excesivos), solo texto simple con algún salto de línea. Si te preguntan por el estado, analiza los datos de los sensores y da una conclusión. La humedad ideal suele ser 40-60%, temp 20-28C.\n";
        }
    }

    if (empty($contexto)) {
        $contexto = "Eres un experto bot de cuidado de plantas de SmartPlant CARE. Responde de forma concisa, amigable y experta. No uses markdown excesivo.";
    }

    // ── 2. Preparar el payload para Gemini ──────────────────────────
    $parts = [];
    
    // Si hay imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $img_tmp = $_FILES['imagen']['tmp_name'];
        $img_type = $_FILES['imagen']['type'];
        $img_data = file_get_contents($img_tmp);
        $img_base64 = base64_encode($img_data);
        
        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($img_type, $allowed_mime)) {
            $parts[] = [
                "inline_data" => [
                    "mime_type" => $img_type,
                    "data" => $img_base64
                ]
            ];
        } else {
            echo json_encode(['error' => 'Formato de imagen no soportado. Usa JPG, PNG o WEBP.']);
            exit;
        }
    }

    $parts[] = [
        "text" => "Contexto del sistema:\n" . $contexto . "\n\nPregunta del usuario:\n" . $mensaje_usuario
    ];

    $data = [
        "contents" => [
            [
                "parts" => $parts
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 800
        ]
    ];

    $json_data = json_encode($data);

    // ── 3. Llamar a la API de Gemini (con fallback) ─────────────────
    $modelos_fallback = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-flash-lite-latest',
        'gemini-3.1-flash-lite-preview'
    ];

    $response = null;
    $http_code = 0;
    $error = null;

    foreach ($modelos_fallback as $modelo) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $modelo . ':generateContent?key=' . $GEMINI_API_KEY;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!$error && $http_code == 200) {
            break; // Éxito!
        }
        
        // Si hay error 503 (Alta demanda) o 429 (Cuota excedida/Rate limit), probamos el siguiente modelo
        if ($http_code != 503 && $http_code != 429) {
             break; // Otro tipo de error, salimos del loop para mostrarlo
        }
    }

    if ($error) {
        echo json_encode(['error' => 'Error de conexión con la IA: ' . $error]);
        exit;
    }

    if ($http_code != 200) {
        $resp_dec = json_decode($response, true);
        $err_msg = $resp_dec['error']['message'] ?? 'Error desconocido de Gemini';
        if ($GEMINI_API_KEY === "TU_API_KEY_AQUI") {
             $err_msg = "Aún no configuraste tu API Key de Gemini. Modificá el archivo controllers/ai_assistant.php y colocá tu key en la línea 17.";
        }
        echo json_encode(['error' => 'Error de IA: ' . $err_msg]);
        exit;
    }

    $result = json_decode($response, true);
    $texto_respuesta = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Mmm, no pude generar una respuesta. Intentá de nuevo.';

    echo json_encode(['respuesta' => trim($texto_respuesta)]);
    exit;
}
?>
