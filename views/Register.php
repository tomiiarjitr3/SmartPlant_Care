<?php
// ═══════════════════════════════════════════════════════════════
//  Register.php — SmartPlant CARE
//  Flujo: Formulario → Código de verificación al email → Cuenta creada
// ═══════════════════════════════════════════════════════════════
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: /SmartPlant_Care/views/Dashboard.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

// ── Configuración SMTP (misma que Login.php) ───────────────────
define('SMTP_USER', 'anatom071@gmail.com');
define('SMTP_PASS', 'easg mhwx dimr coha');
define('SMTP_FROM', 'noreply@smartplantcare.com');
define('SMTP_NAME', 'SmartPlant CARE');

$error    = null;
$step     = $_SESSION['register_step'] ?? 'form'; // 'form' | 'verify'
$form     = $_SESSION['register_form'] ?? ['nombre' => '', 'email' => ''];

// ════════════════════════════════════════════════════════════════
//  PASO 1 — Recibe el formulario, envía el código por email
// ════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ── Enviar código ─────────────────────────────────────────
    if ($_POST['action'] === 'send_code') {
        $nombre    = trim($_POST['nombre']    ?? '');
        $email     = trim($_POST['email']     ?? '');
        $password  = trim($_POST['password']  ?? '');
        $confirmar = trim($_POST['confirmar'] ?? '');

        $form = ['nombre' => $nombre, 'email' => $email];

        // Validaciones
        if (!$nombre || !$email || !$password || !$confirmar) {
            $error = "Completá todos los campos.";
        } elseif (strlen($nombre) < 2) {
            $error = "El nombre debe tener al menos 2 caracteres.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo electrónico no es válido.";
        } elseif (strlen($password) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres.";
        } elseif ($password !== $confirmar) {
            $error = "Las contraseñas no coinciden.";
        } else {
            $db    = Database::connect();
            $check = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Ese correo ya está registrado. ¿Querés iniciar sesión?";
            } else {
                // Generar código de 6 dígitos y guardarlo en sesión
                $codigo  = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expira  = time() + 600; // 10 minutos

                $_SESSION['register_step']     = 'verify';
                $_SESSION['register_form']     = $form;
                $_SESSION['register_codigo']   = $codigo;
                $_SESSION['register_expira']   = $expira;
                $_SESSION['register_password'] = password_hash($password, PASSWORD_DEFAULT);
                $_SESSION['register_intentos'] = 0;

                // Enviar email con el código
                $enviado = enviarCodigo($email, $nombre, $codigo);

                if ($enviado === true) {
                    $step = 'verify';
                } else {
                    // Falló el envío — limpiar sesión y mostrar error
                    unset($_SESSION['register_step'], $_SESSION['register_codigo'],
                          $_SESSION['register_expira'], $_SESSION['register_password']);
                    $step  = 'form';
                    $error = "No se pudo enviar el código al correo. Revisá que sea correcto. ($enviado)";
                }
            }
        }
    }

    // ── Verificar código ──────────────────────────────────────
    elseif ($_POST['action'] === 'verify_code') {
        $ingresado = trim(str_replace(' ', '', $_POST['codigo'] ?? ''));

        // Guardar intentos para prevenir fuerza bruta
        $_SESSION['register_intentos'] = ($_SESSION['register_intentos'] ?? 0) + 1;

        if ($_SESSION['register_intentos'] > 5) {
            // Demasiados intentos → resetear
            unset($_SESSION['register_step'], $_SESSION['register_codigo'],
                  $_SESSION['register_expira'], $_SESSION['register_password'],
                  $_SESSION['register_intentos'], $_SESSION['register_form']);
            $step  = 'form';
            $error = "Demasiados intentos fallidos. Volvé a registrarte.";

        } elseif (time() > ($_SESSION['register_expira'] ?? 0)) {
            // Código expirado
            unset($_SESSION['register_step'], $_SESSION['register_codigo'],
                  $_SESSION['register_expira'], $_SESSION['register_password'],
                  $_SESSION['register_intentos']);
            $step  = 'form';
            $error = "El código expiró (10 minutos). Intentá de nuevo.";

        } elseif ($ingresado !== $_SESSION['register_codigo']) {
            $step  = 'verify';
            $error = "Código incorrecto. Te quedan " . (5 - $_SESSION['register_intentos']) . " intentos.";

        } else {
            // ✅ Código correcto — crear la cuenta
            $db     = Database::connect();
            $nombre = $_SESSION['register_form']['nombre'];
            $email  = $_SESSION['register_form']['email'];
            $hash   = $_SESSION['register_password'];

            $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, plan) VALUES (?, ?, ?, 'free')");
            $stmt->bind_param("sss", $nombre, $email, $hash);

            if ($stmt->execute()) {
                $nuevo_id = $db->insert_id;

                // Planta por defecto
                $planta = $db->prepare("
                    INSERT INTO plantas (usuario_id, nombre, especie, descripcion, humedad_min, humedad_max, temp_min, temp_max)
                    VALUES (?, 'Mi primera planta', 'Por definir', 'Planta registrada al crear la cuenta', 35, 65, 15.0, 35.0)
                ");
                $planta->bind_param("i", $nuevo_id);
                $planta->execute();

                // Limpiar sesión de registro y hacer login
                $plan = 'free';
                unset($_SESSION['register_step'], $_SESSION['register_codigo'],
                      $_SESSION['register_expira'], $_SESSION['register_password'],
                      $_SESSION['register_intentos'], $_SESSION['register_form']);

                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['usuario']    = $email;
                $_SESSION['nombre']     = $nombre;
                $_SESSION['plan']       = $plan;

                header("Location: /SmartPlant_Care/views/Dashboard.php");
                exit;
            } else {
                $error = "Error al crear la cuenta. Intentá de nuevo.";
                $step  = 'form';
            }
        }
    }

    // ── Reenviar código ───────────────────────────────────────
    elseif ($_POST['action'] === 'resend_code') {
        if ($step === 'verify' && isset($_SESSION['register_form'])) {
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['register_codigo']   = $codigo;
            $_SESSION['register_expira']   = time() + 600;
            $_SESSION['register_intentos'] = 0;

            $nombre  = $_SESSION['register_form']['nombre'];
            $email   = $_SESSION['register_form']['email'];
            $enviado = enviarCodigo($email, $nombre, $codigo);

            if ($enviado !== true) {
                $error = "No se pudo reenviar el código. ($enviado)";
            }
        }
        $step = 'verify';
    }

    // ── Volver al formulario ──────────────────────────────────
    elseif ($_POST['action'] === 'back_to_form') {
        unset($_SESSION['register_step'], $_SESSION['register_codigo'],
              $_SESSION['register_expira'], $_SESSION['register_password'],
              $_SESSION['register_intentos']);
        $_SESSION['register_step'] = 'form';
        $step = 'form';
    }
}

