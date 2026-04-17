<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$logged = isset($_SESSION['usuario_id']);

$products = [
    [
        'id'       => 'smartplant-sensor',
        'name'     => 'SmartPlant Sensor',
        'tag'      => 'Más vendido',
        'tagColor' => 'green',
        'shortDesc'=> 'Sensor inteligente con panel solar integrado. Monitorea humedad, temperatura y luz en tiempo real.',
        'longDesc' => 'El SmartPlant Sensor es un dispositivo compacto y resistente al agua (IP67) diseñado para vivir al aire libre. Equipado con panel solar, conectividad WiFi + Bluetooth y 5 sensores de alta precisión, te permite monitorear tus plantas 24/7 desde cualquier lugar del mundo. Su setup toma menos de 5 minutos y no requiere herramientas.',
        'price'    => 54990,
        'oldPrice' => 74990,
        'image'    => '/SmartPlant_Care/assets/product-device.png',
        'features' => ['Panel solar integrado', '5 sensores', 'WiFi + Bluetooth', 'IP67'],
        'specs'    => [
            'Autonomía'     => '120 hs sin sol',
            'Conectividad'  => 'WiFi 2.4GHz + BLE 5.0',
            'Sensores'      => 'Humedad, Temp, Luz, pH, NPK',
            'Protección'    => 'IP67 — Sumergible',
            'Peso'          => '148 g',
            'Dimensiones'   => '4.2 × 4.2 × 12 cm',
        ],
        'colors'   => [
            ['name' => 'Verde Bosque', 'hex' => '#3a5a40', 'image' => '/SmartPlant_Care/assets/product-device.png'],
            ['name' => 'Negro Mate',   'hex' => '#1a1a1a', 'image' => '/SmartPlant_Care/assets/product-device-black.png'],
            ['name' => 'Blanco Perla', 'hex' => '#e8e4de', 'image' => '/SmartPlant_Care/assets/product-device-white.png'],
        ],
        'gallery'  => [
            '/SmartPlant_Care/assets/product-device.png',
            '/SmartPlant_Care/assets/product-lifestyle.png',
            '/SmartPlant_Care/assets/product-device-black.png',
            '/SmartPlant_Care/assets/product-device-white.png',
        ],
    ],
    [
        'id'       => 'smartplant-kit',
        'name'     => 'SmartPlant Kit Pro',
        'tag'      => 'Kit completo',
        'tagColor' => 'cyan',
        'shortDesc'=> 'Todo lo que necesitás para automatizar tu jardín: controlador, panel solar, sensores y bomba de riego.',
        'longDesc' => 'El Kit Pro incluye todo lo necesario para convertir cualquier jardín en un sistema inteligente: controlador central con ESP32, panel solar de alta eficiencia, 2 sensores de suelo capacitivos, bomba de riego sumergible y toda la tubería. Conectá, configurá la app y olvidate del riego manual para siempre.',
        'price'    => 149990,
        'oldPrice' => 220000,
        'image'    => '/SmartPlant_Care/assets/product-kit.png',
        'features' => ['Controlador + Panel solar', 'Bomba de riego incluida', '2 sensores de suelo', 'Setup en 5 min'],
        'specs'    => [
            'Controlador'  => 'ESP32 Dual-Core 240MHz',
            'Panel Solar'  => '5V 2W Alta Eficiencia',
            'Bomba'        => '5V USB Sumergible 1.5L/min',
            'Sensores'     => '2× Capacitivo de suelo',
            'Alcance WiFi' => 'Hasta 100m',
            'Contenido'    => '6 piezas + manual',
        ],
        'colors'   => [
            ['name' => 'Negro Standard', 'hex' => '#1a1a1a', 'image' => '/SmartPlant_Care/assets/product-kit.png'],
        ],
        'gallery'  => [
            '/SmartPlant_Care/assets/product-kit.png',
            '/SmartPlant_Care/assets/product-solar.png',
            '/SmartPlant_Care/assets/product-lifestyle.png',
        ],
    ],
    [
        'id'       => 'smartplant-solar',
        'name'     => 'Panel Solar Extra',
        'tag'      => 'Accesorio',
        'tagColor' => 'orange',
        'shortDesc'=> 'Panel solar de alta eficiencia para extender la autonomía de tu SmartPlant. Conexión USB-C.',
        'longDesc' => 'Ampliá la autonomía de tu SmartPlant con este panel solar adicional. Diseñado para exteriores con protección IP67, soporte ajustable de 0° a 45° y conexión USB-C universal. Compatible con todos los dispositivos SmartPlant. Instalación sin herramientas en menos de 2 minutos.',
        'price'    => 24990,
        'oldPrice' => null,
        'image'    => '/SmartPlant_Care/assets/product-solar.png',
        'features' => ['Alta eficiencia', 'USB-C', 'Montaje ajustable', 'Compatible IP67'],
        'specs'    => [
            'Potencia'     => '5V 2W',
            'Conexión'     => 'USB-C Universal',
            'Protección'   => 'IP67',
            'Ángulo'       => 'Ajustable 0°–45°',
            'Peso'         => '95 g',
            'Cable'        => '1.5m trenzado',
        ],
        'colors'   => [
            ['name' => 'Negro Mate', 'hex' => '#1a1a1a', 'image' => '/SmartPlant_Care/assets/product-solar.png'],
        ],
        'gallery'  => [
            '/SmartPlant_Care/assets/product-solar.png',
            '/SmartPlant_Care/assets/product-kit.png',
        ],
    ],
];

