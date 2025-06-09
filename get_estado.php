<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Verifica si se ha seleccionado una pulsera
if (!isset($_SESSION['selected_pulsera'])) {
    echo json_encode(['error' => 'No hay pulsera seleccionada']);
    exit();
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'nes');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

$id_pulsera = $_SESSION['selected_pulsera'];

// Consulta el último estado de la pulsera
$stmt = $conn->prepare("SELECT estado_pulsera FROM historialpulseras WHERE id_pulsera = ? ORDER BY timestamp DESC LIMIT 1");
$stmt->bind_param("i", $id_pulsera);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['estado' => $row['estado_pulsera']]);
} else {
    echo json_encode(['error' => 'No se encontró estado para la pulsera']);
}

$stmt->close();
$conn->close();
?>