// ════════════════════════════════════════════════════════════════
//  Función: enviar email con el código
// ════════════════════════════════════════════════════════════════
function enviarCodigo(string $email, string $nombre, string $codigo): true|string {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($email, $nombre);

        $mail->isHTML(true);
        $mail->Subject = "Tu código de verificación — SmartPlant CARE";
        $mail->Body    = "
        <!DOCTYPE html>
        <html lang='es'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#0a0a0a;font-family:Arial,sans-serif;'>
          <table width='100%' cellpadding='0' cellspacing='0' style='background:#0a0a0a;padding:40px 20px;'>
            <tr><td align='center'>
              <table width='520' cellpadding='0' cellspacing='0' style='background:#111;border:1px solid rgba(255,255,255,0.08);border-radius:24px;overflow:hidden;'>
                <!-- Header verde -->
                <tr>
                  <td style='background:linear-gradient(135deg,#14532d,#052e16);padding:40px 40px 30px;text-align:center;'>
                    <p style='color:#4ade80;font-size:13px;letter-spacing:4px;text-transform:uppercase;margin:0 0 12px;'>SmartPlant CARE</p>
                    <p style='color:white;font-size:28px;font-weight:700;margin:0;'>🌱 Verificá tu correo</p>
                  </td>
                </tr>
                <!-- Cuerpo -->
                <tr>
                  <td style='padding:40px;'>
                    <p style='color:#d1d5db;font-size:15px;margin:0 0 8px;'>Hola, <strong style='color:white;'>{$nombre}</strong></p>
                    <p style='color:#9ca3af;font-size:14px;margin:0 0 36px;line-height:1.6;'>
                      Usá el siguiente código para verificar tu cuenta. Expira en <strong style='color:white;'>10 minutos</strong>.
                    </p>
                    <!-- Código -->
                    <div style='background:rgba(74,222,128,0.06);border:1px solid rgba(74,222,128,0.2);border-radius:16px;padding:32px;text-align:center;margin-bottom:36px;'>
                      <p style='color:#9ca3af;font-size:11px;letter-spacing:3px;text-transform:uppercase;margin:0 0 16px;'>Tu código de verificación</p>
                      <p style='color:#4ade80;font-size:52px;font-weight:900;letter-spacing:16px;margin:0;font-family:monospace;'>{$codigo}</p>
                    </div>
                    <p style='color:#6b7280;font-size:12px;text-align:center;margin:0;'>
                      Si no creaste esta cuenta, ignorá este mensaje.
                    </p>
                  </td>
                </tr>
                <!-- Footer -->
                <tr>
                  <td style='border-top:1px solid rgba(255,255,255,0.05);padding:20px 40px;text-align:center;'>
                    <p style='color:#374151;font-size:11px;margin:0;'>© 2026 SmartPlant CARE — Todos los derechos reservados.</p>
                  </td>
                </tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>";

        $mail->AltBody = "Tu código de verificación para SmartPlant CARE es: {$codigo}\nExpira en 10 minutos.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

// Recalcular step desde sesión al cargar la página
if (empty($_POST)) {
    $step = $_SESSION['register_step'] ?? 'form';
    $form = $_SESSION['register_form'] ?? ['nombre' => '', 'email' => ''];
}

// Tiempo restante para el código
$segundos_restantes = max(0, ($_SESSION['register_expira'] ?? 0) - time());
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $step === 'verify' ? 'Verificar código' : 'Crear cuenta' ?> — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
    <style>
        .strength-bar {
            height: 3px; border-radius: 999px;
            transition: background 0.4s ease;
        }
        .check-custom {
            appearance: none; width: 16px; height: 16px;
            border: 1px solid rgba(255,255,255,0.2); border-radius: 4px;
            background: rgba(255,255,255,0.05); cursor: pointer;
            position: relative; flex-shrink: 0; transition: all 0.2s ease;
        }
        .check-custom:checked { background: #ffffff; border-color: #ffffff; }
        .check-custom:checked::after {
            content: '✓'; position: absolute;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 10px; color: black; font-weight: bold;
        }

        /* Inputs del código OTP */
        .otp-input {
            width: 52px; height: 64px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            color: white;
            font-size: 28px; font-weight: 700;
            text-align: center;
            transition: all 0.3s var(--ease-out);
            font-family: monospace;
            caret-color: #ffffff;
        }
        .otp-input:focus {
            outline: none;
            border-color: rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.06);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.06);
            transform: scale(1.06);
        }
        .otp-input.filled {
            border-color: rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.04);
        }
        .otp-input.error-shake {
            border-color: rgba(239,68,68,0.6);
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-6px); }
            75%      { transform: translateX(6px); }
        }

        /* Timer ring */
        .timer-ring { transform: rotate(-90deg); }
        .timer-circle {
            fill: none; stroke-width: 3;
            stroke-linecap: round;
            transition: stroke-dashoffset 1s linear, stroke 0.5s ease;
        }
    </style>
