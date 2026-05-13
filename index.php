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



<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto max-w-5xl px-4">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <h1 class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-white/60">🌱</span> SmartPlant
        </h1>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium text-white/80">
            <a href="index.php"      class="hover:text-white transition-colors">Inicio</a>
            <a href="#utilidades"    class="hover:text-white transition-colors">Utilidades</a>
            <a href="#asistente-ia"  class="hover:text-white transition-colors">IA</a>
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
    <span class="reveal-blur text-white/50 font-semibold tracking-[0.2em] text-xs uppercase mb-6">Innovación Sustentable</span>

    <h2 class="reveal-blur text-6xl md:text-8xl font-semibold tracking-tight mb-8 leading-[1.1]">
        Inteligente por <br><span class="text-gradient-anim">naturaleza.</span>
    </h2>

    <p class="reveal text-xl md:text-2xl text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
        Tecnología IoT y diseño de vanguardia para llevar un control total de tus plantas desde cualquier parte del mundo.
    </p>

    <div class="reveal mt-14 flex flex-col sm:flex-row gap-6">
        <a href="views/Store.php" class="px-10 py-4 rounded-full bg-white text-black text-lg font-medium hover:opacity-90 transition-all shadow-2xl hover:scale-105">
            Comprar ahora
        </a>
        <a href="#producto" class="px-10 py-4 rounded-full text-white/60 text-lg font-medium hover:underline transition">
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

<!-- ═══ AUREA ONE — TEARDOWN SHOWCASE ═══ -->
<section id="teardown" class="td-section">
    <div class="td-sticky">
        <!-- Ambient -->
        <div class="td-ambient"></div>

        <!-- FULL DEVICE (initial state) -->
        <div class="td-hero" id="tdHero">
            <img src="/SmartPlant_Care/assets/product-device-black.png" alt="Aurea One" class="td-hero-img" draggable="false">
        </div>

        <!-- Title overlay -->
        <div class="td-title-wrap" id="tdTitle">
            <span class="td-overline">Aurea One</span>
            <h2 class="td-heading">Por dentro,<br><span class="td-heading-accent">extraordinario.</span></h2>
            <p class="td-subtitle">Cada componente diseñado con un propósito.</p>
        </div>

        <!-- SPOTLIGHT CARDS (one per component, stacked, cross-faded by JS) -->
        <div class="td-spotlights" id="tdSpotlights">
            <!-- 1. Panel Solar -->
            <div class="td-spot" data-spot="0">
                <div class="td-spot-img-wrap">
                    <img src="/SmartPlant_Care/assets/part-solar.png" alt="Panel Solar" class="td-spot-img" draggable="false">
                </div>
                <div class="td-spot-info">
                    <span class="td-spot-num">01</span>
                    <h3 class="td-spot-title">Panel Solar</h3>
                    <p class="td-spot-desc">Micro panel fotovoltaico de alta eficiencia integrado en la tapa superior. Carga la batería de forma autónoma bajo luz solar directa o difusa, eliminando la necesidad de cables.</p>
                    <div class="td-spot-specs">
                        <div class="td-spec"><span>Tipo</span><strong>Monocristalino</strong></div>
                        <div class="td-spec"><span>Carga</span><strong>Autónoma 24/7</strong></div>
                    </div>
                </div>
            </div>

            <!-- 2. Carcasa -->
            <div class="td-spot" data-spot="1">
                <div class="td-spot-img-wrap">
                    <img src="/SmartPlant_Care/assets/exploded-body.png" alt="Carcasa IP67" class="td-spot-img td-blend" draggable="false">
                </div>
                <div class="td-spot-info">
                    <span class="td-spot-num">02</span>
                    <h3 class="td-spot-title">Carcasa IP67</h3>
                    <p class="td-spot-desc">Cilindro de aluminio anodizado resistente al agua y al polvo. Diseñado para vivir permanentemente al aire libre, soportando lluvia, sol y temperaturas extremas.</p>
                    <div class="td-spot-specs">
                        <div class="td-spec"><span>Material</span><strong>Aluminio anodizado</strong></div>
                        <div class="td-spec"><span>Protección</span><strong>IP67</strong></div>
                    </div>
                </div>
            </div>

            <!-- 3. ESP32 + PCB -->
            <div class="td-spot" data-spot="2">
                <div class="td-spot-img-wrap">
                    <img src="/SmartPlant_Care/assets/exploded-pcb.png" alt="ESP32 PCB" class="td-spot-img td-blend" draggable="false">
                </div>
                <div class="td-spot-info">
                    <span class="td-spot-num">03</span>
                    <h3 class="td-spot-title">ESP32 + Sensores</h3>
                    <p class="td-spot-desc">El cerebro del dispositivo. Un microcontrolador ESP32 dual-core con WiFi y Bluetooth integrado, conectado a 5 sensores que monitorean humedad, temperatura, luz, pH y nutrientes del suelo.</p>
                    <div class="td-spot-specs">
                        <div class="td-spec"><span>Chip</span><strong>ESP32 Dual-Core</strong></div>
                        <div class="td-spec"><span>Conectividad</span><strong>WiFi + BT 5.0</strong></div>
                        <div class="td-spec"><span>Sensores</span><strong>5 integrados</strong></div>
                    </div>
                </div>
            </div>

            <!-- 4. Batería -->
            <div class="td-spot" data-spot="3">
                <div class="td-spot-img-wrap">
                    <img src="/SmartPlant_Care/assets/exploded-battery.png" alt="Batería Li-Ion" class="td-spot-img td-blend" draggable="false">
                </div>
                <div class="td-spot-info">
                    <span class="td-spot-num">04</span>
                    <h3 class="td-spot-title">Batería Li-Ion</h3>
                    <p class="td-spot-desc">Batería recargable de litio de alta capacidad que proporciona hasta 120 horas de operación continua. Se recarga automáticamente a través del panel solar integrado.</p>
                    <div class="td-spot-specs">
                        <div class="td-spec"><span>Autonomía</span><strong>120 horas</strong></div>
                        <div class="td-spec"><span>Recarga</span><strong>Solar automática</strong></div>
                    </div>
                </div>
            </div>

            <!-- 5. Probes -->
            <div class="td-spot" data-spot="4">
                <div class="td-spot-img-wrap">
                    <img src="/SmartPlant_Care/assets/exploded-base.png" alt="Sensores de tierra" class="td-spot-img td-blend" draggable="false">
                </div>
                <div class="td-spot-info">
                    <span class="td-spot-num">05</span>
                    <h3 class="td-spot-title">Probes de Tierra</h3>
                    <p class="td-spot-desc">Dos puntas de acero inoxidable se insertan en el sustrato para medir en tiempo real la humedad, conductividad eléctrica y temperatura de la tierra con precisión profesional.</p>
                    <div class="td-spot-specs">
                        <div class="td-spec"><span>Material</span><strong>Acero inoxidable</strong></div>
                        <div class="td-spec"><span>Medición</span><strong>Tiempo real</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll cue -->
        <div class="td-scroll-cue" id="tdScrollCue">
            <div class="td-mouse"><div class="td-wheel"></div></div>
            <span>Scrolleá para explorar</span>
        </div>

        <!-- Step dots -->
        <div class="td-dots" id="tdDots">
            <div class="td-dot active"></div>
            <div class="td-dot"></div>
            <div class="td-dot"></div>
            <div class="td-dot"></div>
            <div class="td-dot"></div>
        </div>
    </div>
