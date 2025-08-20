<?php
// Conexión a la base de datos
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);


if (!isset($data['id_pulsera'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el dato requerido: id_pulsera']);
    exit();
}


$id_pulsera = $data['id_pulsera'];
$timestamp = date('Y-m-d H:i:s'); // Timestamp actual del servidor

try {
    // Insertar en historialxpulseras (solo id_pulsera y timestamp)
    $stmt = $pdo->prepare("INSERT INTO historialxpulseras (id_pulsera, timestamp) VALUES (?, ?)");
    $stmt->execute([$id_pulsera, $timestamp]);

    echo json_encode([
        'success' => true,
        'message' => 'Registro insertado correctamente',
        'id_pulsera' => $id_pulsera,
        'timestamp' => $timestamp
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    error_log("Error en webhook: " . $e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor', 'detalle' => $e->getMessage()]); // Mostrar detalle temporalmente
}

?>

// Ejemplo de uso:
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d '{"id_pulsera": 1}'