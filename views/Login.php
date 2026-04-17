<?php
// ═══════════════════════════════════════════════════════════════
//  Login.php — SmartPlant CARE
//  Autenticación real contra MySQL con password_hash
// ═══════════════════════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: /SmartPlant_Care/views/Dashboard.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]    ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($email && $password) {
        $db   = Database::connect();
        $stmt = $db->prepare("SELECT id, nombre, password, plan FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res  = $stmt->get_result();

        if ($res && $row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['usuario_id']  = $row['id'];
                $_SESSION['usuario']     = $email;
                $_SESSION['nombre']      = $row['nombre'];
                $_SESSION['plan']        = $row['plan'];
                header("Location: /SmartPlant_Care/views/Dashboard.php");
                exit;
            }
        }
        $error = "Correo o contraseña incorrectos.";
    } else {
        $error = "Completá todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
</head>

<body class="bg-overlay text-white min-h-screen flex flex-col">

<!-- Scroll Progress Bar -->
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
                Mi cuenta
            </a>
        </nav>
    </div>
</header>

<!-- ═══ LOGIN SECTION ═══ -->
<main class="flex-1 flex items-center justify-center px-6 py-20">
    <div class="w-full max-w-md relative">

        <!-- Orbit rings decorativos -->
        <div class="orbit-ring" style="width:500px;height:500px;top:50%;left:50%;margin-top:-250px;margin-left:-250px;"></div>
        <div class="orbit-ring" style="width:660px;height:660px;top:50%;left:50%;margin-top:-330px;margin-left:-330px;animation-duration:32s;animation-direction:reverse;border-color:rgba(34,211,238,0.07);"></div>

        <div class="form-glow relative">
            <form method="POST" class="glass-form rounded-[2.5rem] p-12 md:p-14 relative z-10 reveal-scale" id="loginForm">

                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-green-500/20 to-cyan-500/20 border border-white/10 flex items-center justify-center text-4xl shadow-lg">
                        🌱
                    </div>
                    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight">
                        Bienvenido de <span class="text-gradient-anim">vuelta.</span>
                    </h2>
                    <p class="text-gray-400 text-sm font-light mt-3">
                        Ingresá para gestionar tu jardín inteligente.
                    </p>
                </div>

                <!-- Error -->
                <?php if ($error): ?>
                <div class="error-toast rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
                    <span class="text-red-400 text-lg">⚠️</span>
                    <p class="text-red-300 text-sm font-medium"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <!-- Email -->
                <div class="mb-5">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Correo</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="tucorreo@smartplant.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                        required
                        autocomplete="email"
                    >
                </div>

                <!-- Contraseña -->
                <div class="mb-6">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Contraseña</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="passwordInput"
                            placeholder="••••••••"
                            class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-14"
                            required
                            autocomplete="current-password"
                        >
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm select-none"
                            id="toggleBtn" aria-label="Mostrar contraseña"
                        >👁️</button>
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between mb-8">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="recordar" class="w-4 h-4 rounded accent-green-400">
                        <span class="text-gray-400 text-xs font-light">Recordarme</span>
                    </label>
                    <a href="#" class="text-green-400/80 text-xs font-medium hover:text-green-400 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-glow w-full bg-green-500 text-black py-4 rounded-2xl font-semibold text-base shadow-lg shadow-green-500/20">
                    Ingresar
                </button>

                <!-- Divider -->
                <div class="my-8 flex items-center gap-4">
                    <div class="divider-glass flex-1"></div>
                    <span class="text-gray-500 text-xs font-light">o continuá con</span>
                    <div class="divider-glass flex-1"></div>
                </div>

                <!-- Social (UI únicamente) -->
                <div class="flex gap-4">
                    <button type="button" class="flex-1 input-glass rounded-2xl py-3.5 text-sm font-medium flex items-center justify-center gap-2 hover:bg-white/10 transition-all">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        Google
                    </button>
                    <button type="button" class="flex-1 input-glass rounded-2xl py-3.5 text-sm font-medium flex items-center justify-center gap-2 hover:bg-white/10 transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                        Apple
                    </button>
                </div>

                <!-- Register -->
                <p class="text-center text-gray-400 text-sm font-light mt-8">
                    ¿No tenés cuenta?
                    <a href="Register.php" class="text-green-400 font-medium hover:underline">Crear cuenta</a>
                </p>

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
    // Reveal on load
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.reveal-scale, .reveal-blur').forEach(el => el.classList.add('active'));
        }, 80);
    });

    // Toggle password
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const btn   = document.getElementById('toggleBtn');
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        btn.textContent = show ? '🙈' : '👁️';
    }
</script>
</body>
</html>