</section>

<!-- ═══ PRODUCTO SHOWCASE ═══ -->
<section id="producto" class="max-w-7xl mx-auto px-6 py-32">
    <div class="text-center mb-24 reveal-blur">
        <span class="text-white/50 font-semibold tracking-[0.2em] text-xs uppercase">Nuestro producto</span>
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
                    <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/70">✓</div>
                    <span class="text-gray-300"><?= $feat ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="views/Store.php" class="inline-block mt-4 px-8 py-4 rounded-full bg-white text-black font-semibold text-lg hover:opacity-90 hover:scale-105 transition-all shadow-lg">
                Comprar SmartPlant →
            </a>
        </div>
    </div>
</section>

<!-- ═══ SPECS ═══ -->
<section class="py-32 border-y border-white/5">
    <div class="max-w-6xl mx-auto px-6">
        <div class="reveal text-center mb-20">
            <span class="text-white/50 font-semibold tracking-[0.2em] text-xs uppercase">Especificaciones</span>
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
            <div class="w-full h-[400px] md:h-[560px] rounded-[3rem] bg-gradient-to-br from-white/5 to-white/[0.02] border border-white/10 flex flex-col items-center justify-center backdrop-blur-sm relative overflow-hidden group">
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
        <span class="text-white/50 font-semibold tracking-[0.2em] text-xs uppercase">Funcionalidades</span>
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

