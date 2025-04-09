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
    $estado_boton = 1;
} elseif ($nuevo_estado === "suspendido") {
    $estado_boton = 0;
} else {
    http_response_code(400);
    echo "Estado inválido. Use 'activo' o 'suspendido'.";
    exit(); 
}

// Actualizar el estado del botón en la base de datos
$sql = "UPDATE usuarios2 SET estado_boton = $estado_boton WHERE id = $id";

if ($conexion->query($sql) === TRUE) {
    echo "Estado actualizado correctamente a " . ($estado_boton ? "activo" : "suspendido") . ".";

    // Actualizar el estado del botón en la base de datos
$sql = "UPDATE usuarios2 SET estado_boton = $estado_boton WHERE id = $id";

if ($conexion->query($sql) === TRUE) {
    echo "Estado actualizado correctamente a " . ($estado_boton ? "activo" : "suspendido") . ".";

    // Obtener la IP del usuario
    $ip_usuario = $_SERVER['REMOTE_ADDR'];

    // Registrar en la tabla registro_botones
    $stmt = $conexion->prepare("INSERT INTO registro_botones (id_usuario, ip_usuario, estado) VALUES (?, ?, ?)");
    $estado_texto = $estado_boton ? 1 : 0;
    $stmt->bind_param("iss", $id, $ip_usuario, $estado_texto);

    if ($stmt->execute()) {
        // Registro exitoso (opcional: mostrar mensaje de log)
    } else {
        error_log("Error al registrar evento: " . $stmt->error);
    }

    $stmt->close();

} else {
    http_response_code(500);
    echo "Error al actualizar: " . $conexion->error;
}
}

$conexion->close();
?>

//
//
// apagado
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id\": 1, \"nuevo_estado\": \"suspendido\"}"
// activo
// curl -X POST http://localhost/webhook/webhook.php -H "Content-Type: application/json" -d "{\"id\": 1, \"nuevo_estado\": \"activo\"}"