function formatPrice($price) {
    return '$' . number_format($price, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda — SmartPlant CARE</title>
    <meta name="description" content="Comprá tu SmartPlant CARE: sensores inteligentes, kits completos y accesorios para el cuidado automatizado de tus plantas.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/SmartPlant_Care/assets/styles.css">
</head>

<body class="bg-overlay text-white min-h-screen">

<div class="scroll-progress" id="scrollProgress"></div>

<!-- ═══ HEADER ═══ -->
<header class="sticky top-6 z-50 mx-auto max-w-5xl px-4">
    <div class="glass-clean flex items-center justify-between px-10 py-5 rounded-[2.5rem]">
        <a href="/SmartPlant_Care/index.php" class="text-2xl font-semibold tracking-tight flex items-center gap-2">
            <span class="text-green-400">🌱</span> SmartPlant
        </a>
        <nav class="hidden md:flex gap-10 items-center text-sm font-medium text-white/80">
            <a href="/SmartPlant_Care/index.php"            class="hover:text-white transition-colors">Inicio</a>
            <a href="/SmartPlant_Care/index.php#utilidades"  class="hover:text-white transition-colors">Utilidades</a>
            <a href="/SmartPlant_Care/index.php#producto"    class="hover:text-white transition-colors">Producto</a>
            <a href="/SmartPlant_Care/views/Store.php"       class="text-green-400 font-semibold">Tienda</a>
            <button class="cart-header-btn" onclick="toggleCart()" aria-label="Carrito">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <span class="cart-badge" id="cartBadge">0</span>
            </button>
            <?php if ($logged): ?>
                <a href="/SmartPlant_Care/views/Dashboard.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">Mi Dashboard</a>
            <?php else: ?>
                <a href="/SmartPlant_Care/views/Login.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold hover:scale-105 transition-all shadow-lg text-sm">Mi cuenta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- ═══ STORE HERO ═══ -->
<section class="flex flex-col items-center text-center pt-36 pb-16 px-6">
    <span class="reveal-blur text-green-400 font-semibold tracking-[0.2em] text-xs uppercase mb-6">Tienda Oficial</span>
    <h1 class="reveal-blur text-5xl md:text-7xl font-semibold tracking-tight mb-6 leading-[1.1]">
        Equipá tu <span class="text-gradient-anim">jardín.</span>
    </h1>
    <p class="reveal text-lg md:text-xl text-gray-400 max-w-xl mx-auto font-light leading-relaxed">
        Tecnología de vanguardia para el cuidado autónomo de tus plantas. Envío a todo el país.
    </p>
</section>

<!-- ═══ PRODUCTS GRID ═══ -->
<section class="max-w-7xl mx-auto px-6 pb-32">
    <div class="grid md:grid-cols-3 gap-8 stagger-children" id="productsGrid">

        <?php foreach ($products as $idx => $p): ?>
        <div class="store-card group" id="<?= $p['id'] ?>">
            <div class="store-card-badge store-badge-<?= $p['tagColor'] ?>"><?= $p['tag'] ?></div>

            <div class="store-card-image-wrap">
                <div class="store-card-glow store-glow-<?= $p['tagColor'] ?>"></div>
                <img src="<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                     class="store-card-img group-hover:scale-110 transition-transform duration-700">
            </div>

            <div class="store-card-body">
                <h3 class="text-2xl font-semibold tracking-tight mb-2"><?= $p['name'] ?></h3>
                <p class="text-gray-400 text-sm font-light leading-relaxed mb-5"><?= $p['shortDesc'] ?></p>

                <div class="store-features">
                    <?php foreach ($p['features'] as $f): ?>
                    <span class="store-feature-tag">✓ <?= $f ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-end gap-3 mb-6">
                    <span class="text-3xl font-bold text-gradient-green"><?= formatPrice($p['price']) ?></span>
                    <?php if ($p['oldPrice']): ?>
                    <span class="text-gray-500 line-through text-lg"><?= formatPrice($p['oldPrice']) ?></span>
                    <?php endif; ?>
                </div>

                <button class="store-btn-info btn-glow w-full" onclick="openModal(<?= $idx ?>)">
                    Más información →
                </button>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<!-- ═══ TRUST BAR ═══ -->
<section class="py-16 border-y border-white/5">
    <div class="max-w-5xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center stagger-children" id="trustBar">
        <?php foreach ([
            ['🚚', 'Envío gratis', 'A todo el país'],
            ['🛡️', 'Garantía 3 años', 'Cobertura total'],
            ['⚡', 'Setup en 5 min', 'Sin herramientas'],
            ['🔄', '30 días', 'Devolución libre'],
        ] as [$icon, $title, $sub]): ?>
        <div class="flex flex-col items-center gap-2">
            <span class="text-3xl mb-1"><?= $icon ?></span>
            <p class="text-white font-semibold text-sm"><?= $title ?></p>
            <p class="text-gray-500 text-xs font-light"><?= $sub ?></p>
        </div>
        <?php endforeach; ?>
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

<!-- ═══════════════════════════════════════════════
     MODAL — DETALLE DE PRODUCTO
═══════════════════════════════════════════════ -->
<div class="modal-overlay" id="productModal">
    <div class="modal-container">
        <!-- Close -->
        <button class="modal-close" onclick="closeModal()" aria-label="Cerrar">✕</button>

        <div class="modal-grid">
            <!-- ── LEFT: Gallery ── -->
            <div class="modal-gallery">
                <div class="modal-main-img-wrap">
                    <img id="modalMainImg" src="" alt="" class="modal-main-img">
                </div>
                <div class="modal-thumbs" id="modalThumbs"></div>
            </div>

            <!-- ── RIGHT: Info ── -->
            <div class="modal-info">
                <span class="modal-badge" id="modalBadge"></span>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mb-3" id="modalName"></h2>

                <div class="flex items-end gap-3 mb-6">
                    <span class="text-4xl font-bold text-gradient-green" id="modalPrice"></span>
                    <span class="text-gray-500 line-through text-xl" id="modalOldPrice"></span>
                </div>

                <p class="text-gray-400 font-light leading-relaxed mb-8" id="modalDesc"></p>

                <!-- Colors -->
                <div id="modalColorsSection" class="mb-8">
                    <p class="text-sm font-semibold text-white/60 uppercase tracking-widest mb-3">Color</p>
                    <div class="flex gap-3 items-center" id="modalColors"></div>
                    <p class="text-sm text-gray-500 mt-2" id="modalColorName"></p>
                </div>

                <!-- Specs -->
                <div class="mb-8">
                    <p class="text-sm font-semibold text-white/60 uppercase tracking-widest mb-4">Especificaciones</p>
                    <div class="modal-specs-grid" id="modalSpecs"></div>
                </div>

                <!-- Features -->
                <div class="store-features mb-8" id="modalFeatures"></div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button class="store-btn btn-glow flex-1" onclick="buyNow()">
                        Comprar ahora
                    </button>
                    <button class="store-btn-cart flex-1" onclick="addToCart()">
                        🛒 Agregar al carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Toast -->
<div id="cartToast" class="store-toast" role="alert">
    <span class="text-green-400 text-lg">✓</span>
    <span id="cartToastMsg">Producto agregado al carrito</span>
</div>

<!-- ═══ CART DRAWER (must be direct child of body for fixed positioning) ═══ -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<aside class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer-header">
        <h2 class="text-xl font-semibold flex items-center gap-2">🛒 Mi Carrito</h2>
        <button class="modal-close" onclick="toggleCart()" aria-label="Cerrar" style="position:static">✕</button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="cart-empty" id="cartEmpty">
            <span class="text-5xl mb-4 block">🌿</span>
            <p class="text-gray-400 font-light">Tu carrito está vacío</p>
            <p class="text-gray-600 text-sm mt-1">Explorá la tienda y agregá productos</p>
        </div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display:none">
        <div class="divider-glass mb-4"></div>
        <div class="flex justify-between items-center mb-6">
            <span class="text-gray-400 font-light">Subtotal</span>
            <span class="text-2xl font-bold text-gradient-green" id="cartSubtotal">$0</span>
        </div>
        <div class="flex flex-col gap-3">
            <button class="checkout-btn checkout-mp" onclick="checkoutMercadoPago()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                Pagar con MercadoPago
            </button>
            <button class="checkout-btn checkout-pp" onclick="checkoutPayPal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 01-.633-.74L4.944 2.56A.759.759 0 015.69 2h5.553c3.011 0 5.1 1.607 4.578 4.878-.53 3.317-3.072 4.95-6.078 4.95H7.963l-1.108 9.17a.476.476 0 01-.468.339h-.311zm12.227-13.12c-.53 3.317-3.072 4.95-6.078 4.95h-1.658l-1.108 6.878h-2.08l2.778-17.48h5.553c1.646 0 2.96.565 3.278 1.583.215.693.116 1.473-.685 4.07z"/></svg>
                Pagar con PayPal
            </button>
        </div>
        <p class="text-center text-gray-600 text-xs mt-4 font-light">Serás redirigido a la plataforma de pago segura</p>
    </div>
</aside>

<script>
    const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let currentProduct = null;
    let selectedColor = 0;

    // ═══════════ CART (localStorage) ═══════════
    function getCart() {
        try { return JSON.parse(localStorage.getItem('sp_cart')) || []; }
        catch { return []; }
    }
    function saveCart(cart) {
        localStorage.setItem('sp_cart', JSON.stringify(cart));
        renderCart();
    }

    function addToCart() {
        const cart = getCart();
        const color = currentProduct.colors[selectedColor];
        const key = currentProduct.id + '_' + color.name;
        const existing = cart.find(i => i.key === key);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({
                key, id: currentProduct.id,
                name: currentProduct.name,
                color: color.name,
                price: currentProduct.price,
                image: color.image,
                qty: 1
            });
        }
        saveCart(cart);
        showToast(`${currentProduct.name} — ${color.name} agregado al carrito`);
        closeModal();
        setTimeout(() => toggleCart(true), 350);
    }

    function removeFromCart(key) {
        saveCart(getCart().filter(i => i.key !== key));
    }

    function updateQty(key, delta) {
        const cart = getCart();
        const item = cart.find(i => i.key === key);
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) return removeFromCart(key);
        saveCart(cart);
    }

    function renderCart() {
        const cart = getCart();
        const container = document.getElementById('cartItems');
        const empty = document.getElementById('cartEmpty');
        const footer = document.getElementById('cartFooter');
        const badge = document.getElementById('cartBadge');
        const totalQty = cart.reduce((s, i) => s + i.qty, 0);
        const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);

        badge.textContent = totalQty;
        badge.style.display = totalQty > 0 ? '' : 'none';

        if (cart.length === 0) {
            empty.style.display = '';
            footer.style.display = 'none';
            container.querySelectorAll('.cart-item').forEach(el => el.remove());
            return;
        }

        empty.style.display = 'none';
        footer.style.display = '';
        document.getElementById('cartSubtotal').textContent = formatPrice(subtotal);

        // Remove old items
        container.querySelectorAll('.cart-item').forEach(el => el.remove());

        cart.forEach(item => {
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                <div class="cart-item-info">
                    <p class="font-semibold text-sm">${item.name}</p>
                    <p class="text-gray-500 text-xs">${item.color}</p>
                    <p class="text-green-400 font-bold text-sm mt-1">${formatPrice(item.price)}</p>
                </div>
                <div class="cart-item-controls">
                    <button class="cart-qty-btn" onclick="updateQty('${item.key}',-1)">−</button>
                    <span class="cart-qty-num">${item.qty}</span>
                    <button class="cart-qty-btn" onclick="updateQty('${item.key}',1)">+</button>
                </div>
                <button class="cart-remove-btn" onclick="removeFromCart('${item.key}')" title="Eliminar">✕</button>
            `;
            container.appendChild(div);
        });
    }

    function toggleCart(forceOpen) {
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartOverlay');
        const isOpen = drawer.classList.contains('cart-drawer-open');
        if (forceOpen === true && isOpen) return;
        drawer.classList.toggle('cart-drawer-open');
        overlay.classList.toggle('cart-overlay-visible');
        document.body.style.overflow = drawer.classList.contains('cart-drawer-open') ? 'hidden' : '';
    }

    // ═══════════ CHECKOUT ═══════════
    function checkoutMercadoPago() {
        const cart = getCart();
        if (!cart.length) return;
        // En producción: llamar a tu backend para crear preferencia de pago
        // Ejemplo: window.location.href = '/SmartPlant_Care/controllers/CheckoutController.php?method=mp';
        showToast('Redirigiendo a MercadoPago…');
        setTimeout(() => {
            window.open('https://www.mercadopago.com.ar', '_blank');
        }, 800);
    }

    function checkoutPayPal() {
        const cart = getCart();
        if (!cart.length) return;
        // En producción: crear orden PayPal via API
        showToast('Redirigiendo a PayPal…');
        setTimeout(() => {
            window.open('https://www.paypal.com', '_blank');
        }, 800);
    }

    function buyNow() {
        addToCart();
    }

    // ═══════════ MODAL ═══════════
    function openModal(idx) {
        currentProduct = products[idx];
        selectedColor = 0;
        const m = document.getElementById('productModal');
        const badge = document.getElementById('modalBadge');
        badge.textContent = currentProduct.tag;
        badge.className = 'modal-badge store-badge-' + currentProduct.tagColor;

        document.getElementById('modalName').textContent = currentProduct.name;
        document.getElementById('modalDesc').textContent = currentProduct.longDesc;
        document.getElementById('modalPrice').textContent = formatPrice(currentProduct.price);

        const oldP = document.getElementById('modalOldPrice');
        oldP.textContent = currentProduct.oldPrice ? formatPrice(currentProduct.oldPrice) : '';
        oldP.style.display = currentProduct.oldPrice ? '' : 'none';

        document.getElementById('modalMainImg').src = currentProduct.image;
        document.getElementById('modalMainImg').alt = currentProduct.name;

        const thumbsC = document.getElementById('modalThumbs');
        thumbsC.innerHTML = '';
        currentProduct.gallery.forEach((src, i) => {
            const t = document.createElement('button');
            t.className = 'modal-thumb' + (i === 0 ? ' modal-thumb-active' : '');
            t.innerHTML = `<img src="${src}" alt="Vista ${i+1}">`;
            t.onclick = () => selectThumb(src, i);
            thumbsC.appendChild(t);
        });

        const colorsS = document.getElementById('modalColorsSection');
        const colorsC = document.getElementById('modalColors');
        colorsC.innerHTML = '';
        if (currentProduct.colors.length > 1) {
            colorsS.style.display = '';
            currentProduct.colors.forEach((c, i) => {
                const b = document.createElement('button');
                b.className = 'modal-color-btn' + (i === 0 ? ' modal-color-active' : '');
                b.style.background = c.hex;
                b.title = c.name;
                b.onclick = () => selectColor(i);
                colorsC.appendChild(b);
            });
            document.getElementById('modalColorName').textContent = currentProduct.colors[0].name;
        } else { colorsS.style.display = 'none'; }

        const specsC = document.getElementById('modalSpecs');
        specsC.innerHTML = '';
        Object.entries(currentProduct.specs).forEach(([k, v]) => {
            specsC.innerHTML += `<div class="modal-spec-row"><span class="modal-spec-key">${k}</span><span class="modal-spec-val">${v}</span></div>`;
        });

        const featC = document.getElementById('modalFeatures');
        featC.innerHTML = '';
        currentProduct.features.forEach(f => { featC.innerHTML += `<span class="store-feature-tag">✓ ${f}</span>`; });

        m.classList.add('modal-visible');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('productModal').classList.remove('modal-visible');
        document.body.style.overflow = '';
    }
    function selectThumb(src, i) {
        document.getElementById('modalMainImg').src = src;
        document.querySelectorAll('.modal-thumb').forEach((t, idx) => t.classList.toggle('modal-thumb-active', idx === i));
    }
    function selectColor(i) {
        selectedColor = i;
        const c = currentProduct.colors[i];
        document.getElementById('modalMainImg').src = c.image;
        document.getElementById('modalColorName').textContent = c.name;
        document.querySelectorAll('.modal-color-btn').forEach((b, idx) => b.classList.toggle('modal-color-active', idx === i));
        document.querySelectorAll('.modal-thumb').forEach(t => t.classList.remove('modal-thumb-active'));
    }
    function formatPrice(n) { return '$' + n.toLocaleString('es-AR'); }

    // ═══════════ UTILS ═══════════
    function showToast(msg) {
        const toast = document.getElementById('cartToast');
        document.getElementById('cartToastMsg').textContent = msg;
        toast.classList.add('store-toast-visible');
        setTimeout(() => toast.classList.remove('store-toast-visible'), 2800);
    }

    document.getElementById('productModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeModal(); const d = document.getElementById('cartDrawer'); if (d.classList.contains('cart-drawer-open')) toggleCart(); }
    });
    window.addEventListener('scroll', () => {
        const pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        document.getElementById('scrollProgress').style.width = pct + '%';
    });
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('active'); });
    }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });
    document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-blur, .stagger-children').forEach(el => obs.observe(el));

    // Init cart on load
    renderCart();
</script>
</body>
</html>
