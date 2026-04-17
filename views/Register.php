<?php
// ═══════════════════════════════════════════════════════════════
//  Registro.php — SmartPlant CARE
//  Crea un nuevo usuario en MySQL con password hasheado
// ═══════════════════════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();

// Si ya está logueado, mandar al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: /SmartPlant_Care/views/Dashboard.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$error   = null;
$success = null;

// ── Valores para repoblar el form en caso de error ─────────────
$form = [
    'nombre'   => '',
    'email'    => '',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre    = trim($_POST['nombre']    ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = trim($_POST['password']  ?? '');
    $confirmar = trim($_POST['confirmar'] ?? '');

    $form['nombre'] = $nombre;
    $form['email']  = $email;

    // ── Validaciones ───────────────────────────────────────────
    if (!$nombre || !$email || !$password || !$confirmar) {
        $error = "Completá todos los campos.";

    } elseif (strlen($nombre) < 2 || strlen($nombre) > 100) {
        $error = "El nombre debe tener entre 2 y 100 caracteres.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";

    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";

    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";

    } else {
        $db = Database::connect();

        // Verificar si el email ya existe
        $check = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ese correo ya está registrado. ¿Querés iniciar sesión?";
        } else {
            // Hashear contraseña e insertar
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, plan) VALUES (?, ?, ?, 'free')");
            $stmt->bind_param("sss", $nombre, $email, $hash);

            if ($stmt->execute()) {
                $nuevo_id = $db->insert_id;

                // Crear planta por defecto para el nuevo usuario
                $planta = $db->prepare("
                    INSERT INTO plantas (usuario_id, nombre, especie, descripcion, humedad_min, humedad_max, temp_min, temp_max)
                    VALUES (?, 'Mi primera planta', 'Por definir', 'Planta registrada al crear la cuenta', 35, 65, 15.0, 35.0)
                ");
                $planta->bind_param("i", $nuevo_id);
                $planta->execute();

                // Iniciar sesión automáticamente
                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['usuario']    = $email;
                $_SESSION['nombre']     = $nombre;
                $_SESSION['plan']       = 'free';

                header("Location: /SmartPlant_Care/views/Dashboard.php");
                exit;
            } else {
                $error = "Ocurrió un error al registrar. Intentá de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
    <style>
        /* Barra de fuerza de contraseña */
        .strength-bar {
            height: 3px;
            border-radius: 999px;
            transition: width 0.4s var(--ease-out), background 0.4s ease;
        }
        /* Checkbox personalizado */
        .check-custom {
            appearance: none;
            width: 16px; height: 16px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .check-custom:checked {
            background: #4ade80;
            border-color: #4ade80;
        }
        .check-custom:checked::after {
            content: '✓';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10px;
            color: black;
            font-weight: bold;
        }
        /* Toast de éxito */
        .success-toast {
            background: rgba(74,222,128,0.1);
            border: 1px solid rgba(74,222,128,0.2);
        }
    </style>
</head>

<body class="bg-overlay text-white min-h-screen flex flex-col">

<div class="scroll-progress" id="scrollProgress"></div>

<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto w-full max-w-5xl px-4">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </a>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium text-white/80">
            <a href="/SmartPlant_Care/index.php#utilidades" class="hover:text-white transition-colors">Utilidades</a>
            <a href="/SmartPlant_Care/index.php#producto"   class="hover:text-white transition-colors">Producto</a>
            <a href="/SmartPlant_Care/store.php"            class="hover:text-white transition-colors">Tienda</a>
            <a href="Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">
                Iniciar sesión
            </a>
        </nav>
    </div>
</header>

<!-- ═══ REGISTRO SECTION ═══ -->
<main class="flex-1 flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md relative">

        <!-- Orbit rings -->
        <div class="orbit-ring" style="width:520px;height:520px;top:50%;left:50%;margin-top:-260px;margin-left:-260px;"></div>
        <div class="orbit-ring" style="width:680px;height:680px;top:50%;left:50%;margin-top:-340px;margin-left:-340px;animation-duration:35s;animation-direction:reverse;border-color:rgba(34,211,238,0.06);"></div>

        <div class="form-glow relative">
            <form method="POST" id="registerForm" class="glass-form rounded-[2.5rem] p-10 md:p-12 relative z-10 reveal-scale">

                <!-- Header -->
                <div class="text-center mb-9">
                    <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-gradient-to-br from-green-500/20 to-cyan-500/20 border border-white/10 flex items-center justify-center text-4xl shadow-lg">
                        🌿
                    </div>
                    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight">
                        Crear <span class="text-gradient-anim">cuenta.</span>
                    </h2>
                    <p class="text-gray-400 text-sm font-light mt-2">
                        Unite y empezá a cuidar tus plantas hoy.
                    </p>
                </div>

                <!-- Error -->
                <?php if ($error): ?>
                <div class="error-toast rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
                    <span class="text-red-400">⚠️</span>
                    <p class="text-red-300 text-sm font-medium"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <!-- Nombre -->
                <div class="mb-4">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Nombre completo</label>
                    <input
                        type="text"
                        name="nombre"
                        placeholder="Tu nombre"
                        value="<?= htmlspecialchars($form['nombre']) ?>"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                        required
                        autocomplete="name"
                        minlength="2"
                        maxlength="100"
                    >
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Correo electrónico</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="tucorreo@ejemplo.com"
                        value="<?= htmlspecialchars($form['email']) ?>"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                        required
                        autocomplete="email"
                    >
                </div>

                <!-- Contraseña -->
                <div class="mb-4">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Contraseña</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="passwordInput"
                            placeholder="Mínimo 6 caracteres"
                            class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-14"
                            required
                            minlength="6"
                            autocomplete="new-password"
                            oninput="checkStrength(this.value)"
                        >
                        <button type="button" onclick="togglePass('passwordInput','toggleBtn1')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm select-none"
                            id="toggleBtn1" aria-label="Mostrar contraseña">👁️</button>
                    </div>
                    <!-- Barra de fuerza -->
                    <div class="mt-2 flex gap-1.5">
                        <div class="strength-bar flex-1 bg-white/10" id="bar1"></div>
                        <div class="strength-bar flex-1 bg-white/10" id="bar2"></div>
                        <div class="strength-bar flex-1 bg-white/10" id="bar3"></div>
                        <div class="strength-bar flex-1 bg-white/10" id="bar4"></div>
                    </div>
                    <p class="text-[11px] text-gray-600 mt-1 ml-1" id="strengthLabel"></p>
                </div>

                <!-- Confirmar contraseña -->
                <div class="mb-6">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Confirmar contraseña</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="confirmar"
                            id="confirmarInput"
                            placeholder="Repetí tu contraseña"
                            class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-14"
                            required
                            autocomplete="new-password"
                            oninput="checkMatch()"
                        >
                        <button type="button" onclick="togglePass('confirmarInput','toggleBtn2')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm select-none"
                            id="toggleBtn2" aria-label="Mostrar contraseña">👁️</button>
                    </div>
                    <p class="text-[11px] mt-1 ml-1 hidden" id="matchLabel"></p>
                </div>

                <!-- Términos -->
                <label class="flex items-start gap-3 mb-8 cursor-pointer">
                    <input type="checkbox" id="terminos" class="check-custom mt-0.5" required>
                    <span class="text-gray-400 text-xs font-light leading-relaxed">
                        Acepto los
                        <a href="#" class="text-green-400 hover:underline">términos y condiciones</a>
                        y la
                        <a href="#" class="text-green-400 hover:underline">política de privacidad</a>
                        de SmartPlant CARE.
                    </span>
                </label>

                <!-- Submit -->
                <button type="submit" id="submitBtn"
                    class="btn-glow w-full bg-green-500 text-black py-4 rounded-2xl font-semibold text-base shadow-lg shadow-green-500/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    Crear mi cuenta
                </button>

                <!-- Divider -->
                <div class="my-7 flex items-center gap-4">
                    <div class="divider-glass flex-1"></div>
                    <span class="text-gray-500 text-xs font-light">¿ya tenés cuenta?</span>
                    <div class="divider-glass flex-1"></div>
                </div>

                <!-- Link al login -->
                <a href="Login.php"
                   class="w-full flex items-center justify-center gap-2 input-glass rounded-2xl py-4 text-sm font-medium hover:bg-white/10 transition-all text-white/80 hover:text-white">
                    <span>←</span> Iniciar sesión
                </a>

            </form>
        </div>
    </div>
</main>

<!-- Marquee decorativo -->
<div class="py-6 overflow-hidden border-t border-white/5">
    <div class="marquee-track">
        <span class="text-[3rem] font-bold tracking-tighter text-white/[0.025] whitespace-nowrap px-8">
            SmartPlant CARE — Tu jardín inteligente — Monitoreo 24/7 — Riego automático — Energía solar — &nbsp;
        </span>
        <span class="text-[3rem] font-bold tracking-tighter text-white/[0.025] whitespace-nowrap px-8">
            SmartPlant CARE — Tu jardín inteligente — Monitoreo 24/7 — Riego automático — Energía solar — &nbsp;
        </span>
    </div>
</div>

<footer class="py-8 text-center text-gray-600 text-xs font-light border-t border-white/5">
    <p>© 2026 SmartPlant CARE — Todos los derechos reservados.</p>
</footer>

<script>
    // ─── Reveal on load ───
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.reveal-scale, .reveal-blur').forEach(el => el.classList.add('active'));
        }, 80);
    });

    // ─── Toggle password visibility ───
    function togglePass(inputId, btnId) {
        const input = document.getElementById(inputId);
        const btn   = document.getElementById(btnId);
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        btn.textContent = show ? '🙈' : '👁️';
    }

    // ─── Fuerza de contraseña ───
    function checkStrength(val) {
        const bars   = [document.getElementById('bar1'), document.getElementById('bar2'),
                        document.getElementById('bar3'), document.getElementById('bar4')];
        const label  = document.getElementById('strengthLabel');

        // Resetear
        bars.forEach(b => { b.style.background = 'rgba(255,255,255,0.08)'; });

        if (!val) { label.textContent = ''; return; }

        let score = 0;
        if (val.length >= 6)  score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colors = ['#ef4444', '#fb923c', '#facc15', '#4ade80'];
        const labels = ['Muy débil', 'Débil', 'Buena', 'Fuerte'];

        for (let i = 0; i < score; i++) bars[i].style.background = colors[score - 1];
        label.textContent = labels[score - 1] || '';
        label.style.color = colors[score - 1] || '';
    }

    // ─── Match de contraseñas ───
    function checkMatch() {
        const pass = document.getElementById('passwordInput').value;
        const conf = document.getElementById('confirmarInput').value;
        const lbl  = document.getElementById('matchLabel');

        if (!conf) { lbl.classList.add('hidden'); return; }

        lbl.classList.remove('hidden');
        if (pass === conf) {
            lbl.textContent = '✓ Las contraseñas coinciden';
            lbl.style.color = '#4ade80';
        } else {
            lbl.textContent = '✗ Las contraseñas no coinciden';
            lbl.style.color = '#f87171';
        }
    }

    // ─── Scroll progress ───
    window.addEventListener('scroll', () => {
        const pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        document.getElementById('scrollProgress').style.width = pct + '%';
    });
</script>
</body>
</html>