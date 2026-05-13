<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesion para registrar una compra.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db         = Database::connect();
$usuario_id = (int) $_SESSION['usuario_id'];
$input      = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = $_POST;
}

$action = $_GET['action'] ?? $input['action'] ?? '';

crearTablaCompras($db);

if ($action !== 'create' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Accion no valida.']);
    exit;
}

$stmt = $db->prepare("SELECT nombre, email FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado.']);
    exit;
}

$metodo_pago = trim((string) ($input['metodo_pago'] ?? ''));
$estado      = trim((string) ($input['estado'] ?? 'pendiente'));
$moneda      = strtoupper(trim((string) ($input['moneda'] ?? 'ARS')));
$referencia  = trim((string) ($input['referencia_externa'] ?? ''));
$notas       = trim((string) ($input['notas'] ?? ''));
$cart        = $input['cart'] ?? [];

if ($metodo_pago === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Debes indicar el metodo de pago.']);
    exit;
}

$estados_validos = ['pendiente', 'aprobado', 'rechazado', 'cancelado', 'reembolsado'];
if (!in_array($estado, $estados_validos, true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Estado de compra invalido.']);
    exit;
}

if (!is_array($cart)) {
    http_response_code(422);
    echo json_encode(['error' => 'El carrito recibido no es valido.']);
    exit;
}

$items_normalizados = [];
$cantidad_items     = 0;
$monto_total        = 0.0;

foreach ($cart as $item) {
    $nombre   = trim((string) ($item['name'] ?? 'Producto'));
    $cantidad = max(1, (int) ($item['qty'] ?? 1));
    $precio   = round((float) ($item['price'] ?? 0), 2);
    $color    = trim((string) ($item['color'] ?? ''));

    $cantidad_items += $cantidad;
    $monto_total    += $precio * $cantidad;

    $items_normalizados[] = [
        'id'       => trim((string) ($item['id'] ?? '')),
        'nombre'   => $nombre,
        'color'    => $color,
        'cantidad' => $cantidad,
        'precio'   => $precio,
    ];
}

$monto_payload = isset($input['monto_total']) ? round((float) $input['monto_total'], 2) : null;
if ($monto_payload !== null && $monto_payload > 0) {
    $monto_total = $monto_payload;
}

if ($cantidad_items <= 0 || $monto_total <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'La compra debe incluir al menos un producto con monto valido.']);
    exit;
}

$fecha_pago_input = trim((string) ($input['fecha_pago'] ?? ''));
$fecha_pago_ts    = $fecha_pago_input !== '' ? strtotime($fecha_pago_input) : time();
$fecha_pago       = $fecha_pago_ts ? date('Y-m-d H:i:s', $fecha_pago_ts) : date('Y-m-d H:i:s');
$detalle_items    = json_encode($items_normalizados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$stmt = $db->prepare("
    INSERT INTO compras (
        usuario_id,
        usuario_nombre,
        usuario_email,
        metodo_pago,
        estado,
        moneda,
        monto_total,
        cantidad_items,
        referencia_externa,
        detalle_items,
        notas,
        fecha_pago
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssssdissss",
    $usuario_id,
    $usuario['nombre'],
    $usuario['email'],
    $metodo_pago,
    $estado,
    $moneda,
    $monto_total,
    $cantidad_items,
    $referencia,
    $detalle_items,
    $notas,
    $fecha_pago
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo guardar la compra.']);
    exit;
}

echo json_encode([
    'success'   => true,
    'compra_id' => $db->insert_id,
    'data'      => [
        'usuario'        => $usuario['nombre'],
        'metodo_pago'    => $metodo_pago,
        'estado'         => $estado,
        'monto_total'    => $monto_total,
        'cantidad_items' => $cantidad_items,
        'fecha_pago'     => $fecha_pago,
    ]
]);
exit;

function crearTablaCompras(mysqli $db): void {
    $db->query("
        CREATE TABLE IF NOT EXISTS compras (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            usuario_id INT(10) UNSIGNED NOT NULL,
            usuario_nombre VARCHAR(100) NOT NULL,
            usuario_email VARCHAR(150) NOT NULL,
            metodo_pago VARCHAR(50) NOT NULL,
            estado ENUM('pendiente', 'aprobado', 'rechazado', 'cancelado', 'reembolsado') NOT NULL DEFAULT 'pendiente',
            moneda CHAR(3) NOT NULL DEFAULT 'ARS',
            monto_total DECIMAL(10,2) NOT NULL,
            cantidad_items INT UNSIGNED NOT NULL DEFAULT 0,
            referencia_externa VARCHAR(120) DEFAULT NULL,
            detalle_items LONGTEXT DEFAULT NULL,
            notas TEXT DEFAULT NULL,
            fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_usuario_fecha (usuario_id, fecha_pago),
            KEY idx_estado (estado),
            KEY idx_referencia (referencia_externa),
            CONSTRAINT fk_compras_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}
