<?php
require_once __DIR__ . '/../config/database.php';

class Planta {
    public static function all() {
        $db = Database::connect();
        return $db->query("SELECT * FROM plantas");
    }
}