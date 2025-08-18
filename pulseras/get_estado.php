<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Verifica si se ha seleccionado una pulsera
if (!isset($_SESSION['selected_pulsera'])) {
    echo json_encode(['error' => 'No hay pulsera seleccionada', 'estado-boton' => 'deshabilitado']);
    exit();
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'nes');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

$id_pulsera = $_SESSION['selected_pulsera'];

$stmt->close();
$conn->close();
?>