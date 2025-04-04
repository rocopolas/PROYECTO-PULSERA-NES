<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'usuarios');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

$username = $_SESSION['username']; // Usuario autenticado

// Consulta el estado del botón para el usuario autenticado
$stmt = $conn->prepare("SELECT estado_boton FROM usuarios2 WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['estado_boton' => $row['estado_boton']]);
} else {
    echo json_encode(['error' => 'Usuario no encontrado']);
}

$stmt->close();
$conn->close();
?>