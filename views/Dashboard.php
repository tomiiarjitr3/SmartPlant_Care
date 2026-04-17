<?php
// ═══════════════════════════════════════════════════════════════
//  Dashboard.php — SmartPlant CARE
//  Muestra datos reales leídos de la base de datos MySQL
// ═══════════════════════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /SmartPlant_Care/views/Login.php");
    exit;
}

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: /SmartPlant_Care/views/Login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db         = Database::connect();
$usuario_id = (int) $_SESSION['usuario_id'];
$nombre     = $_SESSION['nombre'] ?? $_SESSION['usuario'];

// ── 1. Obtener plantas del usuario ──────────────────────────────
$stmt   = $db->prepare("SELECT * FROM plantas WHERE usuario_id = ? AND activa = 1 ORDER BY id ASC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$plantas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Planta seleccionada (primera por defecto o la del parámetro GET)
$planta_id = isset($_GET['planta']) ? (int)$_GET['planta'] : ($plantas[0]['id'] ?? 0);

// ── 2. Última lectura del sensor ────────────────────────────────
$ultima = null;
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT l.* FROM lecturas_sensores l
        INNER JOIN dispositivos d ON l.dispositivo_id = d.id
        WHERE l.planta_id = ?
        ORDER BY l.creada_en DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $planta_id);
    $stmt->execute();
    $ultima = $stmt->get_result()->fetch_assoc();
}

// ── 3. Historial de humedad últimas 12h (para el mini-chart) ───
$historial = [];
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT humedad_suelo, DATE_FORMAT(creada_en, '%H:%i') AS hora
        FROM lecturas_sensores
        WHERE planta_id = ? AND creada_en >= NOW() - INTERVAL 12 HOUR
        ORDER BY creada_en ASC
        LIMIT 24
    ");
    $stmt->bind_param("i", $planta_id);
    $stmt->execute();
    $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ── 4. Eventos recientes (log) ──────────────────────────────────
