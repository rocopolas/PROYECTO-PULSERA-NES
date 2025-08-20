<?php
// Conexión a la base de datos
function getConnection() {
    $conexion = new mysqli("localhost", "root", "", "nes");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    return $conexion;
}
?>
