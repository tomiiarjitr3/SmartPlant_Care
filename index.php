<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logged = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlant CARE — El futuro en tu jardín</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            padding: 0;
        }

        .bg-overlay {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('/SmartPlant_Care/assets/plant-bg.avif'); /* Asegurate de que el nombre sea exacto */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Estilo Cristal Limpio Apple */
        .glass-clean {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.3);
        }

        /* Tarjetas de utilidades con efecto vidrio */
        .card-glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
        }
        .card-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-5px);
        }
    </style>
</head>

<body class="bg-overlay text-white min-h-screen">

<header class="sticky top-6 z-50 mx-auto max-w-5xl px-4">
        <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
            <h1 class="text-2xl font-semibold tracking-tight text-white flex items-center gap-2">
                <span class="text-green-400">🌱</span> SmartPlant
            </h1>

            <nav class="hidden md:flex gap-10 items-center text-sm font-medium tracking-wide text-white/80">
                <a href="index.php" class="hover:text-white transition-all">Inicio</a>
                <a href="#utilidades" class="hover:text-white transition-all">Utilidades</a>
                <a href="store.php" class="hover:text-white transition-all">Tienda</a>
                <a href="views/Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg">
                    Mi cuenta
                </a>
            </nav>
        </div>
    </header>

    <section class="flex flex-col items-center justify-center text-center pt-40 pb-20 px-6">
        <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase mb-6">Innovación Sustentable</span>
        
        <h2 class="text-6xl md:text-8xl font-semibold tracking-tighter mb-8 leading-[1.1]">
            Inteligente por <br> naturaleza.
        </h2>

        <p class="text-xl md:text-2xl text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
            Tecnología IoT y diseño de vanguardia para que <br class="hidden md:block"> llevar un control total de tus plantas desde cualquier parte del mundo.
        </p>

        <div class="mt-14 flex flex-col sm:flex-row gap-6">
            <a href="store.php" class="px-10 py-4 rounded-full bg-blue-600 text-white text-lg font-medium hover:bg-blue-500 transition shadow-2xl">
                Comprar ahora
            </a>
            <a href="#utilidades" class="px-10 py-4 rounded-full text-blue-400 text-lg font-medium hover:underline transition">
                Más información >
            </a>
        </div>
    </section>

    <section id="utilidades" class="max-w-6xl mx-auto px-6 py-32">
        <div class="grid md:grid-cols-2 gap-6">
            
            <div class="card-glass rounded-[3rem] p-12">
                <h3 class="text-3xl font-semibold mb-4">Monitoreo global.</h3>
                <p class="text-gray-400 text-lg font-light leading-relaxed">
                    Humedad, luz y temperatura bajo control total desde tu dispositivo, estés donde estés.
                </p>
            </div>

            <div class="card-glass rounded-[3rem] p-12">
                <h3 class="text-3xl font-semibold mb-4">Riego autónomo.</h3>
                <p class="text-gray-400 text-lg font-light leading-relaxed">
                    Nuestra IA decide el momento exacto de riego. No te preocupes, lo hacemos por vos.
                </p>
            </div>

            <div class="card-glass rounded-[3rem] p-12">
                <h3 class="text-3xl font-semibold mb-4">Energía Solar.</h3>
                <p class="text-gray-400 text-lg font-light leading-relaxed">
                    Panel integrado de alta eficiencia para un funcionamiento 24/7.
                </p>
            </div>

            <div class="card-glass rounded-[3rem] p-12">
                <h3 class="text-3xl font-semibold mb-4">Dashboard intuitivo y seguro.</h3>
                <p class="text-gray-400 text-lg font-light leading-relaxed">
                    Visualizá la salud de tu jardín con estadísticas precisas y una interfaz impecable.
                </p>
            </div>

        </div>
    </section>

    <footer class="py-20 text-center text-gray-500 text-sm border-t border-white/10 glass-clean rounded-t-[3rem] mt-20">
        <div class="max-w-4xl mx-auto px-6">
            <p class="mb-4">🌱 SmartPlant CARE</p>
            <p class="font-light tracking-widest uppercase text-[10px]">Diseñado para el futuro</p>
            <p class="mt-10 opacity-50">© 2026 Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>