$eventos = [];
if ($planta_id) {
    $stmt = $db->prepare("
        SELECT * FROM eventos
        WHERE planta_id = ?
        ORDER BY creado_en DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $planta_id);
    $stmt->execute();
    $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ── Valores con fallback si no hay lectura ──────────────────────
$humedad   = $ultima ? (float)$ultima['humedad_suelo']  : 0;
$temp      = $ultima ? (float)$ultima['temperatura']    : 0;
$luz       = $ultima ? (int)  $ultima['luz_ambiental']  : 0;
$tanque    = $ultima ? (int)  $ultima['nivel_tanque']   : 0;
$bateria   = $ultima ? (int)  $ultima['bateria']        : 0;

// ── Salud general simple (promedio ponderado) ───────────────────
$salud = $ultima ? min(100, round(($humedad + $bateria) / 2)) : 0;

// ── Íconos y textos de eventos ─────────────────────────────────
$evento_icono = [
    'riego'              => ['icon' => '💧', 'color' => 'green'],
    'alerta_humedad'     => ['icon' => '⚠️', 'color' => 'yellow'],
    'alerta_temperatura' => ['icon' => '🌡️', 'color' => 'orange'],
    'bateria_baja'       => ['icon' => '🔋', 'color' => 'cyan'],
    'sin_conexion'       => ['icon' => '📡', 'color' => 'red'],
    'otro'               => ['icon' => '🔔', 'color' => 'gray'],
];
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SmartPlant CARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
</head>

<body class="bg-solid-dark text-white min-h-screen">

<!-- Scroll Progress Bar -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto max-w-6xl px-4">
    <div class="glass-clean flex items-center justify-between px-8 md:px-10 py-5 rounded-[2.5rem]">
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </a>
        <div class="hidden md:flex items-center gap-3">
            <div class="status-dot"></div>
            <span class="text-xs font-medium text-gray-400 tracking-wide">Sistema activo</span>
        </div>
        <div class="flex items-center gap-5">
            <div class="hidden md:flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-green-500/20 to-cyan-500/20 border border-white/10 flex items-center justify-center text-sm">👤</div>
                <div>
                    <p class="text-sm font-medium text-white/90 leading-tight"><?= htmlspecialchars($nombre) ?></p>
                    <p class="text-[10px] text-gray-500 font-light capitalize"><?= $_SESSION['plan'] ?? 'free' ?></p>
                </div>
            </div>
            <a href="?logout=1" class="bg-white/5 border border-white/10 text-white/70 px-5 py-2.5 rounded-full text-xs font-medium hover:bg-white/10 hover:text-white transition-all">
                Cerrar sesión
            </a>
        </div>
    </div>
</header>

<!-- ═══ HERO / WELCOME ═══ -->
<section class="max-w-6xl mx-auto px-6 pt-20 pb-8">
    <div class="reveal-blur">
        <span class="text-green-400 font-semibold tracking-[0.2em] text-xs uppercase">Dashboard</span>
        <h2 class="text-4xl md:text-6xl font-semibold tracking-tight mt-3">
            Hola, <span class="text-gradient-anim"><?= htmlspecialchars(explode(' ', $nombre)[0]) ?>.</span>
        </h2>
        <p class="text-gray-500 text-lg font-light mt-3 max-w-xl">
            <?php if ($ultima): ?>
                Última lectura: <?= date('d/m/Y H:i', strtotime($ultima['creada_en'])) ?> — Todo bajo control.
            <?php else: ?>
                Todavía no hay lecturas de sensores. Conectá tu ESP32.
            <?php endif; ?>
        </p>
    </div>

    <?php if (count($plantas) > 1): ?>
    <!-- Selector de planta si tiene más de una -->
    <div class="mt-6 flex gap-3 flex-wrap">
        <?php foreach ($plantas as $p): ?>
        <a href="?planta=<?= $p['id'] ?>"
           class="px-5 py-2 rounded-full text-sm border transition-all <?= $p['id'] == $planta_id ? 'bg-green-500 text-black border-green-500 font-semibold' : 'border-white/10 text-gray-400 hover:border-white/25 hover:text-white' ?>">
            🌿 <?= htmlspecialchars($p['nombre']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ═══ STATS CARDS ═══ -->
<section class="max-w-6xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 stagger-children" id="statsGrid">

        <!-- 💧 Humedad -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-green w-12 h-12 rounded-2xl flex items-center justify-center text-xl">💧</div>
                <span class="text-[10px] font-medium text-green-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Humedad del suelo</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-green"
               data-target="<?= $humedad ?>" data-suffix="%">0%</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-green-500 to-cyan-400"
                     style="width:0%" data-width="<?= $humedad ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Óptimo: 40–60%</p>
        </div>

        <!-- 🌡️ Temperatura -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-orange w-12 h-12 rounded-2xl flex items-center justify-center text-xl">🌡️</div>
                <span class="text-[10px] font-medium text-orange-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Temperatura</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-orange"
               data-target="<?= $temp ?>" data-suffix="°C">0°C</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-orange-500 to-pink-400"
                     style="width:0%" data-width="<?= min(100, round($temp / 50 * 100)) ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">Ideal: 20–28°C</p>
        </div>

        <!-- ☀️ Luz -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-blue w-12 h-12 rounded-2xl flex items-center justify-center text-xl">☀️</div>
                <span class="text-[10px] font-medium text-blue-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Luz Ambiental</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-blue"
               data-target="<?= $luz ?>" data-suffix=" lx">0 lx</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-blue-500 to-indigo-400"
                     style="width:0%" data-width="<?= min(100, round($luz / 1000 * 100)) ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">
                <?= $luz > 600 ? 'Buena iluminación' : ($luz > 200 ? 'Luz moderada' : 'Poca luz') ?>
            </p>
        </div>

        <!-- 🪣 Tanque -->
        <div class="card-stat rounded-[2rem] p-7">
            <div class="flex items-center justify-between mb-6">
                <div class="icon-glow-cyan w-12 h-12 rounded-2xl flex items-center justify-center text-xl">🪣</div>
                <span class="text-[10px] font-medium text-cyan-400/60 tracking-widest uppercase">En vivo</span>
            </div>
            <p class="text-gray-500 text-xs font-medium tracking-wide uppercase mb-2">Nivel del Tanque</p>
            <p class="text-5xl font-bold tracking-tighter text-gradient-cyan"
               data-target="<?= $tanque ?>" data-suffix="%">0%</p>
            <div class="progress-track h-1.5 mt-5">
                <div class="progress-fill h-full bg-gradient-to-r from-cyan-500 to-indigo-400"
                     style="width:0%" data-width="<?= $tanque ?>%"></div>
            </div>
            <p class="text-[11px] text-gray-600 font-light mt-3">
                <?= $tanque > 50 ? 'Capacidad suficiente' : ($tanque > 20 ? 'Nivel medio' : 'Nivel bajo — Rellenar') ?>
            </p>
        </div>

    </div>
</section>

<!-- ═══ CHART + ESTADO PLANTA ═══ -->
<section class="max-w-6xl mx-auto px-6 py-6">
    <div class="grid lg:grid-cols-3 gap-5">

        <!-- Mini chart humedad -->
        <div class="lg:col-span-2 card-glass rounded-[2rem] p-8 reveal">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Humedad del suelo</p>
                    <p class="text-2xl font-semibold tracking-tight mt-1">Últimas 12 horas</p>
                </div>
            </div>
            <div class="flex items-end gap-2 h-36" id="chartBars">
                <?php if (!empty($historial)): ?>
                    <?php foreach ($historial as $h): ?>
                        <?php $pct = min(100, max(5, round((float)$h['humedad_suelo']))); ?>
                        <div class="mini-bar flex-1" style="height:<?= $pct ?>%;" title="<?= $h['hora'] ?> — <?= $h['humedad_suelo'] ?>%"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ([35,50,40,65,60,55,70,45,58,62,50,47] as $v): ?>
                        <div class="mini-bar flex-1" style="height:<?= $v ?>%;opacity:0.3;"></div>
                    <?php endforeach; ?>
                    <p class="absolute text-gray-600 text-xs font-light ml-2">Sin datos aún</p>
                <?php endif; ?>
            </div>
            <div class="flex justify-between mt-4">
                <?php if (!empty($historial)): ?>
                    <span class="text-[10px] text-gray-600"><?= $historial[0]['hora'] ?? '' ?></span>
                    <span class="text-[10px] text-gray-600"><?= $historial[count($historial)-1]['hora'] ?? '' ?></span>
                <?php else: ?>
                    <span class="text-[10px] text-gray-600">00:00</span>
                    <span class="text-[10px] text-gray-600">12:00</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estado de la planta -->
        <div class="card-glass rounded-[2rem] p-8 reveal flex flex-col items-center justify-center text-center">
            <div class="ring-gauge mb-6">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle class="ring-bg" cx="60" cy="60" r="52"/>
                    <circle class="ring-fill" cx="60" cy="60" r="52"
                        stroke="url(#ringGrad)"
                        stroke-dasharray="326.73"
                        stroke-dashoffset="326.73"
                        id="healthRing"/>
                    <defs>
                        <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#4ade80"/>
                            <stop offset="100%" stop-color="#22d3ee"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div>
                        <p class="text-3xl font-bold tracking-tighter text-gradient-green"><?= $salud ?></p>
                        <p class="text-[9px] text-gray-500 font-light uppercase tracking-widest">salud</p>
                    </div>
                </div>
            </div>
            <h3 class="text-xl font-semibold tracking-tight mb-2">
                <?= $salud >= 75 ? 'Estado excelente' : ($salud >= 50 ? 'Estado regular' : 'Necesita atención') ?>
            </h3>
            <p class="text-gray-500 text-xs font-light leading-relaxed max-w-[200px]">
                <?= $salud >= 75 ? 'Tu planta está en condiciones óptimas. 🌿' : 'Revisá los niveles de humedad y batería.' ?>
            </p>
            <div class="divider-glass w-full my-6"></div>
            <div class="w-full space-y-3 text-left">
                <div class="flex justify-between">
                    <span class="text-[11px] text-gray-500">Hidratación</span>
                    <span class="text-[11px] font-medium <?= $humedad >= 40 ? 'text-green-400' : 'text-red-400' ?>">
                        <?= $humedad >= 40 ? 'Óptima' : 'Baja' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[11px] text-gray-500">Temperatura</span>
                    <span class="text-[11px] font-medium <?= ($temp >= 20 && $temp <= 28) ? 'text-green-400' : 'text-orange-400' ?>">
                        <?= ($temp >= 20 && $temp <= 28) ? 'Normal' : 'Fuera de rango' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[11px] text-gray-500">Luz solar</span>
                    <span class="text-[11px] font-medium <?= $luz > 600 ? 'text-yellow-400' : 'text-gray-400' ?>">
                        <?= $luz > 600 ? 'Alta' : ($luz > 200 ? 'Media' : 'Baja') ?>
                    </span>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ═══ ACCIONES + ACTIVIDAD ═══ -->
<section class="max-w-6xl mx-auto px-6 py-6 pb-16">
    <div class="grid lg:grid-cols-2 gap-5">

        <!-- Acciones rápidas -->
        <div class="card-glass rounded-[2rem] p-8 reveal">
            <div class="mb-8">
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Control</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Acciones rápidas</p>
            </div>
            <div class="space-y-4">
                <button class="btn-action w-full flex items-center gap-5 bg-green-500/[0.07] border border-green-500/15 rounded-2xl p-5 text-left hover:border-green-500/30 group">
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">💧</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Regar ahora</p>
                        <p class="text-[11px] text-gray-500 font-light">Activar riego manual por 30 segundos</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
                <button class="btn-action w-full flex items-center gap-5 bg-blue-500/[0.07] border border-blue-500/15 rounded-2xl p-5 text-left hover:border-blue-500/30 group">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">🔄</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Actualizar lecturas</p>
                        <p class="text-[11px] text-gray-500 font-light">Forzar lectura del sensor ahora</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
                <button class="btn-action w-full flex items-center gap-5 bg-purple-500/[0.07] border border-purple-500/15 rounded-2xl p-5 text-left hover:border-purple-500/30 group">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">⚙️</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white/90">Configuración</p>
                        <p class="text-[11px] text-gray-500 font-light">Ajustar umbrales y notificaciones</p>
                    </div>
                    <span class="text-gray-600 text-lg">→</span>
                </button>
            </div>
        </div>

        <!-- Log de actividad -->
        <div class="card-glass rounded-[2rem] p-8 reveal">
            <div class="mb-8">
                <div class="line-accent mb-4"></div>
                <p class="text-xs font-medium text-gray-500 tracking-widest uppercase">Log</p>
                <p class="text-2xl font-semibold tracking-tight mt-1">Actividad reciente</p>
            </div>
            <div class="space-y-5">
                <?php if (!empty($eventos)): ?>
                    <?php foreach ($eventos as $i => $ev): ?>
                        <?php
                        $info  = $evento_icono[$ev['tipo']] ?? $evento_icono['otro'];
                        $color = $info['color'];
                        $icon  = $info['icon'];
                        $last  = $i === count($eventos) - 1;
                        ?>
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl bg-<?= $color ?>-500/10 border border-<?= $color ?>-500/20 flex items-center justify-center text-sm"><?= $icon ?></div>
                                <?php if (!$last): ?>
                                    <div class="timeline-line flex-1 mt-2"></div>
                                <?php endif; ?>
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-medium text-white/90"><?= htmlspecialchars($ev['mensaje']) ?></p>
                                <p class="text-[10px] text-gray-600 mt-1">
                                    <?php
                                    $diff = time() - strtotime($ev['creado_en']);
                                    if     ($diff < 3600)   echo 'Hace ' . round($diff/60)  . ' min';
                                    elseif ($diff < 86400)  echo 'Hace ' . round($diff/3600) . ' h';
                                    else                    echo 'Hace ' . round($diff/86400) . ' días';
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600 text-sm font-light">Sin eventos recientes.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Footer -->
<footer class="max-w-6xl mx-auto px-6 py-10">
    <div class="divider-glass mb-6"></div>
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <span class="text-xs text-gray-600 font-light flex items-center gap-2"><span class="text-green-400">🌱</span> SmartPlant CARE — Dashboard</span>
        <div class="flex gap-6">
            <a href="/SmartPlant_Care/index.php" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Inicio</a>
            <a href="#" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Soporte</a>
        </div>
        <p class="text-[10px] text-gray-700">© 2026 Todos los derechos reservados.</p>
    </div>
</footer>

<script>
    // ─── Scroll Progress ───
    window.addEventListener('scroll', () => {
        const pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        document.getElementById('scrollProgress').style.width = pct + '%';
    });

    // ─── Intersection Observer ───
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('active');
                if (e.target.id === 'statsGrid') { animateCounters(); animateProgressBars(); }
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.reveal, .reveal-blur, .reveal-scale, .stagger-children').forEach(el => observer.observe(el));

    // ─── Counter Animation ───
    let done = false;
    function animateCounters() {
        if (done) return; done = true;
        document.querySelectorAll('[data-target]').forEach(el => {
            const target = parseFloat(el.getAttribute('data-target'));
            const suffix = el.getAttribute('data-suffix') || '';
            const dur    = 2000;
            const start  = performance.now();
            function step(now) {
                const p = Math.min((now - start) / dur, 1);
                const e = 1 - Math.pow(1 - p, 3);
                el.textContent = (Number.isInteger(target) ? Math.round(e * target) : (e * target).toFixed(1)) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    // ─── Progress Bars ───
    function animateProgressBars() {
        document.querySelectorAll('.progress-fill[data-width]').forEach(bar => {
            setTimeout(() => bar.style.width = bar.dataset.width, 400);
        });
    }

    // ─── Health Ring ───
    function animateRing() {
        const ring = document.getElementById('healthRing');
        if (!ring) return;
        const circ   = 326.73;
        const health = <?= $salud ?>;
        setTimeout(() => ring.style.strokeDashoffset = circ - (circ * health / 100), 700);
    }

    // ─── Init ───
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => document.querySelectorAll('.reveal-blur').forEach(el => el.classList.add('active')), 80);
        animateRing();
    });
</script>
</body>
</html>