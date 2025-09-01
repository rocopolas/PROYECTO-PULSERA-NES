<?php
// Conexión a la base de datos
function getConnection() {
    $conexion = new mysqli("192.168.101.93", "ag01", "StudentAG01_2025", "ag01", 3306);
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    return $conexion;
}
?>
