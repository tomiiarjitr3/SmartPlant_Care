<?php
// ═══════════════════════════════════════════════════════════════
//  Dashboard.php — SmartPlant CARE
//  Muestra datos reales leídos de la base de datos MySQL
// ═══════════════════════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /SmartPlant_Care/views/Login.php");
    exit;
}

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: /SmartPlant_Care/views/Login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db         = Database::connect();
$usuario_id = (int) $_SESSION['usuario_id'];

// ── Check if table needs columns ────────────────────────────────
$checkCols = $db->query("SHOW COLUMNS FROM usuarios LIKE 'telefono'");
if ($checkCols && $checkCols->num_rows === 0) {
    $db->query("ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT NULL");
    $db->query("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL");
}

// ── Load user data ──────────────────────────────────────────────
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario_data = $stmt->get_result()->fetch_assoc();

$msg_perfil = "";
// ── Profile update logic ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nuevo_telefono = trim($_POST['telefono'] ?? '');
    $nueva_password = $_POST['password'] ?? '';
    
    // Upload profile photo
    $foto_path = $usuario_data['foto_perfil'];
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'perfil_' . $usuario_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_dir . $filename)) {
                $foto_path = '/SmartPlant_Care/assets/uploads/' . $filename;
            }
        }
    }

    if (!empty($nueva_password)) {
        $hashed = password_hash($nueva_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, foto_perfil = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nuevo_telefono, $foto_path, $hashed, $usuario_id);
    } else {
        $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nuevo_telefono, $foto_path, $usuario_id);
    }
    
    if ($stmt->execute()) {
        $msg_perfil = "<div class='text-green-400 text-sm mb-4 bg-green-500/10 p-3 rounded-xl border border-green-500/20'>Perfil actualizado correctamente.</div>";
        $_SESSION['foto_perfil'] = $foto_path;
        $usuario_data['telefono'] = $nuevo_telefono;
        $usuario_data['foto_perfil'] = $foto_path;
    } else {
        $msg_perfil = "<div class='text-red-400 text-sm mb-4 bg-red-500/10 p-3 rounded-xl border border-red-500/20'>Error al actualizar el perfil.</div>";
    }
}

$_SESSION['nombre']      = $usuario_data['nombre'];
$_SESSION['foto_perfil'] = $usuario_data['foto_perfil'] ?? '';
$nombre                  = $_SESSION['nombre'];

// ── Settings update logic ────────────────────────────────────────
$msg_settings = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $pl_id = (int)$_POST['planta_id'];
    $h_min = (float)$_POST['humedad_min'];
    $h_max = (float)$_POST['humedad_max'];
    $t_min = (float)$_POST['temp_min'];
    $t_max = (float)$_POST['temp_max'];

    $stmt = $db->prepare("UPDATE plantas SET humedad_min = ?, humedad_max = ?, temp_min = ?, temp_max = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ddddii", $h_min, $h_max, $t_min, $t_max, $pl_id, $usuario_id);
    if ($stmt->execute()) {
        $msg_settings = "<div class='text-green-400 text-sm mb-4 bg-green-500/10 p-3 rounded-xl border border-green-500/20'>Configuración guardada correctamente.</div>";
    } else {
        $msg_settings = "<div class='text-red-400 text-sm mb-4 bg-red-500/10 p-3 rounded-xl border border-red-500/20'>Error al guardar configuración.</div>";
    }
}

// ── 1. Obtener plantas del usuario ──────────────────────────────
$stmt   = $db->prepare("SELECT * FROM plantas WHERE usuario_id = ? AND activa = 1 ORDER BY id ASC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$plantas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Planta seleccionada (primera por defecto o la del parámetro GET)
$planta_id = isset($_GET['planta']) ? (int)$_GET['planta'] : ($plantas[0]['id'] ?? 0);

// Find the current plant in the array
$planta_actual = null;
foreach ($plantas as $p) {
    if ($p['id'] == $planta_id) {
        $planta_actual = $p;
        break;
    }
}
if (!$planta_actual && count($plantas) > 0) {
    $planta_actual = $plantas[0];
}

// ── 2. Última lectura del sensor ────────────────────────────────
$ultima = null;
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT l.* FROM lecturas_sensores l
        INNER JOIN dispositivos d ON l.dispositivo_id = d.id
        WHERE l.planta_id = ?
        ORDER BY l.creada_en DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $planta_id);
    $stmt->execute();
    $ultima = $stmt->get_result()->fetch_assoc();
}

// ── 3. Historial de humedad últimas 12h (para el mini-chart) ───
$historial = [];
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT humedad_suelo, DATE_FORMAT(creada_en, '%H:%i') AS hora
        FROM lecturas_sensores
        WHERE planta_id = ? AND creada_en >= NOW() - INTERVAL 12 HOUR
        ORDER BY creada_en ASC
        LIMIT 24
    ");
    $stmt->bind_param("i", $planta_id);
    $stmt->execute();
    $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ── 4. Eventos recientes (log) ──────────────────────────────────
$eventos = [];
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT * FROM eventos
        WHERE planta_id = ?
        ORDER BY creado_en DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $planta_id);
    $stmt->execute();
    $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ── Valores con fallback si no hay lectura ──────────────────────
$humedad   = $ultima ? (float)$ultima['humedad_suelo']  : 0;
$temp      = $ultima ? (float)$ultima['temperatura']    : 0;
$luz       = $ultima ? (int)  $ultima['luz_ambiental']  : 0;
$tanque    = $ultima ? (int)  $ultima['nivel_tanque']   : 0;
$bateria   = $ultima ? (int)  $ultima['bateria']        : 0;

// ── Salud general simple (promedio ponderado) ───────────────────
$salud = $ultima ? min(100, round(($humedad + $bateria) / 2)) : 0;

