<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: /SmartPlant_Care/views/Login.php");
    exit;
}

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: /SmartPlant_Care/views/Login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-green-600 text-white p-6 shadow-lg flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">🌱 SmartPlant CARE</h1>
            <p class="text-sm opacity-90">
                Bienvenido, <?= $_SESSION["usuario"] ?>
            </p>
        </div>

        <a
            href="?logout=1"
            class="bg-white text-green-600 px-4 py-2 rounded-xl font-semibold"
        >
            Cerrar sesión
        </a>
    </header>

    <main class="max-w-6xl mx-auto p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-sm text-gray-500">Humedad del suelo</h2>
                <p class="text-4xl font-bold text-green-600 mt-2">42%</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-sm text-gray-500">Temperatura</h2>
                <p class="text-4xl font-bold mt-2">26°C</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-sm text-gray-500">Luz</h2>
                <p class="text-4xl font-bold mt-2">780 lx</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-sm text-gray-500">Tanque</h2>
                <p class="text-4xl font-bold text-blue-600 mt-2">80%</p>
            </div>
        </div>
    </main>
</body>
</html>