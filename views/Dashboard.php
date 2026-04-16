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
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #0a0a0a;
        }

        /* ═══════════════════════════════════════════
           FONDO SÓLIDO CON GRADIENTES SUTILES
           ═══════════════════════════════════════════ */
        .bg-solid-dark {
            background: #0a0a0a;
            position: relative;
        }
        .bg-solid-dark::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(74, 222, 128, 0.03) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(34, 211, 238, 0.03) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(129, 140, 248, 0.02) 0%, transparent 60%);
            z-index: 0;
            pointer-events: none;
        }

        /* ═══════════════════════════════════════════
           GLASS STYLES
           ═══════════════════════════════════════════ */
        .glass-clean {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.3);
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-glass:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .card-stat {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .card-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        }
        .card-stat:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.12);
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }

        /* ═══════════════════════════════════════════
           GRADIENTES DE TEXTO
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

        .text-gradient-green {
            background: linear-gradient(135deg, #4ade80, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-blue {
            background: linear-gradient(135deg, #60a5fa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-orange {
            background: linear-gradient(135deg, #fb923c, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-cyan {
            background: linear-gradient(135deg, #22d3ee, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ═══════════════════════════════════════════
           ICON CONTAINERS CON GLOW
           ═══════════════════════════════════════════ */
        .icon-glow-green {
            background: rgba(74, 222, 128, 0.08);
            border: 1px solid rgba(74, 222, 128, 0.15);
            box-shadow: 0 0 30px rgba(74, 222, 128, 0.05);
        }
        .icon-glow-blue {
            background: rgba(96, 165, 250, 0.08);
            border: 1px solid rgba(96, 165, 250, 0.15);
            box-shadow: 0 0 30px rgba(96, 165, 250, 0.05);
        }
        .icon-glow-orange {
            background: rgba(251, 146, 60, 0.08);
            border: 1px solid rgba(251, 146, 60, 0.15);
            box-shadow: 0 0 30px rgba(251, 146, 60, 0.05);
        }
        .icon-glow-cyan {
            background: rgba(34, 211, 238, 0.08);
            border: 1px solid rgba(34, 211, 238, 0.15);
            box-shadow: 0 0 30px rgba(34, 211, 238, 0.05);
        }

        /* ═══════════════════════════════════════════
           PROGRESS BARS
           ═══════════════════════════════════════════ */
        .progress-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            border-radius: 999px;
            transition: width 2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .progress-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* ═══════════════════════════════════════════
           STATUS INDICATOR
           ═══════════════════════════════════════════ */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 12px rgba(74, 222, 128, 0.6);
            animation: statusPulse 2s ease-in-out infinite;
        }
        @keyframes statusPulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 12px rgba(74, 222, 128, 0.6); }
            50% { opacity: 0.6; box-shadow: 0 0 20px rgba(74, 222, 128, 0.8); }
        }

        /* ═══════════════════════════════════════════
           ANIMACIONES REVEAL
           ═══════════════════════════════════════════ */
        .reveal {
            opacity: 0;
            transform: translateY(60px);
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-blur {
            opacity: 0;
            filter: blur(20px);
            transform: translateY(30px);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-blur.active {
            opacity: 1;
            filter: blur(0px);
            transform: translateY(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-scale.active {
            opacity: 1;
            transform: scale(1);
        }

        .stagger-children > * {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .stagger-children.active > *:nth-child(1) { transition-delay: 0.05s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(2) { transition-delay: 0.12s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(3) { transition-delay: 0.19s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(4) { transition-delay: 0.26s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(5) { transition-delay: 0.33s; opacity: 1; transform: translateY(0); }
        .stagger-children.active > *:nth-child(6) { transition-delay: 0.40s; opacity: 1; transform: translateY(0); }

        /* ═══════════════════════════════════════════
           LINE ACCENT
           ═══════════════════════════════════════════ */
        .line-accent {
            width: 50px;
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
           MINI CHART (CSS BARS)
           ═══════════════════════════════════════════ */
        .mini-bar {
            background: rgba(74, 222, 128, 0.3);
            border-radius: 4px 4px 0 0;
            transition: all 0.3s ease;
            min-width: 6px;
        }
        .mini-bar:hover {
            background: rgba(74, 222, 128, 0.6);
        }

        /* ═══════════════════════════════════════════
           ACTION BUTTON
           ═══════════════════════════════════════════ */
        .btn-action {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s ease;
        }
        .btn-action:hover::before {
            left: 100%;
        }
        .btn-action:hover {
            transform: scale(1.03);
        }

        /* ═══════════════════════════════════════════
           DIVIDER
           ═══════════════════════════════════════════ */
        .divider-glass {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
        }

        /* ═══════════════════════════════════════════
           TIMELINE DOT
           ═══════════════════════════════════════════ */
        .timeline-line {
            width: 1px;
            background: linear-gradient(180deg, rgba(74, 222, 128, 0.3), transparent);
        }

        /* ═══════════════════════════════════════════
           RING GAUGE (CSS)
           ═══════════════════════════════════════════ */
        .ring-gauge {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            position: relative;
        }
        .ring-gauge svg {
            transform: rotate(-90deg);
        }
        .ring-gauge .ring-bg {
            fill: none;
            stroke: rgba(255,255,255,0.05);
            stroke-width: 8;
        }
        .ring-gauge .ring-fill {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dashoffset 2s cubic-bezier(0.16, 1, 0.3, 1);
        }
    </style>
</head>

<body class="bg-solid-dark text-white min-h-screen">

<!-- Scroll Progress Bar -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- ═══════════════════════════════════════════
     HEADER / NAV
     ═══════════════════════════════════════════ -->
<header class="sticky top-6 z-50 mx-auto max-w-6xl px-4">
    <div class="glass-clean flex items-center justify-between px-8 md:px-10 py-5 rounded-[2.5rem]">
        
        <!-- Logo -->
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight text-white flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </a>

        <!-- Centro: Status -->
        <div class="hidden md:flex items-center gap-3">
            <div class="status-dot"></div>
            <span class="text-xs font-medium text-gray-400 tracking-wide">Sistema activo</span>
        </div>

        <!-- Derecha: User + Logout -->
        <div class="flex items-center gap-6">
            <div class="hidden md:flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-green-500/20 to-cyan-500/20 border border-white/10 flex items-center justify-center text-sm">
                    👤
                </div>
                <div>
                    <p class="text-sm font-medium text-white/90 leading-tight"><?= $_SESSION["usuario"] ?></p>
                    <p class="text-[10px] text-gray-500 font-light">Plan Premium</p>
                </div>
            </div>
            <a href="?logout=1" class="bg-white/5 border border-white/10 text-white/70 px-5 py-2.5 rounded-full text-xs font-medium hover:bg-white/10 hover:text-white transition-all duration-300">
                Cerrar sesión
            </a>
        </div>
    </div>
</header>

<!-- ═══════════════════════════════════════════
     HERO / WELCOME
     ═══════════════════════════════════════════ -->
<section class="max-w-6xl mx-auto px-6 pt-20 pb-10">
    <div class="reveal-blur">
        <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Dashboard</span>
        <h2 class="text-4xl md:text-6xl font-semibold tracking-tighter mt-3">
            Tu jardín, en <span class="text-gradient-anim">tiempo real.</span>
        </h2>
        <p class="text-gray-500 text-lg font-light mt-4 max-w-xl">
            Toda la información de tus sensores actualizada al instante. Todo bajo control.
        </p>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     STATS PRINCIPALES (4 CARDS)
     ═══════════════════════════════════════════ -->
<section class="max-w-6xl mx-auto px-6 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 stagger-children" id="statsGrid">

        <!-- 💧 Humedad del suelo -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-green w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                    💧
                </div>
                <span class="text-[10px] font-medium text-green-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Humedad del suelo</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-green" data-target="42" data-suffix="%">0%</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-green-500 to-cyan-400" style="width: 0%" data-width="42%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Óptimo: 40-60%</p>
        </div>

        <!-- 🌡️ Temperatura -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-orange w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                    🌡️
                </div>
                <span class="text-[10px] font-medium text-orange-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Temperatura</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-orange" data-target="26" data-suffix="°C">0°C</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-orange-500 to-pink-400" style="width: 0%" data-width="52%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Ideal: 20-28°C</p>
        </div>

        <!-- ☀️ Luz -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-blue w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                    ☀️
                </div>
                <span class="text-[10px] font-medium text-blue-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Luz Ambiental</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-blue" data-target="780" data-suffix=" lx">0 lx</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-blue-500 to-indigo-400" style="width: 0%" data-width="78%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Buena iluminación</p>
        </div>

        <!-- 🪣 Tanque -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-cyan w-12 h-12 rounded-2xl flex items-center justify-center text-xl">
                    🪣
                </div>
                <span class="text-[10px] font-medium text-cyan-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Nivel del Tanque</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-cyan" data-target="80" data-suffix="%">0%</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-cyan-500 to-indigo-400" style="width: 0%" data-width="80%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Capacidad suficiente</p>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════
     SECCIÓN MEDIA: GRÁFICO + ESTADO PLANTA
     ═══════════════════════════════════════════ -->
<section class="max-w-6xl mx-auto px-6 py-10">
    <div class="grid lg:grid-cols-3 gap-5">

        <!-- MINI CHART - Humedad últimas 12h -->
        <div class="lg:col-span-2 card-glass rounded-[2rem] p-8 reveal">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Humedad del suelo</p>
                    <p class="text-2xl font-semibold tracking-tight mt-1">Últimas 12 horas</p>
                </div>
                <div class="flex gap-2">
                    <button class="bg-white/5 border border-white/10 text-xs text-gray-400 px-4 py-2 rounded-full hover:bg-white/10 transition-all">12h</button>
                    <button class="bg-white/5 border border-white/10 text-xs text-gray-400 px-4 py-2 rounded-full hover:bg-white/10 transition-all">24h</button>
                    <button class="bg-white/5 border border-white/10 text-xs text-gray-400 px-4 py-2 rounded-full hover:bg-white/10 transition-all">7d</button>
                </div>
            </div>

            <!-- CSS Bar Chart -->
            <div class="flex items-end gap-2 h-40" id="chartBars">
                <div class="mini-bar flex-1" style="height: 55%;" data-value="35"></div>
                <div class="mini-bar flex-1" style="height: 60%;" data-value="38"></div>
                <div class="mini-bar flex-1" style="height: 50%;" data-value="32"></div>
                <div class="mini-bar flex-1" style="height: 70%;" data-value="44"></div>
                <div class="mini-bar flex-1" style="height: 65%;" data-value="41"></div>
                <div class="mini-bar flex-1" style="height: 75%;" data-value="47"></div>
                <div class="mini-bar flex-1" style="height: 68%;" data-value="43"></div>
                <div class="mini-bar flex-1" style="height: 80%;" data-value="50"></div>
                <div class="mini-bar flex-1" style="height: 55%;" data-value="35"></div>
                <div class="mini-bar flex-1" style="height: 62%;" data-value="39"></div>
                <div class="mini-bar flex-1" style="height: 72%;" data-value="45"></div>
                <div class="mini-bar flex-1" style="height: 67%;" data-value="42"></div>
            </div>

            <!-- Labels -->
            <div class="flex justify-between mt-4">
                <span class="text-[10px] text-gray-600 font-light">00:00</span>
                <span class="text-[10px] text-gray-600 font-light">06:00</span>
                <span class="text-[10px] text-gray-600 font-light">12:00</span>
            </div>
        </div>

        <!-- ESTADO DE LA PLANTA -->
        <div class="card-glass rounded-[2rem] p-8 reveal flex flex-col items-center justify-center text-center">
            
            <!-- Ring Gauge -->
            <div class="ring-gauge mb-6">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle class="ring-bg" cx="60" cy="60" r="52"/>
                    <circle 
                        class="ring-fill" 
                        cx="60" cy="60" r="52"
                        stroke="url(#ringGrad)"
                        stroke-dasharray="326.73"
                        stroke-dashoffset="326.73"
                        id="healthRing"
                    />
                    <defs>
                        <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#4ade80"/>
                            <stop offset="100%" stop-color="#22d3ee"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div>
                        <p class="text-3xl font-bold tracking-tighter text-gradient-green">85</p>
                        <p class="text-[9px] text-gray-500 font-light uppercase tracking-widest">salud</p>
                    </div>
                </div>
            </div>

            <h3 class="text-xl font-semibold tracking-tight mb-2">Estado excelente</h3>
            <p class="text-gray-500 text-xs font-light leading-relaxed max-w-[200px]">
                Tu planta está en condiciones óptimas. Seguí así 🌿
            </p>

            <div class="divider-glass w-full my-6"></div>

            <div class="w-full space-y-3 text-left">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] text-gray-500">Hidratación</span>
                    <span class="text-[11px] text-green-400 font-medium">Óptima</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] text-gray-500">Temperatura</span>
                    <span class="text-[11px] text-green-400 font-medium">Normal</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] text-gray-500">Luz solar</span>
                    <span class="text-[11px] text-yellow-400 font-medium">Alta</span>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════
     ACCIONES RÁPIDAS + ACTIVIDAD RECIENTE
     ═══════════════════════════════════════════ -->
<section class="max-w-6xl mx-auto px-6 py-10">
    <div class="grid lg:grid-cols-2 gap-5">

        <!-- ACCIONES RÁPIDAS -->
        <div class="card-glass rounded-[2rem] p-8 reveal">
            <div class="mb-8">
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Control</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Acciones rápidas</p>
            </div>

            <div class="space-y-4">
                <button class="btn-action w-full flex items-center gap-5 bg-green-500/8 border border-green-500/15 rounded-2xl p-5 text-left hover:border-green-500/30 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        💧
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Regar ahora</p>
                        <p class="text-[11px] text-gray-500 font-light">Activar riego manual por 30 segundos</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>

                <button class="btn-action w-full flex items-center gap-5 bg-blue-500/8 border border-blue-500/15 rounded-2xl p-5 text-left hover:border-blue-500/30 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Ver reportes</p>
                        <p class="text-[11px] text-gray-500 font-light">Historial completo de sensores</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>

                <button class="btn-action w-full flex items-center gap-5 bg-purple-500/8 border border-purple-500/15 rounded-2xl p-5 text-left hover:border-purple-500/30 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        ⚙️
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Configuración</p>
                        <p class="text-[11px] text-gray-500 font-light">Ajustar umbrales y notificaciones</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
            </div>
        </div>

        <!-- ACTIVIDAD RECIENTE -->
        <div class="card-glass rounded-[2rem] p-8 reveal">
            <div class="mb-8">
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Log</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Actividad reciente</p>
            </div>

            <div class="space-y-6">
                <!-- Item 1 -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-sm">💧</div>
                        <div class="timeline-line flex-1 mt-2"></div>
                    </div>
                    <div class="pb-6">
                        <p class="text-sm font-medium text-white/90">Riego automático activado</p>
                        <p class="text-[11px] text-gray-500 font-light mt-1">Humedad bajó al 30% — Se regó por 45s</p>
                        <p class="text-[10px] text-gray-600 mt-2">Hace 2 horas</p>
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center text-sm">⚠️</div>
                        <div class="timeline-line flex-1 mt-2"></div>
                    </div>
                    <div class="pb-6">
                        <p class="text-sm font-medium text-white/90">Alerta de temperatura</p>
                        <p class="text-[11px] text-gray-500 font-light mt-1">Temperatura subió a 32°C brevemente</p>
                        <p class="text-[10px] text-gray-600 mt-2">Hace 5 horas</p>
                    </div>
                </div>

                <!-- Item 3 -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-sm">🔋</div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white/90">Batería cargada al 100%</p>
                        <p class="text-[11px] text-gray-500 font-light mt-1">Panel solar recargó la batería completa</p>
                        <p class="text-[10px] text-gray-600 mt-2">Hace 8 horas</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════
     FOOTER MINI
     ═══════════════════════════════════════════ -->
<footer class="max-w-6xl mx-auto px-6 py-12">
    <div class="divider-glass mb-8"></div>
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-green-400">🌱</span>
            <span class="text-xs text-gray-600 font-light">SmartPlant CARE — Dashboard v2.0</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="/SmartPlant_Care/index.php" class="text-xs text-gray-600 hover:text-gray-400 transition-colors font-light">Inicio</a>
            <a href="/SmartPlant_Care/store.php" class="text-xs text-gray-600 hover:text-gray-400 transition-colors font-light">Tienda</a>
            <a href="#" class="text-xs text-gray-600 hover:text-gray-400 transition-colors font-light">Soporte</a>
        </div>
        <p class="text-[10px] text-gray-700 font-light">© 2026 Todos los derechos reservados.</p>
    </div>
</footer>

<!-- ═══════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════ -->
<script>
    // ─── Scroll Progress Bar ───
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        document.getElementById('scrollProgress').style.width = scrollPercent + '%';
    });

    // ─── Intersection Observer ───
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -30px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');

                // Animar counters cuando statsGrid es visible
                if (entry.target.id === 'statsGrid') {
                    animateCounters();
                    animateProgressBars();
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal, .reveal-blur, .reveal-scale, .stagger-children').forEach(el => {
        observer.observe(el);
    });

    // ─── Counter Animation ───
    let countersAnimated = false;
    function animateCounters() {
        if (countersAnimated) return;
        countersAnimated = true;

        document.querySelectorAll('[data-target]').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const suffix = counter.getAttribute('data-suffix') || '';
            const duration = 2000;
            const start = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);

                counter.textContent = current + suffix;

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            requestAnimationFrame(update);
        });
    }

    // ─── Progress Bar Animation ───
    function animateProgressBars() {
        document.querySelectorAll('.progress-fill[data-width]').forEach(bar => {
            setTimeout(() => {
                bar.style.width = bar.getAttribute('data-width');
            }, 500);
        });
    }

    // ─── Health Ring Animation ───
    function animateHealthRing() {
        const ring = document.getElementById('healthRing');
        if (!ring) return;
        const circumference = 326.73;
        const healthPercent = 85;
        const offset = circumference - (circumference * healthPercent / 100);
        
        setTimeout(() => {
            ring.style.strokeDashoffset = offset;
        }, 800);
    }

    // ─── Init on load ───
    window.addEventListener('DOMContentLoaded', () => {
        // Trigger reveal-blur inmediatamente
        setTimeout(() => {
            document.querySelectorAll('.reveal-blur').forEach(el => {
                el.classList.add('active');
            });
        }, 100);

        // Animar el health ring
        setTimeout(animateHealthRing, 600);
    });
</script>

</body>
</html>