</head>

<body class="bg-overlay text-white min-h-screen flex flex-col">


<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto w-full max-w-5xl px-4">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-white/60">🌱</span> SmartPlant
        </a>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium text-white/80">
            <a href="/SmartPlant_Care/index.php#utilidades" class="hover:text-white transition-colors">Utilidades</a>
            <a href="/SmartPlant_Care/index.php#producto"   class="hover:text-white transition-colors">Producto</a>
            <a href="/SmartPlant_Care/views/Store.php"      class="hover:text-white transition-colors">Tienda</a>
            <a href="Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">
                Iniciar sesión
            </a>
        </nav>
    </div>
</header>

<main class="flex-1 flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md relative">

        <!-- Orbit rings -->
        <div class="orbit-ring" style="width:520px;height:520px;top:50%;left:50%;margin-top:-260px;margin-left:-260px;"></div>
        <div class="orbit-ring" style="width:680px;height:680px;top:50%;left:50%;margin-top:-340px;margin-left:-340px;animation-duration:35s;animation-direction:reverse;border-color:rgba(255,255,255,0.06);"></div>

        <div class="form-glow relative">

        <?php if ($step === 'form'): ?>
        <!-- ════════════════════════════════════════════
             PASO 1 — Formulario de registro
             ════════════════════════════════════════════ -->
        <form method="POST" class="glass-form rounded-[2.5rem] p-10 md:p-12 relative z-10 reveal-scale">
            <input type="hidden" name="action" value="send_code">

            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-gradient-to-br from-white/10 to-white/5 border border-white/10 flex items-center justify-center text-4xl shadow-lg">🌿</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight">
                    Crear <span class="text-gradient-anim">cuenta.</span>
                </h2>
                <p class="text-gray-400 text-sm font-light mt-2">Unite y empezá a cuidar tus plantas hoy.</p>
            </div>

            <?php if ($error): ?>
            <div class="error-toast rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
                <span class="text-red-400">⚠️</span>
                <p class="text-red-300 text-sm font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <!-- Nombre -->
            <div class="mb-4">
                <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Nombre completo</label>
                <input type="text" name="nombre" placeholder="Tu nombre"
                    value="<?= htmlspecialchars($form['nombre']) ?>"
                    class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                    required minlength="2" maxlength="100" autocomplete="name">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Correo electrónico</label>
                <input type="email" name="email" placeholder="tucorreo@ejemplo.com"
                    value="<?= htmlspecialchars($form['email']) ?>"
                    class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                    required autocomplete="email">
            </div>

            <!-- Contraseña -->
            <div class="mb-4">
                <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Contraseña</label>
                <div class="relative">
                    <input type="password" name="password" id="passwordInput"
                        placeholder="Mínimo 6 caracteres"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-14"
                        required minlength="6" autocomplete="new-password"
                        oninput="checkStrength(this.value)">
                    <button type="button" onclick="togglePass('passwordInput','tb1')"
                        id="tb1" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm select-none">👁️</button>
                </div>
                <div class="mt-2 flex gap-1.5">
                    <div class="strength-bar flex-1 bg-white/10" id="bar1"></div>
                    <div class="strength-bar flex-1 bg-white/10" id="bar2"></div>
                    <div class="strength-bar flex-1 bg-white/10" id="bar3"></div>
                    <div class="strength-bar flex-1 bg-white/10" id="bar4"></div>
                </div>
                <p class="text-[11px] text-gray-600 mt-1 ml-1" id="strengthLabel"></p>
            </div>

            <!-- Confirmar -->
            <div class="mb-6">
                <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Confirmar contraseña</label>
                <div class="relative">
                    <input type="password" name="confirmar" id="confirmarInput"
                        placeholder="Repetí tu contraseña"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-14"
                        required autocomplete="new-password" oninput="checkMatch()">
                    <button type="button" onclick="togglePass('confirmarInput','tb2')"
                        id="tb2" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm select-none">👁️</button>
                </div>
                <p class="text-[11px] mt-1 ml-1 hidden" id="matchLabel"></p>
            </div>

            <!-- Términos -->
            <label class="flex items-start gap-3 mb-8 cursor-pointer">
                <input type="checkbox" class="check-custom mt-0.5" required>
                <span class="text-gray-400 text-xs font-light leading-relaxed">
                    Acepto los <a href="#" class="text-white/70 hover:underline">términos y condiciones</a>
                    y la <a href="#" class="text-white/70 hover:underline">política de privacidad</a>.
                </span>
            </label>

            <button type="submit" class="btn-glow w-full bg-white text-black py-4 rounded-2xl font-semibold text-base shadow-lg">
                Continuar — Verificar correo →
            </button>

            <div class="my-7 flex items-center gap-4">
                <div class="divider-glass flex-1"></div>
                <span class="text-gray-500 text-xs font-light">¿ya tenés cuenta?</span>
                <div class="divider-glass flex-1"></div>
            </div>

            <a href="Login.php" class="w-full flex items-center justify-center gap-2 input-glass rounded-2xl py-4 text-sm font-medium hover:bg-white/10 transition-all text-white/80 hover:text-white">
                ← Iniciar sesión
            </a>
        </form>

        <?php else: ?>
        <!-- ════════════════════════════════════════════
             PASO 2 — Ingresar el código de verificación
             ════════════════════════════════════════════ -->
        <div class="glass-form rounded-[2.5rem] p-10 md:p-12 relative z-10 reveal-scale">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-gradient-to-br from-white/10 to-white/5 border border-white/10 flex items-center justify-center text-4xl shadow-lg">📧</div>
                <h2 class="text-3xl font-semibold tracking-tight">Verificá tu <span class="text-gradient-anim">correo.</span></h2>
                <p class="text-gray-400 text-sm font-light mt-3 leading-relaxed">
                    Enviamos un código de 6 dígitos a<br>
                    <span class="text-white font-medium"><?= htmlspecialchars($form['email']) ?></span>
                </p>
            </div>

            <?php if ($error): ?>
            <div class="error-toast rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
                <span class="text-red-400">⚠️</span>
                <p class="text-red-300 text-sm font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <!-- Timer visual -->
            <div class="flex flex-col items-center mb-8">
                <div class="relative w-16 h-16">
                    <svg class="timer-ring w-16 h-16" viewBox="0 0 56 56">
                        <circle cx="28" cy="28" r="24" class="timer-circle" stroke="rgba(255,255,255,0.05)" stroke-dasharray="150.8" stroke-dashoffset="0"/>
                        <circle cx="28" cy="28" r="24" class="timer-circle" stroke="#ffffff"
                            stroke-dasharray="150.8"
                            stroke-dashoffset="<?= 150.8 * (1 - $segundos_restantes / 600) ?>"
                            id="timerCircle"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-bold text-white" id="timerLabel">
                            <?= gmdate('i:s', $segundos_restantes) ?>
                        </span>
                    </div>
                </div>
                <p class="text-[11px] text-gray-600 mt-2 font-light">Tiempo restante</p>
            </div>

            <!-- Inputs OTP -->
            <form method="POST" id="otpForm">
                <input type="hidden" name="action" value="verify_code">
                <input type="hidden" name="codigo" id="codigoHidden">

                <div class="flex justify-center gap-3 mb-8" id="otpContainer">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        class="otp-input" id="otp<?= $i ?>"
                        autocomplete="<?= $i === 0 ? 'one-time-code' : 'off' ?>">
                    <?php endfor; ?>
                </div>

                <button type="submit" id="verifyBtn"
                    class="btn-glow w-full bg-white text-black py-4 rounded-2xl font-semibold text-base shadow-lg opacity-50 cursor-not-allowed"
                    disabled>
                    Verificar código
                </button>
            </form>

            <!-- Acciones secundarias -->
            <div class="mt-6 space-y-3">
                <!-- Reenviar -->
                <form method="POST">
                    <input type="hidden" name="action" value="resend_code">
                    <button type="submit" class="w-full text-center text-white/50 text-sm hover:text-white transition-colors py-2">
                        ¿No llegó el correo? Reenviar código
                    </button>
                </form>

                <!-- Volver -->
                <form method="POST">
                    <input type="hidden" name="action" value="back_to_form">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 input-glass rounded-2xl py-3.5 text-sm text-white/70 hover:bg-white/10 hover:text-white transition-all">
                        ← Cambiar correo
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        </div>
    </div>
