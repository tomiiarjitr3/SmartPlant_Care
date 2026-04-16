<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_demo = "admin@smartplant.com";
$password_demo = "1234";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($email === $usuario_demo && $password === $password_demo) {
        $_SESSION["usuario"] = $email;

        header("Location: /SmartPlant_Care/views/dashboard.php");
        exit;
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .bg-overlay {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('/SmartPlant_Care/assets/plant-bg.avif');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* ═══════════════════════════════════════════
           GLASS STYLES
           ═══════════════════════════════════════════ */
        .glass-clean {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.3);
        }

        .glass-form {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 
                0 25px 60px 0 rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* ═══════════════════════════════════════════
           INPUTS APPLE STYLE
           ═══════════════════════════════════════════ */
        .input-glass {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: white;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .input-glass::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }
        .input-glass:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(74, 222, 128, 0.5);
            box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.1), 0 0 30px rgba(74, 222, 128, 0.05);
        }

        /* ═══════════════════════════════════════════
           HERO TEXT GRADIENT ANIM
           ═══════════════════════════════════════════ */
        .text-gradient-anim {
            background: linear-gradient(90deg, #4ade80, #22d3ee, #818cf8, #4ade80);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 8s ease infinite;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ═══════════════════════════════════════════
           LINE ACCENT
           ═══════════════════════════════════════════ */
        .line-accent {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4ade80, transparent);
            border-radius: 2px;
        }

        /* ═══════════════════════════════════════════
           BUTTON GLOW
           ═══════════════════════════════════════════ */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.6s ease;
        }
        .btn-glow:hover::before {
            left: 100%;
        }
        .btn-glow:hover {
            transform: scale(1.03);
            box-shadow: 0 15px 40px rgba(74, 222, 128, 0.25);
        }

        /* ═══════════════════════════════════════════
           ANIMACIONES REVEAL
           ═══════════════════════════════════════════ */
        .reveal-blur {
            opacity: 0;
            filter: blur(20px);
            transform: translateY(40px);
            transition: all 1.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-blur.active {
            opacity: 1;
            filter: blur(0px);
            transform: translateY(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-scale.active {
            opacity: 1;
            transform: scale(1);
        }

        /* ═══════════════════════════════════════════
           ORBIT DECORATIVOS (como en producto)
           ═══════════════════════════════════════════ */
        .orbit-ring {
            position: absolute;
            border: 1px solid rgba(74, 222, 128, 0.08);
            border-radius: 50%;
            animation: orbitSpin 20s linear infinite;
            pointer-events: none;
        }
        @keyframes orbitSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .orbit-ring::after {
            content: '';
            position: absolute;
            top: -4px;
            left: 50%;
            width: 8px;
            height: 8px;
            background: rgba(74, 222, 128, 0.4);
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(74, 222, 128, 0.6);
        }

        /* ═══════════════════════════════════════════
           GLOW BACKGROUND
           ═══════════════════════════════════════════ */
        .form-glow::before {
            content: '';
            position: absolute;
            inset: -80px;
            background: radial-gradient(circle, rgba(74, 222, 128, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            animation: pulseGlow 4s ease-in-out infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        /* ═══════════════════════════════════════════
           ERROR TOAST
           ═══════════════════════════════════════════ */
        .error-toast {
            background: rgba(239, 68, 68, 0.12);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(239, 68, 68, 0.25);
            animation: shakeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes shakeIn {
            0% { transform: translateX(-10px); opacity: 0; }
            25% { transform: translateX(8px); }
            50% { transform: translateX(-5px); }
            75% { transform: translateX(3px); }
            100% { transform: translateX(0); opacity: 1; }
        }

        /* ═══════════════════════════════════════════
           MARQUEE
           ═══════════════════════════════════════════ */
        .marquee-track {
            display: flex;
            width: max-content;
            animation: marquee 30s linear infinite;
        }
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        /* ═══════════════════════════════════════════
           DIVIDER
           ═══════════════════════════════════════════ */
        .divider-glass {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        }

        /* ═══════════════════════════════════════════
           CHECKBOX CUSTOM
           ═══════════════════════════════════════════ */
        .checkbox-custom {
            accent-color: #4ade80;
        }
    </style>
</head>

<body class="bg-overlay text-white min-h-screen flex flex-col">

<!-- ═══════════════════════════════════════════
     HEADER (idéntico al index)
     ═══════════════════════════════════════════ -->
<header class="sticky top-6 z-50 mx-auto max-w-5xl px-4 w-full">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight text-white flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </a>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium tracking-wide text-white/80">
            <a href="/SmartPlant_Care/index.php" class="hover:text-white transition-all">Inicio</a>
            <a href="/SmartPlant_Care/index.php#utilidades" class="hover:text-white transition-all">Utilidades</a>
            <a href="/SmartPlant_Care/index.php#producto" class="hover:text-white transition-all">Producto</a>
            <a href="/SmartPlant_Care/store.php" class="hover:text-white transition-all">Tienda</a>
            <a href="Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg">
                Mi cuenta
            </a>
        </nav>
    </div>
</header>

<!-- ═══════════════════════════════════════════
     LOGIN SECTION
     ═══════════════════════════════════════════ -->
<main class="flex-1 flex items-center justify-center px-6 py-20">
    <div class="w-full max-w-md relative">

        <!-- Orbit rings decorativos -->
        <div class="orbit-ring" style="width: 500px; height: 500px; top: 50%; left: 50%; margin-top: -250px; margin-left: -250px;"></div>
        <div class="orbit-ring" style="width: 650px; height: 650px; top: 50%; left: 50%; margin-top: -325px; margin-left: -325px; animation-duration: 30s; animation-direction: reverse; border-color: rgba(34, 211, 238, 0.06);"></div>

        <!-- Form glow -->
        <div class="form-glow relative">

            <!-- Formulario -->
            <form method="POST" class="glass-form rounded-[3rem] p-12 md:p-14 relative z-10 reveal-scale" id="loginForm">

                <!-- Header del form -->
                <div class="text-center mb-10">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-green-500/20 to-cyan-500/20 border border-white/10 flex items-center justify-center text-4xl shadow-lg shadow-green-500/10">
                        🌱
                    </div>
                    <h2 class="text-3xl md:text-4xl font-semibold tracking-tighter">
                        Bienvenido de <span class="text-gradient-anim">vuelta.</span>
                    </h2>
                    <p class="text-gray-400 text-sm font-light mt-3">
                        Ingresá a tu cuenta para gestionar tu jardín.
                    </p>
                </div>

                <!-- Error message -->
                <?php if (isset($error)): ?>
                    <div class="error-toast rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
                        <span class="text-red-400 text-lg">⚠️</span>
                        <p class="text-red-300 text-sm font-medium"><?= $error ?></p>
                    </div>
                <?php endif; ?>

                <!-- Email -->
                <div class="mb-5">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Correo</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="tucorreo@smartplant.com"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Contraseña</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="passwordInput"
                            placeholder="••••••••"
                            class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-12"
                            required
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm"
                            id="toggleBtn"
                        >
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between mb-8">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="checkbox-custom w-4 h-4 rounded">
                        <span class="text-gray-400 text-xs font-light">Recordarme</span>
                    </label>
                    <a href="#" class="text-green-400/80 text-xs font-medium hover:text-green-400 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Submit button -->
                <button
                    type="submit"
                    class="btn-glow w-full bg-green-500 text-black py-4 rounded-2xl font-semibold text-lg shadow-lg shadow-green-500/20"
                >
                    Ingresar
                </button>

                <!-- Divider -->
                <div class="my-8 flex items-center gap-4">
                    <div class="divider-glass flex-1"></div>
                    <span class="text-gray-500 text-xs font-light">o continuá con</span>
                    <div class="divider-glass flex-1"></div>
                </div>

                <!-- Social login -->
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

                <!-- Register link -->
                <p class="text-center text-gray-400 text-sm font-light mt-8">
                    ¿No tenés cuenta? 
                    <a href="#" class="text-green-400 font-medium hover:underline transition-all">Crear cuenta</a>
                </p>

            </form>
        </div>
    </div>
</main>

<!-- ═══════════════════════════════════════════
     MARQUEE DECORATIVO (sutil)
     ═══════════════════════════════════════════ -->
<div class="py-6 overflow-hidden border-t border-white/5">
    <div class="marquee-track">
        <span class="text-[3rem] font-bold tracking-tighter text-white/[0.02] whitespace-nowrap px-8">
            SmartPlant CARE — Tu jardín inteligente — Monitoreo 24/7 — Riego automático — Energía solar — 
        </span>
        <span class="text-[3rem] font-bold tracking-tighter text-white/[0.02] whitespace-nowrap px-8">
            SmartPlant CARE — Tu jardín inteligente — Monitoreo 24/7 — Riego automático — Energía solar — 
        </span>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     FOOTER MINI
     ═══════════════════════════════════════════ -->
<footer class="py-8 text-center text-gray-600 text-xs font-light border-t border-white/5">
    <p>© 2026 SmartPlant CARE — Todos los derechos reservados.</p>
</footer>

<!-- ═══════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════ -->
<script>
    // ─── Reveal on load ───
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.reveal-scale, .reveal-blur').forEach(el => {
                el.classList.add('active');
            });
        }, 100);
    });

    // ─── Toggle password visibility ───
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const btn = document.getElementById('toggleBtn');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    }
</script>

</body>
</html>