<!-- ═══ ASISTENTE IA ═══ -->
<section id="asistente-ia" class="py-32 border-y border-white/5 relative overflow-hidden">
    <!-- Fondo decorativo -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%); animation: pulseGlow 6s ease-in-out infinite;"></div>
        <div class="absolute top-1/4 right-1/4 w-[300px] h-[300px] rounded-full" style="background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 70%); animation: pulseGlow 8s ease-in-out infinite reverse;"></div>
    </div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">
        <div class="text-center mb-20 reveal-blur">
            <span class="text-white/50 font-semibold tracking-[0.2em] text-xs uppercase">Inteligencia artificial</span>
            <h2 class="text-4xl md:text-6xl font-semibold tracking-tight mt-4">Tu experta en <span class="text-gradient-anim">plantas.</span></h2>
            <p class="text-gray-400 text-lg font-light mt-6 max-w-2xl mx-auto leading-relaxed">
                Integrada directamente en tu dashboard, nuestra IA analiza los datos de tus sensores en tiempo real y te da recomendaciones personalizadas.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-16 items-center">
            <!-- Visual IA -->
            <div class="reveal-left flex justify-center">
                <div class="ai-showcase-visual">
                    <div class="ai-orb">
                        <div class="ai-orb-ring ai-orb-ring-1"></div>
                        <div class="ai-orb-ring ai-orb-ring-2"></div>
                        <div class="ai-orb-ring ai-orb-ring-3"></div>
                        <div class="ai-orb-core">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" fill="url(#aiGrad1)" opacity="0.15"/>
                                <path d="M9 8.5a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0v-2zM13 8.5a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0v-2z" fill="url(#aiGrad1)"/>
                                <path d="M12 17c-2.21 0-4-1.34-4-3h1.5c0 .83 1.12 1.5 2.5 1.5s2.5-.67 2.5-1.5H16c0 1.66-1.79 3-4 3z" fill="url(#aiGrad1)"/>
                                <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="url(#aiGrad1)" stroke-width="1.5" fill="none"/>
                                <defs>
                                    <linearGradient id="aiGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#ffffff"/>
                                        <stop offset="50%" stop-color="#a0a0a0"/>
                                        <stop offset="100%" stop-color="#666666"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <!-- Floating data bubbles -->
                    <div class="ai-data-bubble ai-data-bubble-1">
                        <span class="text-white/70 text-xs font-bold">💧 52%</span>
                        <span class="text-[9px] text-gray-500">Humedad</span>
                    </div>
                    <div class="ai-data-bubble ai-data-bubble-2">
                        <span class="text-white/70 text-xs font-bold">🌡️ 24°C</span>
                        <span class="text-[9px] text-gray-500">Temp</span>
                    </div>
                    <div class="ai-data-bubble ai-data-bubble-3">
                        <span class="text-white/70 text-xs font-bold">✓ Óptimo</span>
                        <span class="text-[9px] text-gray-500">Estado</span>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="reveal-right space-y-8">
                <div>
                    <div class="line-accent mb-6"></div>
                    <h3 class="text-3xl md:text-4xl font-semibold tracking-tight leading-tight">
                        IA que entiende <br>tus plantas.
                    </h3>
                </div>
                <p class="text-gray-400 text-lg font-light leading-relaxed max-w-md">
                    Potenciada por Google Gemini, nuestra IA analiza los datos de tus sensores, responde preguntas sobre el cuidado de tus plantas, e incluso puede identificar especies a partir de una foto.
                </p>
                <div class="space-y-4">
                    <?php foreach ([
                        ['🧠', 'Diagnósticos en tiempo real', 'Analiza humedad, temperatura y luz para sugerirte acciones.'],
                        ['📷', 'Identificación por foto', 'Subí una foto y la IA identifica tu planta y sus cuidados.'],
                        ['💬', 'Chat conversacional', 'Preguntale como a un experto: riego, poda, plagas y más.'],
                        ['📊', 'Recomendaciones adaptadas', 'Aprende de tus datos históricos para optimizar tus rutinas.'],
                    ] as [$icon, $title, $desc]): ?>
                    <div class="flex items-start gap-4 group">
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform"><?= $icon ?></div>
                        <div>
                            <p class="text-white font-medium text-sm"><?= $title ?></p>
                            <p class="text-gray-500 text-xs font-light leading-relaxed mt-0.5"><?= $desc ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($logged): ?>
                <a href="views/Dashboard.php" class="inline-block mt-4 px-8 py-4 rounded-full bg-white text-black font-semibold text-lg hover:opacity-90 hover:scale-105 transition-all shadow-lg">
                    Probar la IA →
                </a>
                <?php else: ?>
                <a href="views/Login.php" class="inline-block mt-4 px-8 py-4 rounded-full bg-white text-black font-semibold text-lg hover:opacity-90 hover:scale-105 transition-all shadow-lg">
                    Acceder al Dashboard →
                </a>
                <?php endif; ?>
            </div>
        </div>
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
    // Scroll progress removed

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

    // ═══════════════════════════════════════════════════
    // EXPLODED VIEW — 3D Scroll-driven animation
    // ═══════════════════════════════════════════════════
    (function() {
        const section   = document.getElementById('exploded-view');
        if (!section) return;

        const device    = document.getElementById('evDevice');
        const header    = document.getElementById('evHeader');
        const scrollCue = document.getElementById('evScrollCue');
        const stepsEl   = document.getElementById('evSteps');
        const steps     = stepsEl ? stepsEl.querySelectorAll('.ev-step') : [];

        const solar     = section.querySelector('[data-layer="solar"]');
        const body      = section.querySelector('[data-layer="body"]');
        const pcb       = section.querySelector('[data-layer="pcb"]');
        const battery   = section.querySelector('[data-layer="battery"]');
        const base      = section.querySelector('[data-layer="base"]');

        const layers    = section.querySelectorAll('.ev-layer');

        // Utilities
        function ease(t) { return t < 0.5 ? 4*t*t*t : 1 - Math.pow(-2*t+2,3)/2; }
        function map(v, a, b) { return Math.max(0, Math.min(1, (v - a) / (b - a))); }
        function lerp(a, b, t) { return a + (b - a) * t; }

        let ticking = false;

        function update() {
            const rect     = section.getBoundingClientRect();
            const total    = section.offsetHeight - window.innerHeight;
            const raw      = -rect.top / total;
            const progress = Math.max(0, Math.min(1, raw));

            // ─── 3D Device rotation (subtle tilt as you scroll) ───
            const rotX = lerp(8, -5, ease(map(progress, 0, 0.5)));
            const rotY = lerp(-5, 12, ease(map(progress, 0, 0.7)));
            const rotZ = lerp(0, -2, ease(map(progress, 0.2, 0.6)));
            device.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) rotateZ(${rotZ}deg)`;

            // ─── Header: fade out as explosion begins ───
            const headerOpacity = 1 - ease(map(progress, 0.05, 0.25));
            const headerY = -ease(map(progress, 0.05, 0.3)) * 40;
            header.style.opacity = headerOpacity;
            header.style.transform = `translateX(-50%) translateY(${headerY}px)`;

            // ─── Scroll cue: hide immediately ───
            if (scrollCue) {
                scrollCue.style.opacity = progress < 0.03 ? 1 : 0;
            }

            // ─── PHASE ANIMATION ───
            // Phase 1 (0.00–0.15): assembled, slight glow
            // Phase 2 (0.15–0.35): solar cap lifts up + slight Z
            // Phase 3 (0.30–0.50): body shifts up, PCB appears from inside
            // Phase 4 (0.45–0.65): battery appears, separates down
            // Phase 5 (0.60–0.80): base drops down
            // Phase 6 (0.80–1.00): fully exploded, all labels visible

            // Solar cap — lift UP and forward in Z
            const s1 = ease(map(progress, 0.12, 0.38));
            solar.style.transform = `translateX(-50%) translateY(${-s1 * 140}px) translateZ(${s1 * 60}px)`;

            // Body — slight lift UP
            const s2 = ease(map(progress, 0.28, 0.50));
            body.style.transform = `translateX(-50%) translateY(${-s2 * 40}px) translateZ(${s2 * 20}px)`;

            // PCB — fade in, pop from center
            const pcbFade = ease(map(progress, 0.28, 0.42));
            const pcbMove = ease(map(progress, 0.35, 0.55));
            pcb.style.opacity = pcbFade;
            pcb.style.transform = `translateX(-50%) translateY(${pcbMove * 10}px) translateZ(${pcbFade * 40}px) scale(${0.7 + pcbFade * 0.3})`;

            // Battery — fade in, drop down
            const batFade = ease(map(progress, 0.42, 0.56));
            const batMove = ease(map(progress, 0.48, 0.68));
            battery.style.opacity = batFade;
            battery.style.transform = `translateX(-50%) translateY(${batMove * 50}px) translateZ(${batFade * 30}px) scale(${0.7 + batFade * 0.3})`;

            // Base — drop DOWN and back in Z
            const s5 = ease(map(progress, 0.58, 0.80));
            base.style.transform = `translateX(-50%) translateY(${s5 * 120}px) translateZ(${-s5 * 30}px)`;

            // ─── Labels: progressive reveal ───
            const thresholds = [0.30, 0.42, 0.52, 0.62, 0.74];
            layers.forEach((layer, i) => {
                const tag = layer.querySelector('.ev-tag');
                if (!tag) return;
                tag.classList.toggle('visible', progress >= thresholds[i]);
            });

            // ─── Step dots ───
            const activeStep = progress < 0.15 ? 0
                             : progress < 0.35 ? 1
                             : progress < 0.55 ? 2
                             : progress < 0.75 ? 3
                             : 4;
            steps.forEach((s, i) => s.classList.toggle('active', i === activeStep));

            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });

        // Initial state
        update();
    })();
</script>
</body>
</html>