// ── Íconos y textos de eventos ─────────────────────────────────
$evento_icono = [
    'riego'              => ['icon' => '💧', 'color' => 'green'],
    'alerta_humedad'     => ['icon' => '⚠️', 'color' => 'yellow'],
    'alerta_temperatura' => ['icon' => '🌡️', 'color' => 'orange'],
    'bateria_baja'       => ['icon' => '🔋', 'color' => 'cyan'],
    'sin_conexion'       => ['icon' => '📡', 'color' => 'red'],
    'otro'               => ['icon' => '🔔', 'color' => 'gray'],
];

// ── 5. Inventario fotográfico ───────────────────────────────────
$db->query("CREATE TABLE IF NOT EXISTS inventario_plantas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    planta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    foto_path VARCHAR(255) NOT NULL,
    diagnostico TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_planta (planta_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$inventario = [];
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT id, foto_path, diagnostico, fecha
        FROM inventario_plantas
        WHERE planta_id = ? AND usuario_id = ?
        ORDER BY fecha DESC
        LIMIT 20
    ");
    $stmt->bind_param("ii", $planta_id, $usuario_id);
    $stmt->execute();
    $inventario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
</head>

<body class="bg-solid-dark text-white min-h-screen">

<!-- Scroll Progress Bar (hidden) -->


<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto max-w-6xl px-4">
    <div class="glass-clean flex items-center justify-between px-8 md:px-10 py-5 rounded-[2.5rem]">
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-white/60">🌱</span> SmartPlant
        </a>
        <div class="hidden md:flex items-center gap-3">
            <div class="status-dot"></div>
            <span class="text-xs font-medium text-gray-400 tracking-wide">Sistema activo</span>
        </div>
        <div class="flex items-center gap-5">
            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="w-9 h-9 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-sm hover:bg-white/10 transition-all" title="Cambiar tema" id="themeToggleBtn">
                🌙
            </button>
            <div class="hidden md:flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-white/10 to-white/5 border border-white/10 flex items-center justify-center text-sm overflow-hidden">
                    <?php if(!empty($_SESSION['foto_perfil'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['foto_perfil']) ?>" class="w-full h-full object-cover" alt="Perfil">
                    <?php else: ?>
                        👤
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm font-medium text-white/90 leading-tight"><?= htmlspecialchars($nombre) ?></p>
                    <p class="text-[10px] text-gray-500 font-light capitalize"><?= $_SESSION['plan'] ?? 'free' ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openProfileModal()" class="bg-white/5 border border-white/10 text-white/70 px-4 py-2.5 rounded-full text-xs font-medium hover:bg-white/10 hover:text-white transition-all">
                    Perfil
                </button>
                <a href="?logout=1" class="bg-white/5 border border-white/10 text-white/50 px-4 py-2.5 rounded-full text-xs font-medium hover:bg-white/10 transition-all">
                    Salir
                </a>
            </div>
        </div>
    </div>
</header>

<!-- ═══ HERO / WELCOME ═══ -->
<section class="max-w-6xl mx-auto px-6 pt-20 pb-8">
    <div class="reveal-blur">
        <span class="text-white/50 font-semibold tracking-[0.2em] text-xs uppercase">Dashboard</span>
        <h2 class="text-4xl md:text-6xl font-semibold tracking-tight mt-3">
            Hola, <span class="text-gradient-anim"><?= htmlspecialchars(explode(' ', $nombre)[0]) ?>.</span>
        </h2>
        <p class="text-gray-500 text-lg font-light mt-3 max-w-xl">
            <?php if ($ultima): ?>
                Última lectura: <?= date('d/m/Y H:i', strtotime($ultima['creada_en'])) ?> — Todo bajo control.
            <?php else: ?>
                Todavía no hay lecturas de sensores. Conectá tu ESP32.
            <?php endif; ?>
        </p>
    </div>

    <?php if (count($plantas) > 1): ?>
    <!-- Selector de planta si tiene más de una -->
    <div class="mt-6 flex gap-3 flex-wrap">
        <?php foreach ($plantas as $p): ?>
        <a href="?planta=<?= $p['id'] ?>"
           class="px-5 py-2 rounded-full text-sm border transition-all <?= $p['id'] == $planta_id ? 'bg-white text-black border-white font-semibold' : 'border-white/10 text-gray-400 hover:border-white/25 hover:text-white' ?>">
            🌿 <?= htmlspecialchars($p['nombre']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ═══ STATS CARDS ═══ -->
<section class="max-w-6xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 stagger-children" id="statsGrid">

        <!-- 💧 Humedad -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-green w-12 h-12 rounded-2xl flex items-center justify-center text-xl">💧</div>
                <span class="text-[10px] font-medium text-white/40 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Humedad del suelo</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-green"
               data-target="<?= $humedad ?>" data-suffix="%">0%</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-white/30 to-white/50"
                     style="width:0%" data-width="<?= $humedad ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Óptimo: 40–60%</p>
        </div>

        <!-- 🌡️ Temperatura -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-orange w-12 h-12 rounded-2xl flex items-center justify-center text-xl">🌡️</div>
                <span class="text-[10px] font-medium text-white/40 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Temperatura</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-orange"
               data-target="<?= $temp ?>" data-suffix="°C">0°C</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-white/25 to-white/45"
                     style="width:0%" data-width="<?= min(100, round($temp / 50 * 100)) ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Ideal: 20–28°C</p>
        </div>

        <!-- ☀️ Luz -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-blue w-12 h-12 rounded-2xl flex items-center justify-center text-xl">☀️</div>
                <span class="text-[10px] font-medium text-white/40 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Luz Ambiental</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-blue"
               data-target="<?= $luz ?>" data-suffix=" lx">0 lx</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-white/25 to-white/45"
                     style="width:0%" data-width="<?= min(100, round($luz / 1000 * 100)) ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">
                <?= $luz > 600 ? 'Buena iluminación' : ($luz > 200 ? 'Luz moderada' : 'Poca luz') ?>
            </p>
        </div>

        <!-- 🪣 Tanque -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-cyan w-12 h-12 rounded-2xl flex items-center justify-center text-xl">🪣</div>
                <span class="text-[10px] font-medium text-white/40 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Nivel del Tanque</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-cyan"
               data-target="<?= $tanque ?>" data-suffix="%">0%</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-white/25 to-white/45"
                     style="width:0%" data-width="<?= $tanque ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">
                <?= $tanque > 50 ? 'Capacidad suficiente' : ($tanque > 20 ? 'Nivel medio' : 'Nivel bajo — Rellenar') ?>
            </p>
        </div>

    </div>
</section>

<!-- ═══ CHART + ESTADO PLANTA ═══ -->
<section class="max-w-6xl mx-auto px-6 py-6">
    <div class="grid lg:grid-cols-3 gap-5">

        <!-- Mini chart humedad -->
        <div class="lg:col-span-2 card-glass rounded-[2rem] p-8 reveal">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Humedad del suelo</p>
                    <p class="text-2xl font-semibold tracking-tight mt-1">Últimas 12 horas</p>
                </div>
            </div>
            <div class="flex items-end gap-2 h-36" id="chartBars">
                <?php if (!empty($historial)): ?>
                    <?php foreach ($historial as $h): ?>
                        <?php $pct = min(100, max(5, round((float)$h['humedad_suelo']))); ?>
                        <div class="mini-bar flex-1" style="height:<?= $pct ?>%;" title="<?= $h['hora'] ?> — <?= $h['humedad_suelo'] ?>%"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ([35,50,40,65,60,55,70,45,58,62,50,47] as $v): ?>
                        <div class="mini-bar flex-1" style="height:<?= $v ?>%;opacity:0.3;"></div>
                    <?php endforeach; ?>
                    <p class="absolute text-gray-600 text-xs font-light ml-2">Sin datos aún</p>
                <?php endif; ?>
            </div>
            <div class="flex justify-between mt-4">
                <?php if (!empty($historial)): ?>
                    <span class="text-[10px] text-gray-600"><?= $historial[0]['hora'] ?? '' ?></span>
                    <span class="text-[10px] text-gray-600"><?= $historial[count($historial)-1]['hora'] ?? '' ?></span>
                <?php else: ?>
                    <span class="text-[10px] text-gray-600">00:00</span>
                    <span class="text-[10px] text-gray-600">12:00</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estado de la planta -->
        <div class="card-glass rounded-[2rem] p-8 reveal flex flex-col items-center justify-center text-center">
            <div class="ring-gauge mb-6">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle class="ring-bg" cx="60" cy="60" r="52"/>
                    <circle class="ring-fill" cx="60" cy="60" r="52"
                        stroke="url(#ringGrad)"
                        stroke-dasharray="326.73"
                        stroke-dashoffset="326.73"
                        id="healthRing"/>
                    <defs>
                        <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#e0e0e0"/>
                            <stop offset="100%" stop-color="#a0a0a0"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div>
                        <p class="text-3xl font-bold tracking-tighter text-gradient-green"><?= $salud ?></p>
                        <p class="text-[9px] text-gray-500 font-light uppercase tracking-widest">salud</p>
                    </div>
                </div>
            </div>
            <h3 class="text-xl font-semibold tracking-tight mb-2">
                <?= $salud >= 75 ? 'Estado excelente' : ($salud >= 50 ? 'Estado regular' : 'Necesita atención') ?>
            </h3>
            <p class="text-gray-500 text-xs font-light leading-relaxed max-w-[200px]">
                <?= $salud >= 75 ? 'Tu planta está en condiciones óptimas. 🌿' : 'Revisá los niveles de humedad y batería.' ?>
            </p>
            <div class="divider-glass w-full my-6"></div>
            <div class="w-full space-y-3 text-left">
                <div class="flex justify-between">
                    <span class="text-[11px] text-gray-500">Hidratación</span>
                    <span class="text-[11px] font-medium <?= $humedad >= 40 ? 'text-white/80' : 'text-white/40' ?>">
                        <?= $humedad >= 40 ? 'Óptima' : 'Baja' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[11px] text-gray-500">Temperatura</span>
                    <span class="text-[11px] font-medium <?= ($temp >= 20 && $temp <= 28) ? 'text-white/80' : 'text-white/40' ?>">
                        <?= ($temp >= 20 && $temp <= 28) ? 'Normal' : 'Fuera de rango' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[11px] text-gray-500">Luz solar</span>
                    <span class="text-[11px] font-medium <?= $luz > 600 ? 'text-white/80' : 'text-white/40' ?>">
                        <?= $luz > 600 ? 'Alta' : ($luz > 200 ? 'Media' : 'Baja') ?>
                    </span>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ═══ ACCIONES + ACTIVIDAD ═══ -->
<section class="max-w-6xl mx-auto px-6 py-6 pb-16">
    <div class="grid lg:grid-cols-2 gap-5">

        <!-- Acciones rápidas -->
        <div class="card-glass rounded-[2rem] p-8 reveal">
            <div class="mb-8">
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Control</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Acciones rápidas</p>
            </div>
            <div class="space-y-4">
                <button class="btn-action w-full flex items-center gap-5 bg-white/[0.04] border border-white/10 rounded-2xl p-5 text-left hover:border-white/20 group">
                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">💧</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Regar ahora</p>
                        <p class="text-[11px] text-gray-500 font-light">Activar riego manual por 30 segundos</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
                <button class="btn-action w-full flex items-center gap-5 bg-white/[0.04] border border-white/10 rounded-2xl p-5 text-left hover:border-white/20 group">
                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">🔄</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Actualizar lecturas</p>
                        <p class="text-[11px] text-gray-500 font-light">Forzar lectura del sensor ahora</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
                <button onclick="openSettingsModal()" class="btn-action w-full flex items-center gap-5 bg-white/[0.04] border border-white/10 rounded-2xl p-5 text-left hover:border-white/20 group">
                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">⚙️</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Configuración</p>
                        <p class="text-[11px] text-gray-500 font-light">Ajustar umbrales y notificaciones</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
            </div>
        </div>

        <!-- Log de actividad -->
        <div class="card-glass rounded-[2rem] p-8 reveal">
            <div class="mb-8">
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Log</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Actividad reciente</p>
            </div>
            <div class="space-y-5">
                <?php if (!empty($eventos)): ?>
                    <?php foreach ($eventos as $i => $ev): ?>
                        <?php
                        $info  = $evento_icono[$ev['tipo']] ?? $evento_icono['otro'];
                        $color = $info['color'];
                        $icon  = $info['icon'];
                        $last  = $i === count($eventos) - 1;
                        ?>
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl bg-<?= $color ?>-500/10 border border-<?= $color ?>-500/20 flex items-center justify-center text-sm"><?= $icon ?></div>
                                <?php if (!$last): ?>
                                    <div class="timeline-line flex-1 mt-2"></div>
                                <?php endif; ?>
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-medium text-white/90"><?= htmlspecialchars($ev['mensaje']) ?></p>
                                <p class="text-[10px] text-gray-600 mt-1">
                                    <?php
                                    $diff = time() - strtotime($ev['creado_en']);
                                    if     ($diff < 3600)   echo 'Hace ' . round($diff/60)  . ' min';
                                    elseif ($diff < 86400)  echo 'Hace ' . round($diff/3600) . ' h';
                                    else                    echo 'Hace ' . round($diff/86400) . ' días';
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600 text-sm font-light">Sin eventos recientes.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ═══ INVENTARIO FOTOGRÁFICO ═══ -->
<section class="max-w-6xl mx-auto px-6 py-6 pb-16" id="inventarioSection">
    <div class="card-glass rounded-[2rem] p-8 reveal">
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Inventario</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Evolución visual</p>
            </div>
            <button onclick="openInventoryUpload()" class="flex items-center gap-2 bg-white/5 border border-white/10 text-white/70 px-5 py-2.5 rounded-full text-xs font-semibold hover:bg-white/10 hover:scale-105 transition-all">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Subir foto
            </button>
        </div>

        <!-- Timeline del inventario -->
        <div id="inventoryTimeline">
            <?php if (!empty($inventario)): ?>
                <div class="inv-timeline">
                    <?php foreach ($inventario as $i => $entry): ?>
                        <?php
                        $fecha_fmt  = date('d M Y', strtotime($entry['fecha']));
                        $hora_fmt   = date('H:i', strtotime($entry['fecha']));
                        $diag_short = mb_strlen($entry['diagnostico']) > 120
                            ? mb_substr($entry['diagnostico'], 0, 120) . '...'
                            : $entry['diagnostico'];
                        ?>
                        <div class="inv-entry" data-id="<?= $entry['id'] ?>">
                            <div class="inv-entry-dot"></div>
                            <div class="inv-entry-line"></div>
                            <div class="inv-entry-content">
                                <div class="inv-entry-card" onclick="openInventoryDetail(<?= $entry['id'] ?>, '<?= htmlspecialchars($entry['foto_path'], ENT_QUOTES) ?>', `<?= htmlspecialchars($entry['diagnostico'], ENT_QUOTES) ?>`, '<?= $entry['fecha'] ?>')">
                                    <div class="inv-entry-img-wrap">
                                        <img src="<?= htmlspecialchars($entry['foto_path']) ?>" alt="Foto planta" class="inv-entry-img" loading="lazy">
                                    </div>
                                    <div class="inv-entry-info">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="text-xs font-semibold text-white/90"><?= $fecha_fmt ?></span>
                                            <span class="text-[10px] text-gray-600"><?= $hora_fmt ?></span>
                                        </div>
                                        <p class="text-xs text-gray-400 font-light leading-relaxed"><?= htmlspecialchars($diag_short) ?></p>
                                    </div>
                                </div>
                                <button onclick="event.stopPropagation(); deleteInventoryEntry(<?= $entry['id'] ?>)" class="inv-delete-btn" title="Eliminar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="inv-empty" id="invEmptyState">
                    <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-3xl">📸</div>
                    <p class="text-gray-400 text-sm font-medium mb-1">Sin fotos todavía</p>
                    <p class="text-gray-600 text-xs font-light">Subí una foto de tu planta y la IA la analizará automáticamente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══ MODAL SUBIR FOTO INVENTARIO ═══ -->
<div id="invUploadModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-[#1a1a1a] border border-white/10 rounded-3xl p-8 max-w-md w-full shadow-2xl transform scale-95 transition-all duration-300" id="invUploadModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold tracking-tight text-white">Subir foto al inventario</h3>
            <button onclick="closeInventoryUpload()" class="text-gray-500 hover:text-white transition-colors text-xl">✕</button>
        </div>

        <form id="invUploadForm" onsubmit="handleInventoryUpload(event)">
            <!-- Drop zone -->
            <div class="inv-dropzone" id="invDropzone" onclick="document.getElementById('invFileInput').click()">
                <div id="invDropzoneContent">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-2xl">📷</div>
                    <p class="text-gray-300 text-sm font-medium mb-1">Arrastrá o hacé clic para subir</p>
                    <p class="text-gray-600 text-xs font-light">JPG, PNG o WEBP • Máx 10MB</p>
                </div>
                <img id="invPreviewImg" src="" alt="Preview" class="hidden w-full h-48 object-cover rounded-xl">
            </div>
            <input type="file" id="invFileInput" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewInventoryImage(event)">

            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeInventoryUpload()" class="flex-1 bg-white/5 border border-white/10 text-gray-400 font-medium rounded-xl py-3 text-sm hover:bg-white/10 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="invUploadBtn" class="flex-1 bg-white text-black font-semibold rounded-xl py-3 text-sm hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    <span id="invUploadBtnText">Subir y analizar</span>
                    <span id="invUploadBtnLoading" class="hidden flex items-center justify-center gap-2">
                        <span class="ai-typing-indicator" style="padding:0;"><span class="ai-dot"></span><span class="ai-dot"></span><span class="ai-dot"></span></span>
                        Analizando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL DETALLE INVENTARIO ═══ -->
<div id="invDetailModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-[#1a1a1a] border border-white/10 rounded-3xl p-8 max-w-2xl w-full shadow-2xl transform scale-95 transition-all duration-300" id="invDetailModalContent">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-semibold tracking-tight text-white">Detalle de registro</h3>
                <p class="text-xs text-gray-500 mt-1" id="invDetailDate"></p>
            </div>
            <button onclick="closeInventoryDetail()" class="text-gray-500 hover:text-white transition-colors text-xl">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-2xl overflow-hidden border border-white/10 bg-black/30">
                <img id="invDetailImg" src="" alt="Foto planta" class="w-full h-auto max-h-[400px] object-contain">
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-sm">🧠</div>
                    <p class="text-xs font-semibold text-white/60 tracking-widest uppercase">Diagnóstico IA</p>
                </div>
                <p id="invDetailDiag" class="text-gray-300 text-sm font-light leading-relaxed flex-1"></p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="max-w-6xl mx-auto px-6 py-10">
    <div class="divider-glass mb-6"></div>
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <span class="text-xs text-gray-600 font-light flex items-center gap-2"><span class="text-white/40">🌱</span> SmartPlant CARE — Dashboard</span>
        <div class="flex gap-6">
            <a href="/SmartPlant_Care/index.php" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Inicio</a>
            <a href="/SmartPlant_Care/views/Support.php" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Soporte</a>
        </div>
        <p class="text-[10px] text-gray-700">© 2026 Todos los derechos reservados.</p>
    </div>
</footer>

<!-- ═══ AI ASSISTANT CHAT WIDGET ═══ -->
<button class="ai-chat-btn" id="aiChatBtn" onclick="toggleAIChat()" title="Asistente SmartPlant">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" class="ai-btn-icon">
        <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z" fill="url(#aiBtnGrad)" stroke="rgba(0,0,0,0.15)" stroke-width="0.5"/>
        <path d="M19 2L19.8 4.2L22 5L19.8 5.8L19 8L18.2 5.8L16 5L18.2 4.2L19 2Z" fill="url(#aiBtnGrad)" opacity="0.7"/>
        <path d="M5 16L5.6 17.4L7 18L5.6 18.6L5 20L4.4 18.6L3 18L4.4 17.4L5 16Z" fill="url(#aiBtnGrad)" opacity="0.5"/>
        <defs>
            <linearGradient id="aiBtnGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#0a0a0a"/>
                <stop offset="100%" stop-color="#1a1a2e"/>
            </linearGradient>
        </defs>
    </svg>
</button>

<div class="ai-chat-window" id="aiChatWindow">
    <div class="ai-chat-header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-white/10 to-white/5 flex items-center justify-center shadow-lg" style="animation: aiHeaderGlow 3s ease-in-out infinite;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z" fill="white"/>
                    <path d="M19 2L19.8 4.2L22 5L19.8 5.8L19 8L18.2 5.8L16 5L18.2 4.2L19 2Z" fill="white" opacity="0.7"/>
                </svg>
            </div>
            <div>
                <h4 class="font-semibold text-white tracking-tight">SmartPlant AI</h4>
                <p class="text-[10px] text-white/50 font-medium tracking-widest uppercase">En línea</p>
            </div>
        </div>
        <button onclick="toggleAIChat()" class="text-gray-400 hover:text-white transition-colors">✕</button>
    </div>
    
    <div class="ai-quick-prompts">
        <button class="ai-quick-prompt-btn" onclick="sendQuickPrompt('¿En qué estado se encuentra mi planta?')">¿Cómo está mi planta?</button>
        <button class="ai-quick-prompt-btn" onclick="sendQuickPrompt('¿Qué planta tengo?')">¿Qué especie tengo?</button>
        <button class="ai-quick-prompt-btn" onclick="sendQuickPrompt('Dame consejos de riego')">Consejos de riego</button>
    </div>

    <div class="ai-chat-messages" id="aiChatMessages">
        <div class="msg-bubble msg-ai">
            ¡Hola! Soy tu asistente experto de SmartPlant. Puedo leer los sensores de tu planta en vivo o analizar fotos. ¿En qué te ayudo hoy? 🌱
        </div>
    </div>

    <div class="ai-chat-footer">
        <div class="img-upload-preview" id="aiImgPreviewContainer">
            <img id="aiImgPreview" src="" alt="Preview">
            <span class="img-remove" onclick="removeAIImage()">✕</span>
        </div>
        <form id="aiChatForm" class="ai-chat-input-wrapper" onsubmit="handleAIChatSubmit(event)">
            <input type="hidden" id="aiPlantaId" value="<?= htmlspecialchars($planta_id) ?>">
            <input type="file" id="aiImageInput" accept="image/jpeg, image/png, image/webp" class="hidden" onchange="previewAIImage(event)">
            <button type="button" class="ai-chat-action-btn" onclick="document.getElementById('aiImageInput').click()" title="Subir foto">
                📷
            </button>
            <input type="text" id="aiChatInput" class="ai-chat-input" placeholder="Preguntale a la IA..." autocomplete="off">
            <button type="submit" class="ai-chat-action-btn ai-chat-send" id="aiChatSendBtn">
                ↑
            </button>
        </form>
    </div>
</div>

<!-- ═══ MODAL CONFIGURACIÓN PLANTA ═══ -->
<div id="settingsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-[#1a1a1a] border border-white/10 rounded-3xl p-8 max-w-md w-full shadow-2xl transform scale-95 transition-all duration-300" id="settingsModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold tracking-tight text-white">Configuración de Planta</h3>
            <button onclick="closeSettingsModal()" class="text-gray-500 hover:text-white transition-colors">✕</button>
        </div>
        
        <?= $msg_settings ?>

        <form method="POST" action="">
            <input type="hidden" name="update_settings" value="1">
            <input type="hidden" name="planta_id" value="<?= $planta_actual ? $planta_actual['id'] : 0 ?>">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Humedad Mínima (%)</label>
                    <input type="number" name="humedad_min" value="<?= $planta_actual ? htmlspecialchars($planta_actual['humedad_min']) : 35 ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-green-500 transition-colors" step="1" required>
                    <p class="text-[10px] text-gray-500 mt-1">Nivel en el que consideraremos que la planta necesita agua.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Humedad Máxima (%)</label>
                    <input type="number" name="humedad_max" value="<?= $planta_actual ? htmlspecialchars($planta_actual['humedad_max']) : 65 ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-green-500 transition-colors" step="1" required>
                    <p class="text-[10px] text-gray-500 mt-1">Nivel donde el riego se detendrá automáticamente.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Temp. Mínima (°C)</label>
                        <input type="number" name="temp_min" value="<?= $planta_actual ? htmlspecialchars($planta_actual['temp_min']) : 15 ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-green-500 transition-colors" step="0.1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Temp. Máxima (°C)</label>
                        <input type="number" name="temp_max" value="<?= $planta_actual ? htmlspecialchars($planta_actual['temp_max']) : 35 ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-green-500 transition-colors" step="0.1" required>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-white text-black font-semibold py-3 rounded-xl mt-8 hover:opacity-90 transition-opacity">
                Guardar Ajustes
            </button>
        </form>
    </div>
</div>

<!-- ═══ MODAL PERFIL ═══ -->
<div id="profileModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-[#1a1a1a] border border-white/10 rounded-3xl p-8 max-w-md w-full shadow-2xl transform scale-95 transition-all duration-300" id="profileModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold tracking-tight text-white">Mi Perfil</h3>
            <button onclick="closeProfileModal()" class="text-gray-500 hover:text-white transition-colors">✕</button>
        </div>

        <?= $msg_perfil ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="update_profile" value="1">
            
            <!-- Foto de perfil -->
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-widest mb-2">Foto de perfil</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden shrink-0">
                        <?php if(!empty($usuario_data['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($usuario_data['foto_perfil']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-2xl">👤</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="foto_perfil" accept="image/*" class="text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white/5 file:text-white/70 hover:file:bg-white/10 transition-all cursor-pointer">
                </div>
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-widest mb-2">Teléfono</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($usuario_data['telefono'] ?? '') ?>" placeholder="+54 9 11 1234-5678" class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-green-400 transition-colors">
            </div>

            <!-- Cambiar Contraseña -->
            <div class="pt-2">
                <label class="block text-xs text-gray-400 uppercase tracking-widest mb-2">Cambiar Contraseña (opcional)</label>
                <input type="password" name="password" placeholder="Nueva contraseña" class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-green-400 transition-colors">
                <p class="text-[10px] text-gray-500 mt-1">Dejá este campo vacío si no querés cambiar tu contraseña.</p>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 bg-white text-black font-semibold rounded-xl py-3 transition-colors hover:opacity-90">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ─── Theme Toggle ───
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('sp_theme', next);
        document.getElementById('themeToggleBtn').innerHTML = next === 'dark' ? '🌙' : '☀️';
        // Update body text class
        if (next === 'light') {
            document.body.classList.remove('text-white');
            document.body.classList.add('text-gray-900');
        } else {
            document.body.classList.remove('text-gray-900');
            document.body.classList.add('text-white');
        }
    }

    // Load saved theme
    (function() {
        const saved = localStorage.getItem('sp_theme');
        if (saved === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            document.getElementById('themeToggleBtn').innerHTML = '☀️';
            document.body.classList.remove('text-white');
            document.body.classList.add('text-gray-900');
        }
    })();

    // ─── Profile Modal ───
    function openProfileModal() {
        const modal = document.getElementById('profileModal');
        const content = document.getElementById('profileModalContent');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeProfileModal() {
        const modal = document.getElementById('profileModal');
        const content = document.getElementById('profileModalContent');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }

    // Auto-open if there was a message
    <?php if(!empty($msg_perfil)): ?>
        setTimeout(openProfileModal, 300);
    <?php endif; ?>

    // Close on click outside
    document.getElementById('profileModal').addEventListener('click', (e) => {
        if(e.target === e.currentTarget) closeProfileModal();
    });

    // ─── Scroll Progress ───
    window.addEventListener('scroll', () => {
        const pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        document.getElementById('scrollProgress').style.width = pct + '%';
    });

    // ─── Intersection Observer ───
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('active');
                if (e.target.id === 'statsGrid') { animateCounters(); animateProgressBars(); }
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.reveal, .reveal-blur, .reveal-scale, .stagger-children').forEach(el => observer.observe(el));

    // ─── Counter Animation ───
    let done = false;
    function animateCounters() {
        if (done) return; done = true;
        document.querySelectorAll('[data-target]').forEach(el => {
            const target = parseFloat(el.getAttribute('data-target'));
            const suffix = el.getAttribute('data-suffix') || '';
            const dur    = 2000;
            const start  = performance.now();
            function step(now) {
                const p = Math.min((now - start) / dur, 1);
                const e = 1 - Math.pow(1 - p, 3);
                el.textContent = (Number.isInteger(target) ? Math.round(e * target) : (e * target).toFixed(1)) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    // ─── Progress Bars ───
    function animateProgressBars() {
        document.querySelectorAll('.progress-fill[data-width]').forEach(bar => {
            setTimeout(() => bar.style.width = bar.dataset.width, 400);
        });
    }

    // ─── Health Ring ───
    function animateRing() {
        const ring = document.getElementById('healthRing');
        if (!ring) return;
        const circ   = 326.73;
        const health = <?= $salud ?>;
        setTimeout(() => ring.style.strokeDashoffset = circ - (circ * health / 100), 700);
    }

    // ─── AI Chat Widget ───
    function toggleAIChat() {
        const win = document.getElementById('aiChatWindow');
        win.classList.toggle('visible');
    }

    function sendQuickPrompt(text) {
        document.getElementById('aiChatInput').value = text;
        document.getElementById('aiChatForm').dispatchEvent(new Event('submit'));
    }

    function previewAIImage(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('aiImgPreview').src = e.target.result;
                document.getElementById('aiImgPreviewContainer').classList.add('active');
            }
            reader.readAsDataURL(file);
        }
    }

    function removeAIImage() {
        document.getElementById('aiImageInput').value = '';
        document.getElementById('aiImgPreviewContainer').classList.remove('active');
        document.getElementById('aiImgPreview').src = '';
    }

    function addChatMessage(text, type, imgUrl = null) {
        const container = document.getElementById('aiChatMessages');
        const bubble = document.createElement('div');
        bubble.className = `msg-bubble msg-${type}`;
        
        if (imgUrl) {
            const img = document.createElement('img');
            img.src = imgUrl;
            img.className = 'msg-img-preview';
            bubble.appendChild(img);
        }
        
        // Handle basic formatting (newlines)
        const textSpan = document.createElement('span');
        textSpan.innerHTML = text.replace(/\n/g, '<br>');
        bubble.appendChild(textSpan);
        
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
    }

    function showAITyping() {
        const container = document.getElementById('aiChatMessages');
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble msg-ai';
        bubble.id = 'aiTypingIndicator';
        bubble.innerHTML = '<div class="ai-typing-indicator"><div class="ai-dot"></div><div class="ai-dot"></div><div class="ai-dot"></div></div>';
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
    }

    function hideAITyping() {
        const ind = document.getElementById('aiTypingIndicator');
        if (ind) ind.remove();
    }

    async function handleAIChatSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('aiChatInput');
        const fileInput = document.getElementById('aiImageInput');
        const msg = input.value.trim();
        const file = fileInput.files[0];
        const plantaId = document.getElementById('aiPlantaId').value;

        if (!msg && !file) return;

        // Preview Image URL if any
        let imgUrl = null;
        if (file) imgUrl = document.getElementById('aiImgPreview').src;

        addChatMessage(msg || 'Imagen enviada', 'user', imgUrl);
        
        input.value = '';
        removeAIImage();
        showAITyping();

        const formData = new FormData();
        formData.append('mensaje', msg);
        formData.append('planta_id', plantaId);
        if (file) formData.append('imagen', file);

        try {
            const res = await fetch('/SmartPlant_Care/controllers/ai_assistant.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            hideAITyping();
            
            if (data.error) {
                addChatMessage('⚠️ Error: ' + data.error, 'ai');
            } else {
                addChatMessage(data.respuesta, 'ai');
            }
        } catch (err) {
            hideAITyping();
            addChatMessage('⚠️ No pude conectarme con el servidor.', 'ai');
        }
    }

    // ── Settings Modal ──
    function openSettingsModal() {
        const modal = document.getElementById('settingsModal');
        const content = document.getElementById('settingsModalContent');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeSettingsModal() {
        const modal = document.getElementById('settingsModal');
        const content = document.getElementById('settingsModalContent');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }

    // ─── Init ───
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => document.querySelectorAll('.reveal-blur').forEach(el => el.classList.add('active')), 80);
        animateRing();

        <?php if (!empty($msg_settings)): ?>
            openSettingsModal();
        <?php endif; ?>

        // Inventory drag & drop setup
        setupInventoryDropzone();
    });

    // ═══ INVENTORY ═══

    // ── Modals ──
    function openInventoryUpload() {
        const modal = document.getElementById('invUploadModal');
        const content = document.getElementById('invUploadModalContent');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }
    function closeInventoryUpload() {
        const modal = document.getElementById('invUploadModal');
        const content = document.getElementById('invUploadModalContent');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        modal.classList.add('opacity-0', 'pointer-events-none');
        // Reset form
        document.getElementById('invFileInput').value = '';
        document.getElementById('invPreviewImg').classList.add('hidden');
        document.getElementById('invPreviewImg').src = '';
        document.getElementById('invDropzoneContent').classList.remove('hidden');
        document.getElementById('invUploadBtn').disabled = true;
    }
    document.getElementById('invUploadModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeInventoryUpload();
    });

    function openInventoryDetail(id, foto, diagnostico, fecha) {
        const modal = document.getElementById('invDetailModal');
        const content = document.getElementById('invDetailModalContent');
        document.getElementById('invDetailImg').src = foto;
        document.getElementById('invDetailDiag').textContent = diagnostico;
        const d = new Date(fecha);
        document.getElementById('invDetailDate').textContent =
            d.toLocaleDateString('es-AR', { day: '2-digit', month: 'long', year: 'numeric' }) +
            ' — ' + d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        modal.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }
    function closeInventoryDetail() {
        const modal = document.getElementById('invDetailModal');
        const content = document.getElementById('invDetailModalContent');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }
    document.getElementById('invDetailModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeInventoryDetail();
    });

    // ── Preview de imagen ──
    function previewInventoryImage(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 10 * 1024 * 1024) {
            alert('La imagen es muy pesada. Máximo 10MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('invPreviewImg').src = ev.target.result;
            document.getElementById('invPreviewImg').classList.remove('hidden');
            document.getElementById('invDropzoneContent').classList.add('hidden');
            document.getElementById('invUploadBtn').disabled = false;
        };
        reader.readAsDataURL(file);
    }

    // ── Drag & Drop ──
    function setupInventoryDropzone() {
        const dz = document.getElementById('invDropzone');
        if (!dz) return;
        ['dragenter', 'dragover'].forEach(ev => {
            dz.addEventListener(ev, e => {
                e.preventDefault();
                dz.classList.add('inv-dropzone-active');
            });
        });
        ['dragleave', 'drop'].forEach(ev => {
            dz.addEventListener(ev, e => {
                e.preventDefault();
                dz.classList.remove('inv-dropzone-active');
            });
        });
        dz.addEventListener('drop', e => {
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                const input = document.getElementById('invFileInput');
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                previewInventoryImage({ target: input });
            }
        });
    }

    // ── Upload con diagnóstico IA ──
    async function handleInventoryUpload(e) {
        e.preventDefault();
        const fileInput = document.getElementById('invFileInput');
        const file = fileInput.files[0];
        if (!file) return;

        const btn = document.getElementById('invUploadBtn');
        const btnText = document.getElementById('invUploadBtnText');
        const btnLoad = document.getElementById('invUploadBtnLoading');
        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoad.classList.remove('hidden');
        btnLoad.classList.add('flex');

        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('planta_id', '<?= $planta_id ?>');
        formData.append('foto', file);

        try {
            const res = await fetch('/SmartPlant_Care/controllers/inventory_controller.php?action=upload', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success && data.entry) {
                addTimelineEntry(data.entry);
                closeInventoryUpload();
            } else {
                alert(data.error || 'Error al subir la foto.');
            }
        } catch (err) {
            alert('Error de conexión.');
        } finally {
            btn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoad.classList.add('hidden');
            btnLoad.classList.remove('flex');
        }
    }

    // ── Agregar entrada al timeline dinámicamente ──
    function addTimelineEntry(entry) {
        const timeline = document.getElementById('inventoryTimeline');
        // Remove empty state if present
        const empty = document.getElementById('invEmptyState');
        if (empty) empty.remove();

        // Create or get timeline container
        let container = timeline.querySelector('.inv-timeline');
        if (!container) {
            container = document.createElement('div');
            container.className = 'inv-timeline';
            timeline.appendChild(container);
        }

        const d = new Date(entry.fecha);
        const fechaFmt = d.toLocaleDateString('es-AR', { day: '2-digit', month: 'short', year: 'numeric' });
        const horaFmt = d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        const diagShort = entry.diagnostico.length > 120 ? entry.diagnostico.substring(0, 120) + '...' : entry.diagnostico;
        const diagEscaped = entry.diagnostico.replace(/'/g, "\\'").replace(/`/g, "\\`");

        const div = document.createElement('div');
        div.className = 'inv-entry';
        div.dataset.id = entry.id;
        div.style.animation = 'fadeSlideIn 0.5s var(--ease-out) forwards';
        div.innerHTML = `
            <div class="inv-entry-dot"></div>
            <div class="inv-entry-line"></div>
            <div class="inv-entry-content">
                <div class="inv-entry-card" onclick="openInventoryDetail(${entry.id}, '${entry.foto_path}', \`${diagEscaped}\`, '${entry.fecha}')">
                    <div class="inv-entry-img-wrap">
                        <img src="${entry.foto_path}" alt="Foto planta" class="inv-entry-img">
                    </div>
                    <div class="inv-entry-info">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-xs font-semibold text-white/90">${fechaFmt}</span>
                            <span class="text-[10px] text-gray-600">${horaFmt}</span>
                        </div>
                        <p class="text-xs text-gray-400 font-light leading-relaxed">${diagShort}</p>
                    </div>
                </div>
                <button onclick="event.stopPropagation(); deleteInventoryEntry(${entry.id})" class="inv-delete-btn" title="Eliminar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                </button>
            </div>
        `;

        container.prepend(div);
    }

    // ── Eliminar entrada ──
    async function deleteInventoryEntry(id) {
        if (!confirm('¿Eliminar esta foto del inventario?')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        try {
            const res = await fetch('/SmartPlant_Care/controllers/inventory_controller.php?action=delete', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                const entry = document.querySelector(`.inv-entry[data-id="${id}"]`);
                if (entry) {
                    entry.style.animation = 'fadeSlideOut 0.3s ease forwards';
                    setTimeout(() => {
                        entry.remove();
                        // Check if timeline is empty
                        const container = document.querySelector('.inv-timeline');
                        if (container && container.children.length === 0) {
                            container.remove();
                            const timeline = document.getElementById('inventoryTimeline');
                            timeline.innerHTML = `
                                <div class="inv-empty" id="invEmptyState">
                                    <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-3xl">📸</div>
                                    <p class="text-gray-400 text-sm font-medium mb-1">Sin fotos todavía</p>
                                    <p class="text-gray-600 text-xs font-light">Subí una foto de tu planta y la IA la analizará automáticamente.</p>
                                </div>`;
                        }
                    }, 300);
                }
            } else {
                alert(data.error || 'Error al eliminar.');
            }
        } catch (err) {
            alert('Error de conexión.');
        }
    }
</script>
</body>
</html>