</main>

<!-- Marquee -->
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
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => document.querySelectorAll('.reveal-scale').forEach(el => el.classList.add('active')), 80);
});

// ─── Toggle password ───
function togglePass(inputId, btnId) {
    const input = document.getElementById(inputId);
    const btn   = document.getElementById(btnId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'text' ? '🙈' : '👁️';
}

// ─── Fuerza de contraseña ───
function checkStrength(val) {
    const bars  = ['bar1','bar2','bar3','bar4'].map(id => document.getElementById(id));
    const label = document.getElementById('strengthLabel');
    bars.forEach(b => b.style.background = 'rgba(255,255,255,0.08)');
    if (!val) { label.textContent = ''; return; }
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['#666','#888','#bbb','#fff'];
    const labels = ['Muy débil','Débil','Buena','Fuerte'];
    for (let i = 0; i < score; i++) bars[i].style.background = colors[score-1];
    label.textContent = labels[score-1] || '';
    label.style.color = colors[score-1] || '';
}

// ─── Match contraseñas ───
function checkMatch() {
    const pass = document.getElementById('passwordInput')?.value;
    const conf = document.getElementById('confirmarInput')?.value;
    const lbl  = document.getElementById('matchLabel');
    if (!lbl) return;
    if (!conf) { lbl.classList.add('hidden'); return; }
    lbl.classList.remove('hidden');
    lbl.textContent = pass === conf ? '✓ Las contraseñas coinciden' : '✗ No coinciden';
    lbl.style.color = pass === conf ? '#ffffff' : '#888888';
}

// Scroll progress removed

<?php if ($step === 'verify'): ?>
// ════════════════════════════════════════════════════════════════
//  OTP — Lógica de los 6 inputs
// ════════════════════════════════════════════════════════════════
const otpInputs  = Array.from({length: 6}, (_, i) => document.getElementById('otp' + i));
const hidden     = document.getElementById('codigoHidden');
const verifyBtn  = document.getElementById('verifyBtn');
const otpForm    = document.getElementById('otpForm');

// Focus en el primer input al cargar
setTimeout(() => otpInputs[0]?.focus(), 200);

otpInputs.forEach((inp, idx) => {
    inp.addEventListener('input', e => {
        // Solo dígitos
        inp.value = inp.value.replace(/\D/g, '').slice(-1);
        inp.classList.toggle('filled', inp.value !== '');

        // Avanzar al siguiente
        if (inp.value && idx < 5) otpInputs[idx + 1].focus();

        syncHidden();
    });

    inp.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !inp.value && idx > 0) {
            otpInputs[idx - 1].value = '';
            otpInputs[idx - 1].classList.remove('filled');
            otpInputs[idx - 1].focus();
            syncHidden();
        }
        // Pegar con Ctrl+V
        if (e.key === 'v' && (e.ctrlKey || e.metaKey)) return;
    });

    inp.addEventListener('paste', e => {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        text.split('').forEach((ch, i) => {
            if (otpInputs[i]) {
                otpInputs[i].value = ch;
                otpInputs[i].classList.add('filled');
            }
        });
        const next = Math.min(text.length, 5);
        otpInputs[next].focus();
        syncHidden();
    });
});

