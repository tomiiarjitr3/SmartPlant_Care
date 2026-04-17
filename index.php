<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$logged = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlant CARE — El futuro en tu jardín</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
</head>

<body class="bg-overlay text-white min-h-screen">

<!-- Scroll Progress Bar -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto max-w-5xl px-4">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <h1 class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </h1>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium text-white/80">
            <a href="index.php"      class="hover:text-white transition-colors">Inicio</a>
            <a href="#utilidades"    class="hover:text-white transition-colors">Utilidades</a>
            <a href="#producto"      class="hover:text-white transition-colors">Producto</a>
            <a href="views/Store.php"      class="hover:text-white transition-colors">Tienda</a>
            <?php if ($logged): ?>
                <a href="views/Dashboard.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">
                    Mi Dashboard
                </a>
            <?php else: ?>
                <a href="views/Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">
                    Mi cuenta
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- ═══ HERO ═══ -->
<section class="flex flex-col items-center justify-center text-center pt-40 pb-20 px-6">
    <span class="reveal-blur text-green-400 font-semibold tracking-[0.2em] text-xs uppercase mb-6">Innovación Sustentable</span>

    <h2 class="reveal-blur text-6xl md:text-8xl font-semibold tracking-tight mb-8 leading-[1.1]">
        Inteligente por <br><span class="text-gradient-anim">naturaleza.</span>
    </h2>

    <p class="reveal text-xl md:text-2xl text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
        Tecnología IoT y diseño de vanguardia para llevar un control total de tus plantas desde cualquier parte del mundo.
    </p>

    <div class="reveal mt-14 flex flex-col sm:flex-row gap-6">
        <a href="views/Store.php" class="px-10 py-4 rounded-full bg-blue-600 text-white text-lg font-medium hover:bg-blue-500 transition-all shadow-2xl hover:shadow-blue-500/25 hover:scale-105">
            Comprar ahora
        </a>
        <a href="#producto" class="px-10 py-4 rounded-full text-blue-400 text-lg font-medium hover:underline transition">
            Más información →
        </a>
    </div>
</section>

<!-- ═══ MARQUEE ═══ -->
<section class="py-10 overflow-hidden border-y border-white/5">
    <div class="marquee-track">
        <span class="text-[5rem] md:text-[8rem] font-bold tracking-tighter text-white/[0.03] whitespace-nowrap px-8">
            SmartPlant CARE — IoT para tus plantas — Riego inteligente — Energía solar — Dashboard en tiempo real — &nbsp;
        </span>
        <span class="text-[5rem] md:text-[8rem] font-bold tracking-tighter text-white/[0.03] whitespace-nowrap px-8">
            SmartPlant CARE — IoT para tus plantas — Riego inteligente — Energía solar — Dashboard en tiempo real — &nbsp;
        </span>
    </div>
</section>

<!-- ═══ PRODUCTO SHOWCASE ═══ -->
<section id="producto" class="max-w-7xl mx-auto px-6 py-32">
    <div class="text-center mb-24 reveal-blur">
        <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Nuestro producto</span>
        <h2 class="text-5xl md:text-7xl font-semibold tracking-tight mt-4">
            Conocé <span class="text-gradient-anim">SmartPlant.</span>
        </h2>
    </div>

    <div class="grid md:grid-cols-2 gap-16 items-center">

        <!-- Imagen del producto -->
        <div class="reveal-left flex justify-center">
            <div class="product-glow relative">
                <div class="orbit-ring" style="width:350px;height:350px;top:50%;left:50%;margin-top:-175px;margin-left:-175px;"></div>
                <div class="orbit-ring" style="width:480px;height:480px;top:50%;left:50%;margin-top:-240px;margin-left:-240px;animation-duration:32s;animation-direction:reverse;border-color:rgba(34,211,238,0.09);"></div>

                <div class="product-image-container float-product">
                    <!-- Reemplazá este div por: <img src="/SmartPlant_Care/assets/tu-producto.png" class="w-[380px] rounded-[3rem] shadow-2xl border border-white/10"> -->
                    <div class="w-[300px] h-[300px] md:w-[380px] md:h-[380px] rounded-[3rem] border border-white/10 overflow-hidden group cursor-pointer relative">
                        <img src="assets/product-lifestyle.png" alt="SmartPlant CARE en maceta" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info -->
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
                <?php foreach ([
                    'Resistente al agua IP67',
                    'Batería de larga duración + Solar',
                    'Conectividad WiFi + Bluetooth',
                    'Setup en menos de 5 minutos',
                ] as $feat): ?>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400">✓</div>
                    <span class="text-gray-300"><?= $feat ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="views/Store.php" class="inline-block mt-4 px-8 py-4 rounded-full bg-green-500 text-black font-semibold text-lg hover:bg-green-400 hover:scale-105 transition-all shadow-lg shadow-green-500/20">
                Comprar SmartPlant →
            </a>
        </div>
    </div>
</section>

