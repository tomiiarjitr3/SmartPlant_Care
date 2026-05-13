<?php
// ═══════════════════════════════════════════════════════════════
//  Login.php — SmartPlant CARE
//  Login normal + Google OAuth 2.0 + recuperación de contraseña
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

// ════════════════════════════════════════════════════════════════
//  CONFIGURACIÓN GOOGLE OAUTH
//  Pasos para activarlo:
//  1. Ir a https://console.cloud.google.com
//  2. Crear proyecto → APIs & Services → Credentials → Create OAuth 2.0 Client ID
//  3. Application type: Web application
//  4. Authorized redirect URI: http://localhost/SmartPlant_Care/views/Login.php?oauth=google
//  5. Copiar el Client ID y Client Secret y pegarlos acá abajo
// ════════════════════════════════════════════════════════════════
define('GOOGLE_CLIENT_ID',     '210888582761-ttrr3brcifkkqlqvvg7hv98ru2g2565a.apps.googleusercontent.com');   // <-- reemplazar
define('GOOGLE_CLIENT_SECRET', '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');                          // <-- reemplazar
define('GOOGLE_REDIRECT_URI',  'http://localhost/SmartPlant_Care/views/Login.php?oauth=google');

// SMTP — mismo que Register.php
define('SMTP_USER', 'anatom071@gmail.com');
define('SMTP_PASS', 'easg mhwx dimr coha');

$error      = null;
$msg_forgot = null;

// ════════════════════════════════════════════════════════════════
//  GOOGLE OAUTH — Callback (Google redirige acá con ?oauth=google)
// ════════════════════════════════════════════════════════════════
if (isset($_GET['oauth']) && $_GET['oauth'] === 'google') {

    if (isset($_GET['error'])) {
        $error = "Autenticación con Google cancelada.";

    } elseif (isset($_GET['code'])) {

        // Verificar state anti-CSRF
        if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
            $error = "Error de seguridad. Intentá de nuevo.";
        } else {
            // Intercambiar code por access_token
            $tokenResp = @file_get_contents('https://oauth2.googleapis.com/token', false,
                stream_context_create(['http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query([
                        'code'          => $_GET['code'],
                        'client_id'     => GOOGLE_CLIENT_ID,
                        'client_secret' => GOOGLE_CLIENT_SECRET,
                        'redirect_uri'  => GOOGLE_REDIRECT_URI,
                        'grant_type'    => 'authorization_code',
                    ]),
                ]])
            );

            $tokenData = json_decode($tokenResp ?: '{}', true);

            if (!empty($tokenData['access_token'])) {
                // Obtener perfil del usuario
                $userResp = @file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo', false,
                    stream_context_create(['http' => [
                        'header' => 'Authorization: Bearer ' . $tokenData['access_token']
                    ]])
                );
                $googleUser = json_decode($userResp ?: '{}', true);

                if (!empty($googleUser['email']) && ($googleUser['email_verified'] ?? false)) {
                    $db      = Database::connect();
                    $gEmail  = $googleUser['email'];
                    $gNombre = $googleUser['name'] ?? explode('@', $gEmail)[0];

                    // Buscar usuario existente
                    $stmt = $db->prepare("SELECT id, nombre, plan FROM usuarios WHERE email = ? LIMIT 1");
                    $stmt->bind_param("s", $gEmail);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();

                    if ($row) {
                        // Ya tiene cuenta → login directo
                        $_SESSION['usuario_id'] = $row['id'];
                        $_SESSION['usuario']    = $gEmail;
                        $_SESSION['nombre']     = $row['nombre'];
                        $_SESSION['plan']       = $row['plan'];
                    } else {
                        // Primera vez → crear cuenta automática (ya verificada por Google)
                        $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                        $ins  = $db->prepare("INSERT INTO usuarios (nombre, email, password, plan) VALUES (?, ?, ?, 'free')");
                        $ins->bind_param("sss", $gNombre, $gEmail, $hash);
                        $ins->execute();
                        $nuevo_id = $db->insert_id;

                        $pl = $db->prepare("INSERT INTO plantas (usuario_id, nombre, especie, descripcion, humedad_min, humedad_max, temp_min, temp_max) VALUES (?, 'Mi primera planta', 'Por definir', 'Cuenta creada con Google', 35, 65, 15.0, 35.0)");
                        $pl->bind_param("i", $nuevo_id);
                        $pl->execute();

                        $_SESSION['usuario_id'] = $nuevo_id;
                        $_SESSION['usuario']    = $gEmail;
                        $_SESSION['nombre']     = $gNombre;
                        $_SESSION['plan']       = 'free';
                    }

                    unset($_SESSION['oauth_state']);
                    header("Location: /SmartPlant_Care/views/Dashboard.php");
                    exit;
                } else {
                    $error = "No se pudo verificar el correo de Google.";
                }
            } else {
                $error = "Error al autenticar con Google. Verificá que el Client ID esté configurado.";
            }
        }
    }
}

