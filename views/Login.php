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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <form method="POST" class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-green-600 mb-6">
            🌱 SmartPlant Login
        </h1>

        <?php if (isset($error)): ?>
            <p class="text-red-500 text-sm mb-4"><?= $error ?></p>
        <?php endif; ?>

        <input
            type="email"
            name="email"
            placeholder="Correo"
            class="w-full border rounded-xl p-3 mb-4"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Contraseña"
            class="w-full border rounded-xl p-3 mb-4"
            required
        >

        <button
            type="submit"
            class="w-full bg-green-600 text-white py-3 rounded-xl hover:bg-green-700"
        >
            Ingresar
        </button>
    </form>

</body>
</html>