function syncHidden() {
    const code = otpInputs.map(i => i.value).join('');
    hidden.value = code;
    const ready = code.length === 6;
    verifyBtn.disabled = !ready;
    verifyBtn.classList.toggle('opacity-50', !ready);
    verifyBtn.classList.toggle('cursor-not-allowed', !ready);
}

// Shake en error
<?php if ($error && str_contains($error, 'ncorrecto')): ?>
otpInputs.forEach(i => { i.classList.add('error-shake'); i.value = ''; i.classList.remove('filled'); });
setTimeout(() => { otpInputs.forEach(i => i.classList.remove('error-shake')); otpInputs[0].focus(); }, 500);
<?php endif; ?>

// ─── Timer countdown ───
let secsLeft = <?= $segundos_restantes ?>;
const circle  = document.getElementById('timerCircle');
const timerLbl = document.getElementById('timerLabel');
const totalDash = 150.8;

const tick = setInterval(() => {
    secsLeft = Math.max(0, secsLeft - 1);
    const mins = String(Math.floor(secsLeft / 60)).padStart(2, '0');
    const secs = String(secsLeft % 60).padStart(2, '0');
    timerLbl.textContent = mins + ':' + secs;

    const offset = totalDash * (1 - secsLeft / 600);
    circle.style.strokeDashoffset = offset;

    // Cambiar color cuando queda poco tiempo
    if (secsLeft <= 60)       circle.style.stroke = '#ef4444';
    else if (secsLeft <= 120) circle.style.stroke = '#fb923c';

    if (secsLeft <= 0) {
        clearInterval(tick);
        timerLbl.textContent = '0:00';
        verifyBtn.disabled = true;
        // Mostrar aviso de expiración
        document.getElementById('otpContainer').innerHTML =
            '<p class="text-red-400 text-sm text-center w-full">⏱ El código expiró. Reenviar uno nuevo.</p>';
    }
}, 1000);
<?php endif; ?>
</script>
</body>
</html>