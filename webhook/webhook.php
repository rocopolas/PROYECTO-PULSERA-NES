<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

if ($conexion->connect_error) {
    http_response_code(500);
    echo "Error de conexión: " . $conexion->connect_error;
    exit();
}

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['nuevo_estado'])) {
    http_response_code(400);
    echo "Faltan datos";
    exit();
}

$id = $conexion->real_escape_string($data['id']);
$nuevo_estado = strtolower($conexion->real_escape_string($data['nuevo_estado']));

// Determinar el valor de estado_boton basado en el nuevo estado
if ($nuevo_estado === "activo") {
    $estado_boton = 1; // Encendido
} elseif ($nuevo_estado === "suspendido") {
    $estado_boton = 0; // Apagado
} else {
    http_response_code(400);
    echo "Estado inválido. Use 'activo' o 'suspendido'.";
    exit();
}

// Actualizar el estado del botón en la base de datos
$sql = "UPDATE usuarios2 SET estado_boton = $estado_boton WHERE id = $id";

if ($conexion->query($sql) === TRUE) {
    echo "Estado actualizado correctamente a " . ($estado_boton ? "activo" : "suspendido") . ".";
} else {
    http_response_code(500);
    echo "Error al actualizar: " . $conexion->error;
}

$conexion->close();
?>
//
//
// prendido
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id\": 1, \"nuevo_estado\": \"suspendido\"}"
// activo
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id\": 1, \"nuevo_estado\": \"activo\"}"