// ════════════════════════════════════════════════════════════════
//  POST — Login normal / Recuperar contraseña
// ════════════════════════════════════════════════════════════════
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ── Recuperar contraseña ──────────────────────────────────
    if (isset($_POST['forgot_password'])) {
        $email_forgot = trim($_POST['email_forgot'] ?? '');
        if ($email_forgot) {
            $db   = Database::connect();

            // Auto-create reset columns if they don't exist
            $colCheck = $db->query("SHOW COLUMNS FROM usuarios LIKE 'reset_token'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $db->query("ALTER TABLE usuarios ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL");
                $db->query("ALTER TABLE usuarios ADD COLUMN reset_expira DATETIME DEFAULT NULL");
            }

            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email_forgot);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $token  = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', time() + 86400); // 24 horas

                // Guardar token en la base
                $upd = $db->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE id = ?");
                $upd->bind_param("ssi", $token, $expira, $user['id']);
                $upd->execute();

                $reset_link = "http://localhost:8080/SmartPlant_Care/views/ResetPassword.php?token={$token}&email=" . urlencode($email_forgot);
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
                    $mail->setFrom(SMTP_USER, 'SmartPlant CARE');
                    $mail->addAddress($email_forgot);
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperación de contraseña — SmartPlant CARE';
                    $mail->Body    = "
                    <div style='font-family:Arial,sans-serif;background:#0a0a0a;padding:40px 20px;'>
                      <div style='max-width:500px;margin:auto;background:#111;border:1px solid rgba(255,255,255,0.08);border-radius:20px;overflow:hidden;'>
                        <div style='background:linear-gradient(135deg,#1a1a1a,#111111);padding:36px;text-align:center;'>
                          <p style='color:#999;font-size:12px;letter-spacing:4px;margin:0 0 10px;text-transform:uppercase;'>SmartPlant CARE</p>
                          <p style='color:white;font-size:24px;font-weight:700;margin:0;'>Restablecer contraseña</p>
                        </div>
                        <div style='padding:36px;'>
                          <p style='color:#9ca3af;font-size:14px;margin:0 0 28px;'>Hacé clic en el botón para crear una nueva contraseña. El enlace expira en 24 horas.</p>
                          <div style='text-align:center;'>
                            <a href='{$reset_link}' style='display:inline-block;background:#ffffff;color:#000;padding:14px 32px;border-radius:12px;font-weight:700;text-decoration:none;font-size:15px;'>Restablecer contraseña</a>
                          </div>
                          <p style='color:#6b7280;font-size:12px;margin:28px 0 0;text-align:center;'>Si no fuiste vos, ignorá este mensaje.</p>
                        </div>
                      </div>
                    </div>";
                    $mail->send();
                    $msg_forgot = "Te enviamos un enlace a tu correo.";
                } catch (Exception $e) {
                    $error = "No se pudo enviar. Error: {$mail->ErrorInfo}";
                }
            } else {
                // No revelar si el email existe o no (seguridad)
                $msg_forgot = "Si existe una cuenta con ese correo, recibirás un enlace.";
            }
        }

    // ── Login normal ──────────────────────────────────────────
    } else {
        $email    = trim($_POST["email"]    ?? "");
        $password = trim($_POST["password"] ?? "");

        if ($email && $password) {
            $db   = Database::connect();
            $stmt = $db->prepare("SELECT id, nombre, password, plan FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $row  = $stmt->get_result()->fetch_assoc();

            if ($row && password_verify($password, $row['password'])) {
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario']    = $email;
                $_SESSION['nombre']     = $row['nombre'];
                $_SESSION['plan']       = $row['plan'];
                header("Location: /SmartPlant_Care/views/Dashboard.php");
                exit;
            }
            $error = "Correo o contraseña incorrectos.";
        } else {
            $error = "Completá todos los campos.";
        }
    }
}