<!-- ═══ SPECS ═══ -->
<section class="py-32 border-y border-white/5">
    <div class="max-w-6xl mx-auto px-6">
        <div class="reveal text-center mb-20">
            <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Especificaciones</span>
            <h2 class="text-4xl md:text-6xl font-semibold tracking-tight mt-4">Los números hablan.</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 stagger-children" id="specsGrid">
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="120">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Horas de autonomía</p>
            </div>
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="5">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Sensores integrados</p>
            </div>
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="100" data-suffix="m">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Alcance WiFi</p>
            </div>
            <div class="text-center p-6">
                <p class="spec-number text-5xl md:text-7xl font-bold" data-target="3">0</p>
                <p class="text-gray-400 text-sm mt-3 font-light">Años de garantía</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FOTO LIFESTYLE ═══ -->
<section class="py-32">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal-scale">
            <div class="w-full h-[400px] md:h-[560px] rounded-[3rem] bg-gradient-to-br from-green-900/20 to-cyan-900/20 border border-white/10 flex flex-col items-center justify-center backdrop-blur-sm relative overflow-hidden group">
                <div class="text-9xl group-hover:scale-110 transition-transform duration-1000">🪴</div>
                <p class="text-white/25 text-lg mt-6 font-light">Foto lifestyle del producto en su entorno</p>
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute bottom-12 left-12 z-10">
                    <p class="text-white/50 text-sm font-medium tracking-widest uppercase">SmartPlant CARE</p>
                    <p class="text-4xl md:text-6xl font-semibold tracking-tight mt-2">Belleza funcional.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ UTILIDADES ═══ -->
<section id="utilidades" class="max-w-6xl mx-auto px-6 py-32">
    <div class="reveal text-center mb-20">
        <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Funcionalidades</span>
        <h2 class="text-4xl md:text-6xl font-semibold tracking-tight mt-4">Todo lo que necesitás.</h2>
    </div>
    <div class="grid md:grid-cols-2 gap-6 stagger-children">
        <?php foreach ([
            ['📡', 'Monitoreo global.',   'Humedad, luz y temperatura bajo control total desde tu dispositivo, estés donde estés.'],
            ['💧', 'Riego autónomo.',     'El sistema decide el momento exacto de riego según los datos del sensor. Sin intervención.'],
            ['☀️', 'Energía Solar.',      'Panel integrado de alta eficiencia para un funcionamiento continuo 24/7.'],
            ['📊', 'Dashboard intuitivo.','Visualizá la salud de tu jardín con estadísticas precisas y una interfaz impecable.'],
        ] as [$icon, $title, $desc]): ?>
        <div class="card-glass rounded-[3rem] p-12">
            <span class="text-4xl mb-6 block"><?= $icon ?></span>
            <h3 class="text-3xl font-semibold mb-4"><?= $title ?></h3>
            <p class="text-gray-400 text-lg font-light leading-relaxed"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ═══ CTA FINAL ═══ -->
<section class="py-32">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="reveal-blur">
            <h2 class="text-5xl md:text-7xl font-semibold tracking-tight mb-6">
                Ready to <span class="text-gradient-anim">grow?</span>
            </h2>
            <p class="text-gray-400 text-xl font-light mb-12 max-w-xl mx-auto">
                Unite a la revolución verde. Tu jardín merece lo mejor de la tecnología.
            </p>
            <a href="views/Store.php" class="inline-block px-14 py-5 rounded-full bg-white text-black text-lg font-semibold hover:scale-105 transition-all shadow-2xl shadow-white/10">
                Empezar ahora
            </a>
        </div>
    </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer class="py-20 text-center text-gray-500 text-sm border-t border-white/10 glass-clean rounded-t-[3rem] mt-20">
    <div class="max-w-4xl mx-auto px-6">
        <p class="mb-4">🌱 SmartPlant CARE</p>
        <p class="font-light tracking-widest uppercase text-[10px]">Diseñado para el futuro</p>
        <p class="mt-10 opacity-50">© 2026 Todos los derechos reservados.</p>
    </div>
</footer>

<script>
    // Scroll progress
    window.addEventListener('scroll', () => {
        const pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        document.getElementById('scrollProgress').style.width = pct + '%';
    });

    // Reveal observer
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('active');
                if (e.target.id === 'specsGrid') animateCounters();
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-blur, .stagger-children').forEach(el => obs.observe(el));

    // Counters
    let counted = false;
    function animateCounters() {
        if (counted) return; counted = true;
        document.querySelectorAll('[data-target]').forEach(el => {
            const target = parseInt(el.getAttribute('data-target'));
            const suffix = el.getAttribute('data-suffix') || '';
            const dur    = 2000;
            const start  = performance.now();
            function step(now) {
                const p = Math.min((now - start) / dur, 1);
                const e = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(e * target) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    // Parallax hero
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        const hero     = document.querySelector('section:first-of-type');
        if (hero && scrolled < window.innerHeight) {
            hero.style.transform = `translateY(${scrolled * 0.28}px)`;
            hero.style.opacity   = 1 - (scrolled / (window.innerHeight * 0.85));
        }
    });
</script>
</body>
</html>