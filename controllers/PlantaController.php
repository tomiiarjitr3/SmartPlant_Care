<?php
require_once __DIR__ . '/../models/Planta.php';

class PlantaController {
    public static function index() {
        $plantas = Planta::all();
        include __DIR__ . '/../views/Login.php';
    }
}