// ── Generar URL Google OAuth con state anti-CSRF ───────────────
$oauth_state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $oauth_state;

$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $oauth_state,
    'prompt'        => 'select_account',
]);
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
            <a href="Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">Mi cuenta</a>
        </nav>
    </div>
</header>

<!-- ═══ LOGIN SECTION ═══ -->
<main class="flex-1 flex items-center justify-center px-6 py-20">
    <div class="w-full max-w-md relative">

        <div class="orbit-ring" style="width:500px;height:500px;top:50%;left:50%;margin-top:-250px;margin-left:-250px;"></div>
        <div class="orbit-ring" style="width:660px;height:660px;top:50%;left:50%;margin-top:-330px;margin-left:-330px;animation-duration:32s;animation-direction:reverse;border-color:rgba(255,255,255,0.07);"></div>

        <div class="form-glow relative">
            <form method="POST" class="glass-form rounded-[2.5rem] p-12 md:p-14 relative z-10 reveal-scale">

                <div class="text-center mb-10">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-white/10 to-white/5 border border-white/10 flex items-center justify-center text-4xl shadow-lg">🌱</div>
                    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight">
                        Bienvenido de <span class="text-gradient-anim">vuelta.</span>
                    </h2>
                    <p class="text-gray-400 text-sm font-light mt-3">Ingresá para gestionar tu jardín inteligente.</p>
                </div>

                <?php if ($error): ?>
                <div class="error-toast rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
                    <span class="text-red-400">⚠️</span>
                    <p class="text-red-300 text-sm font-medium"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <!-- Email -->
                <div class="mb-5">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Correo</label>
                    <input type="email" name="email" placeholder="tucorreo@smartplant.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light"
                        required autocomplete="email">
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="text-xs font-medium text-gray-400 tracking-widest uppercase mb-2 block ml-1">Contraseña</label>
                    <div class="relative">
                        <input type="password" name="password" id="passwordInput" placeholder="••••••••"
                            class="input-glass w-full rounded-2xl px-5 py-4 text-sm font-light pr-14"
                            required autocomplete="current-password">
                        <button type="button" onclick="togglePassword()"
                            id="toggleBtn" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors text-sm select-none">👁️</button>
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between mb-8">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="recordar" class="w-4 h-4 rounded accent-neutral-400">
                        <span class="text-gray-400 text-xs font-light">Recordarme</span>
                    </label>
                    <a href="#" onclick="event.preventDefault(); openForgotModal();"
                        class="text-white/50 text-xs font-medium hover:text-white transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn-glow w-full bg-white text-black py-4 rounded-2xl font-semibold text-base shadow-lg">
                    Ingresar
                </button>

                <!-- Divider -->
                <div class="my-7 flex items-center gap-4">
                    <div class="divider-glass flex-1"></div>
                    <span class="text-gray-500 text-xs font-light">o continuá con</span>
                    <div class="divider-glass flex-1"></div>
                </div>

                <!-- Botones sociales -->
                <div class="flex flex-col gap-3">

                    <!-- GOOGLE — OAuth 2.0 real -->
                    <a href="<?= htmlspecialchars($google_auth_url) ?>"
                       class="w-full flex items-center justify-center gap-3 input-glass rounded-2xl py-4 text-sm font-medium hover:bg-white/10 transition-all">
                        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Continuar con Google
                    </a>

                    <!-- APPLE — deshabilitado con tooltip explicativo -->
                    <div class="relative group/apple">
                        <button type="button" disabled
                            class="w-full flex items-center justify-center gap-3 input-glass rounded-2xl py-4 text-sm font-medium opacity-35 cursor-not-allowed">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                            Continuar con Apple
                        </button>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 bg-[#1a1a1a] border border-white/10 rounded-2xl px-4 py-3 text-xs text-gray-400 font-light leading-relaxed shadow-xl opacity-0 pointer-events-none group-hover/apple:opacity-100 transition-opacity duration-200 z-50 text-center">
                            <p class="text-white font-semibold mb-1">🍎 Apple Sign In</p>
                            Requiere cuenta de <span class="text-white">Apple Developer</span> ($99/año) y dominio con HTTPS. No disponible en localhost.
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-[#1a1a1a]"></div>
                        </div>
                    </div>
                </div>

                <p class="text-center text-gray-400 text-sm font-light mt-8">
                    ¿No tenés cuenta?
                    <a href="Register.php" class="text-white/70 font-medium hover:underline">Crear cuenta</a>
                </p>

            </form>
        </div>
    </div>
</main>

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
function togglePassword() {
    const i = document.getElementById('passwordInput');
    const b = document.getElementById('toggleBtn');
    i.type  = i.type === 'password' ? 'text' : 'password';
    b.textContent = i.type === 'text' ? '🙈' : '👁️';
}
function openForgotModal() {
    document.getElementById('forgotModal').classList.remove('opacity-0','pointer-events-none');
    document.getElementById('forgotModalContent').classList.replace('scale-95','scale-100');
}
function closeForgotModal() {
    document.getElementById('forgotModalContent').classList.replace('scale-100','scale-95');
    document.getElementById('forgotModal').classList.add('opacity-0','pointer-events-none');
}
<?php if (!empty($msg_forgot)): ?>setTimeout(openForgotModal, 200);<?php endif; ?>
document.getElementById('forgotModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeForgotModal(); });

</script>

<!-- FORGOT PASSWORD MODAL -->
<div id="forgotModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-[#111] border border-white/10 rounded-3xl p-8 max-w-md w-full shadow-2xl transform scale-95 transition-all duration-300" id="forgotModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold tracking-tight">Recuperar contraseña</h3>
            <button onclick="closeForgotModal()" class="text-gray-500 hover:text-white transition-colors text-xl">✕</button>
        </div>
        <?php if ($msg_forgot): ?>
        <div class="text-white text-sm mb-6 bg-white/10 p-4 rounded-xl border border-white/20">✓ <?= htmlspecialchars($msg_forgot) ?></div>
        <?php else: ?>
        <p class="text-gray-400 text-sm mb-6 font-light">Ingresá tu correo y te enviamos un enlace para restablecer tu contraseña.</p>
        <?php endif; ?>
        <form method="POST" class="space-y-5">
            <input type="hidden" name="forgot_password" value="1">
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-widest mb-2">Correo</label>
                <input type="email" name="email_forgot" placeholder="tucorreo@smartplant.com" required class="w-full input-glass rounded-xl px-4 py-3 text-sm">
            </div>
            <button type="submit" class="btn-glow w-full bg-white text-black font-semibold rounded-xl py-3 text-sm">Enviar enlace</button>
        </form>
    </div>
</div>
</body>
</html>