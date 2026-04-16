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

        /* ═══════════════════════════════════════════
           ANIMACIONES SCROLL - ESTILO APPLE
           ═══════════════════════════════════════════ */

        /* Fade in desde abajo */
        .reveal {
            opacity: 0;
            transform: translateY(80px);
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Fade in desde la izquierda */
        .reveal-left {
            opacity: 0;
            transform: translateX(-100px);
            transition: all 1.1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-left.active {
            opacity: 1;
            transform: translateX(0);
        }

        /* Fade in desde la derecha */
        .reveal-right {
            opacity: 0;
            transform: translateX(100px);
            transition: all 1.1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-right.active {
            opacity: 1;
            transform: translateX(0);
        }

        /* Scale up */
        .reveal-scale {
            opacity: 0;
            transform: scale(0.8);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-scale.active {
            opacity: 1;
            transform: scale(1);
        }

        /* Blur in */
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

        /* Stagger children */
        .stagger-children > * {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .stagger-children.active > *:nth-child(1) { transition-delay: 0.05s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(2) { transition-delay: 0.15s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(3) { transition-delay: 0.25s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(4) { transition-delay: 0.35s; opacity: 1; transform: translateY(0); }

        /* ═══════════════════════════════════════════
           PRODUCTO - SHOWCASE
           ═══════════════════════════════════════════ */
        .product-glow {
            position: relative;
        }
        .product-glow::before {
            content: '';
            position: absolute;
            inset: -40px;
            background: radial-gradient(circle, rgba(74, 222, 128, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            animation: pulseGlow 4s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.08); }
        }

        .product-image-container {
            position: relative;
            z-index: 1;
        }

        /* Floating animation para el producto */
        .float-product {
            animation: floatUp 6s ease-in-out infinite;
        }
        @keyframes floatUp {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-15px) rotate(0.5deg); }
            66% { transform: translateY(-8px) rotate(-0.5deg); }
        }

        /* Orbit ring decorativo */
        .orbit-ring {
            position: absolute;
            border: 1px solid rgba(74, 222, 128, 0.15);
            border-radius: 50%;
            animation: orbitSpin 20s linear infinite;
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
            background: rgba(74, 222, 128, 0.6);
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(74, 222, 128, 0.8);
        }

        /* ═══════════════════════════════════════════
           SPECS COUNTER
           ═══════════════════════════════════════════ */
        .spec-number {
            background: linear-gradient(135deg, #4ade80, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
           PARALLAX LINE DECORATIVA
           ═══════════════════════════════════════════ */
        .line-accent {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4ade80, transparent);
            border-radius: 2px;
        }

        /* ═══════════════════════════════════════════
           SCROLL PROGRESS BAR
           ═══════════════════════════════════════════ */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #4ade80, #22d3ee);
            z-index: 9999;
            transition: width 0.1s linear;
        }

        /* ═══════════════════════════════════════════
           MARQUEE TEXT
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
    </style>
</head>

<body class="bg-overlay text-white min-h-screen">

<!-- Scroll Progress Bar -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- ═══════════════════════════════════════════
     HEADER
     ═══════════════════════════════════════════ -->
<header class="sticky top-6 z-50 mx-auto max-w-5xl px-4">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <h1 class="text-2xl font-semibold tracking-tight text-white flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </h1>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium tracking-wide text-white/80">
            <a href="index.php" class="hover:text-white transition-all">Inicio</a>
            <a href="#utilidades" class="hover:text-white transition-all">Utilidades</a>
            <a href="#producto" class="hover:text-white transition-all">Producto</a>
            <a href="store.php" class="hover:text-white transition-all">Tienda</a>
            <a href="views/Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg">
                Mi cuenta
            </a>
        </nav>
    </div>
</header>

<!-- ═══════════════════════════════════════════
     HERO
     ═══════════════════════════════════════════ -->
<section class="flex flex-col items-center justify-center text-center pt-40 pb-20 px-6">
    <span class="reveal-blur text-green-400 font-semibold tracking-[0.2em] text-xs uppercase mb-6">Innovación Sustentable</span>
    
    <h2 class="reveal-blur text-6xl md:text-8xl font-semibold tracking-tighter mb-8 leading-[1.1]">
        Inteligente por <br> <span class="text-gradient-anim">naturaleza.</span>
    </h2>

    <p class="reveal text-xl md:text-2xl text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
        Tecnología IoT y diseño de vanguardia para que <br class="hidden md:block"> llevar un control total de tus plantas desde cualquier parte del mundo.
    </p>

    <div class="reveal mt-14 flex flex-col sm:flex-row gap-6">
        <a href="store.php" class="px-10 py-4 rounded-full bg-blue-600 text-white text-lg font-medium hover:bg-blue-500 transition shadow-2xl hover:shadow-blue-500/25 hover:scale-105">
            Comprar ahora
        </a>
        <a href="#producto" class="px-10 py-4 rounded-full text-blue-400 text-lg font-medium hover:underline transition">
            Más información >
        </a>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     MARQUEE DECORATIVO
     ═══════════════════════════════════════════ -->
<section class="py-10 overflow-hidden border-y border-white/5">
    <div class="marquee-track">
        <span class="text-[5rem] md:text-[8rem] font-bold tracking-tighter text-white/[0.03] whitespace-nowrap px-8">
            SmartPlant CARE — IoT para tus plantas — Riego inteligente — Energía solar — Dashboard en tiempo real — 
        </span>
        <span class="text-[5rem] md:text-[8rem] font-bold tracking-tighter text-white/[0.03] whitespace-nowrap px-8">
            SmartPlant CARE — IoT para tus plantas — Riego inteligente — Energía solar — Dashboard en tiempo real — 
        </span>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     PRODUCTO SHOWCASE
     ═══════════════════════════════════════════ -->
<section id="producto" class="max-w-7xl mx-auto px-6 py-32">
    
    <!-- Título de sección -->
    <div class="text-center mb-24">
        <div class="reveal-blur">
            <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Nuestro producto</span>
            <h2 class="text-5xl md:text-7xl font-semibold tracking-tighter mt-4">
                Conocé <span class="text-gradient-anim">SmartPlant.</span>
            </h2>
        </div>
    </div>

    <!-- Producto + Info - Layout principal -->
    <div class="grid md:grid-cols-2 gap-16 items-center">

        <!-- IMAGEN DEL PRODUCTO -->
        <div class="reveal-left flex justify-center">
            <div class="product-glow relative">
                
                <!-- Orbit rings decorativos -->
                <div class="orbit-ring" style="width: 350px; height: 350px; top: 50%; left: 50%; margin-top: -175px; margin-left: -175px;"></div>
                <div class="orbit-ring" style="width: 480px; height: 480px; top: 50%; left: 50%; margin-top: -240px; margin-left: -240px; animation-duration: 30s; animation-direction: reverse; border-color: rgba(34, 211, 238, 0.1);"></div>

                <div class="product-image-container float-product">
                    <!-- ══════════════════════════════════════════════
                         📸 ACÁ VA TU FOTO DE PRODUCTO
                         Reemplazá el placeholder con tu imagen real
                         Ejemplo: <img src="/SmartPlant_Care/assets/producto.png" ...>
                         ══════════════════════════════════════════════ -->
                    <div class="w-[320px] h-[320px] md:w-[400px] md:h-[400px] rounded-[3rem] bg-gradient-to-br from-green-900/40 to-emerald-950/40 border border-white/10 flex flex-col items-center justify-center backdrop-blur-sm overflow-hidden group cursor-pointer relative">
                        
                        <!-- Placeholder visual -->
                        <img src="assets/regador.jpg" alt="SmartPlant CARE" class="w-24 opacity-30">

                        <!-- Efecto brillo hover -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    </div>
                    <!-- ══════════════════════════════════════════════
                         Cuando tengas la foto, reemplazá todo el div de arriba por:
                         
                         <img 
                             src="/SmartPlant_Care/assets/tu-producto.png" 
                             alt="SmartPlant CARE" 
                             class="w-[320px] md:w-[400px] rounded-[3rem] shadow-2xl shadow-green-900/30 border border-white/10 hover:scale-105 transition-transform duration-700"
                         >
                         ══════════════════════════════════════════════ -->
                </div>
            </div>
        </div>

        <!-- INFO DEL PRODUCTO -->
        <div class="reveal-right space-y-8">
            <div>
                <div class="line-accent mb-6"></div>
                <h3 class="text-4xl md:text-5xl font-semibold tracking-tight leading-tight">
                    Diseñado para <br>vivir al aire libre.
                </h3>
            </div>

            <p class="text-gray-400 text-lg font-light leading-relaxed max-w-md">
                Un dispositivo compacto, resistente al agua y alimentado por energía solar. Monitorea tus plantas 24/7 y riega automáticamente cuando lo necesitan.
            </p>

            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 text-lg">✓</div>
                    <span class="text-gray-300">Resistente al agua IP67</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 text-lg">✓</div>
                    <span class="text-gray-300">Batería de larga duración + Solar</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 text-lg">✓</div>
                    <span class="text-gray-300">Conectividad WiFi + Bluetooth</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 text-lg">✓</div>
                    <span class="text-gray-300">Setup en menos de 5 minutos</span>
                </div>
            </div>

            <a href="store.php" class="inline-block mt-4 px-8 py-4 rounded-full bg-green-500 text-black font-semibold text-lg hover:bg-green-400 hover:scale-105 transition-all duration-300 shadow-lg shadow-green-500/20">
                Comprar SmartPlant →
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     SPECS / NUMBERS
     ═══════════════════════════════════════════ -->
<section class="py-32 border-y border-white/5">
    <div class="max-w-6xl mx-auto px-6">
        <div class="reveal text-center mb-20">
            <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Especificaciones</span>
            <h2 class="text-4xl md:text-6xl font-semibold tracking-tighter mt-4">Los números hablan.</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 stagger-children" id="specsGrid">
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="24">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Horas de autonomía</p>
            </div>
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="5">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Sensores integrados</p>
            </div>
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="100">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Metros de alcance WiFi</p>
            </div>
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="3">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Años de garantía</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     PRODUCTO DETALLE - SEGUNDA VISTA (full width)
     ═══════════════════════════════════════════ -->
<section class="py-32">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal-scale">
            <!-- ══════════════════════════════════════════════
                 📸 ACÁ VA UNA SEGUNDA FOTO (tipo lifestyle/ambiente)
                 ══════════════════════════════════════════════ -->
            <div class="w-full h-[400px] md:h-[600px] rounded-[3rem] bg-gradient-to-br from-green-900/20 to-cyan-900/20 border border-white/10 flex flex-col items-center justify-center backdrop-blur-sm relative overflow-hidden group">
                <div class="text-9xl group-hover:scale-110 transition-transform duration-1000">🪴</div>
                <p class="text-white/30 text-lg mt-6 font-light">Foto lifestyle del producto en su entorno</p>
                
                <!-- Efecto parallax interno -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute bottom-12 left-12 z-10">
                    <p class="text-white/60 text-sm font-medium tracking-widest uppercase">SmartPlant CARE</p>
                    <p class="text-4xl md:text-6xl font-semibold tracking-tighter mt-2">Belleza funcional.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     UTILIDADES (ORIGINAL MEJORADO)
     ═══════════════════════════════════════════ -->
<section id="utilidades" class="max-w-6xl mx-auto px-6 py-32">
    <div class="reveal text-center mb-20">
        <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Funcionalidades</span>
        <h2 class="text-4xl md:text-6xl font-semibold tracking-tighter mt-4">Todo lo que necesitás.</h2>
    </div>

    <div class="grid md:grid-cols-2 gap-6 stagger-children" id="featuresGrid">
        
        <div class="card-glass rounded-[3rem] p-12">
            <span class="text-4xl mb-6 block">📡</span>
            <h3 class="text-3xl font-semibold mb-4">Monitoreo global.</h3>
            <p class="text-gray-400 text-lg font-light leading-relaxed">
                Humedad, luz y temperatura bajo control total desde tu dispositivo, estés donde estés.
            </p>
        </div>

        <div class="card-glass rounded-[3rem] p-12">
            <span class="text-4xl mb-6 block">💧</span>
            <h3 class="text-3xl font-semibold mb-4">Riego autónomo.</h3>
            <p class="text-gray-400 text-lg font-light leading-relaxed">
                Nuestra IA decide el momento exacto de riego. No te preocupes, lo hacemos por vos.
            </p>
        </div>

        <div class="card-glass rounded-[3rem] p-12">
            <span class="text-4xl mb-6 block">☀️</span>
            <h3 class="text-3xl font-semibold mb-4">Energía Solar.</h3>
            <p class="text-gray-400 text-lg font-light leading-relaxed">
                Panel integrado de alta eficiencia para un funcionamiento 24/7.
            </p>
        </div>

        <div class="card-glass rounded-[3rem] p-12">
            <span class="text-4xl mb-6 block">📊</span>
            <h3 class="text-3xl font-semibold mb-4">Dashboard intuitivo.</h3>
            <p class="text-gray-400 text-lg font-light leading-relaxed">
                Visualizá la salud de tu jardín con estadísticas precisas y una interfaz impecable.
            </p>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════
     CTA FINAL
     ═══════════════════════════════════════════ -->
<section class="py-32">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="reveal-blur">
            <h2 class="text-5xl md:text-7xl font-semibold tracking-tighter mb-6">
                Ready to <span class="text-gradient-anim">grow?</span>
            </h2>
            <p class="text-gray-400 text-xl font-light mb-12 max-w-xl mx-auto">
                Unite a la revolución verde. Tu jardín merece lo mejor de la tecnología.
            </p>
            <a href="store.php" class="inline-block px-14 py-5 rounded-full bg-white text-black text-lg font-semibold hover:scale-105 transition-all duration-300 shadow-2xl shadow-white/10">
                Empezar ahora
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     FOOTER
     ═══════════════════════════════════════════ -->
<footer class="py-20 text-center text-gray-500 text-sm border-t border-white/10 glass-clean rounded-t-[3rem] mt-20">
    <div class="max-w-4xl mx-auto px-6">
        <p class="mb-4">🌱 SmartPlant CARE</p>
        <p class="font-light tracking-widest uppercase text-[10px]">Diseñado para el futuro</p>
        <p class="mt-10 opacity-50">© 2026 Todos los derechos reservados.</p>
    </div>
</footer>

<!-- ═══════════════════════════════════════════
     JAVASCRIPT - ANIMACIONES
     ═══════════════════════════════════════════ -->
<script>
    // ─── Scroll Progress Bar ───
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        document.getElementById('scrollProgress').style.width = scrollPercent + '%';
    });

    // ─── Intersection Observer para animaciones reveal ───
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');

                // Counter animation para specs
                if (entry.target.id === 'specsGrid') {
                    animateCounters();
                }
            }
        });
    }, observerOptions);

    // Observar todos los elementos con clase reveal
    document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-blur, .stagger-children').forEach(el => {
        observer.observe(el);
    });

    // ─── Counter Animation ───
    let countersAnimated = false;
    function animateCounters() {
        if (countersAnimated) return;
        countersAnimated = true;

        document.querySelectorAll('[data-target]').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000;
            const start = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                
                // Ease out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);
                
                counter.textContent = current;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    // Agregar sufijo si es necesario
                    counter.textContent = target;
                    if (target === 100) counter.textContent = target + 'm';
                }
            }

            requestAnimationFrame(update);
        });
    }

    // ─── Parallax suave en el hero ───
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        const hero = document.querySelector('section:first-of-type');
        if (hero && scrolled < window.innerHeight) {
            hero.style.transform = `translateY(${scrolled * 0.3}px)`;
            hero.style.opacity = 1 - (scrolled / (window.innerHeight * 0.8));
        }
    });
</script>

</body>
</html>