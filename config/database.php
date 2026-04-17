<?php
// ═══════════════════════════════════════════════════════════════
//  database.php — SmartPlant CARE
//  Maneja la conexión a MySQL. Usá variables de entorno o
//  un archivo .env en producción para no exponer credenciales.
// ═══════════════════════════════════════════════════════════════

class Database {
    private static ?mysqli $instance = null;

    // ── Configuración ──────────────────────────────────────────
    private static string $host   = 'localhost';
    private static string $user   = 'root';
    private static string $pass   = '';              // Cambiá en producción
    private static string $db     = 'smartplant_care';
    private static string $charset = 'utf8mb4';

    // ── Singleton: una sola conexión por request ───────────────
    public static function connect(): mysqli {
        if (self::$instance === null) {
            $conn = new mysqli(self::$host, self::$user, self::$pass, self::$db);

            if ($conn->connect_error) {
                // En producción: loggear el error, no mostrarlo
                error_log("DB Connection error: " . $conn->connect_error);
                http_response_code(500);
                die(json_encode(['error' => 'No se pudo conectar a la base de datos.']));
            }

            $conn->set_charset(self::$charset);
            self::$instance = $conn;
        }

        return self::$instance;
    }

    // ── Cierre explícito (opcional) ────────────────────────────
    public static function close(): void {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }
}