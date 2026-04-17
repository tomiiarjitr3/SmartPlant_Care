<?php
require_once __DIR__ . '/config/database.php';
$db = Database::connect();
$db->query("ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT NULL");
$db->query("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL");
echo "OK";
