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

if (!isset($data['id']) || !isset($data['nuevo_estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit();
}

try {
    $id = $data['id'];
    $nuevo_estado = strtolower($data['nuevo_estado']);

    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit();
    }

    // Determinar el valor de estado_boton
    $estado_boton = ($nuevo_estado === "activo") ? 1 : 0;

    if (!in_array($nuevo_estado, ['activo', 'suspendido'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Estado inválido. Use "activo" o "suspendido"']);
        exit();
    }

    // Actualizar el estado del botón
    $stmt = $pdo->prepare("UPDATE users SET estado_boton = ? WHERE id = ?");
    $stmt->execute([$estado_boton, $id]);

    // Registrar en la tabla registro_botones
    $ip_usuario = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO registro_botones (id_usuario, ip_usuario, estado) VALUES (?, ?, ?)");
    $stmt->execute([$id, $ip_usuario, $estado_boton]);

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
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id\": 1, \"nuevo_estado\": \"suspendido\"}"
// Activar:
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id\": 1, \"nuevo_estado\": \"activo\"}"