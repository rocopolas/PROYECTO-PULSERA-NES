<?php
// Conexión a la base de datos
require_once '../config.php';

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_pulsera']) || !isset($data['nuevo_estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit();
}

try {
    $id_pulsera = $data['id_pulsera'];
    $nuevo_estado = strtolower($data['nuevo_estado']);

    // Verificar si la pulsera existe
    $stmt = $pdo->prepare("SELECT id FROM pulseras WHERE id = ?");
    $stmt->execute([$id_pulsera]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Pulsera no encontrada']);
        exit();
    }

    // Validar el estado
    if (!in_array($nuevo_estado, ['encendido', 'apagado'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Estado inválido. Use "encendido" o "apagado"']);
        exit();
    }

    // Registrar en la tabla historialpulseras
    $estado = $nuevo_estado === 'encendido' ? 'encendido' : 'apagado';
    
    $stmt = $pdo->prepare("INSERT INTO historialpulseras (id_pulsera, estado_pulsera) VALUES (?, ?)");
    $stmt->execute([$id_pulsera, $estado]);

    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente',
        'estado' => $nuevo_estado
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    error_log("Error en webhook: " . $e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
} catch(Exception $e) {
    http_response_code(500);
    error_log("Error general: " . $e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
}

?>

// Ejemplos de uso:
// Apagar:
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id_pulsera\": 1, \"nuevo_estado\": \"apagado\"}"
// Encender:
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id_pulsera\": 1, \"nuevo_estado\": \"encendido\"}"