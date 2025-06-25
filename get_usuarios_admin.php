<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar si se ha seleccionado una pulsera
if (!isset($_SESSION['selected_pulsera'])) {
    echo json_encode(['error' => 'No se ha seleccionado una pulsera']);
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "nes");

if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conexion->connect_error]);
    exit();
}

// Verificar si el usuario es administrador de esta pulsera
$id_usuario = $_SESSION['user_id'];
$id_pulsera = $_SESSION['selected_pulsera'];
$query_admin = "SELECT COUNT(*) as es_admin FROM administradorxpulsera WHERE id_usuario = '$id_usuario' AND id_pulsera = '$id_pulsera'";
$result_admin = $conexion->query($query_admin);
$es_admin = $result_admin->fetch_assoc();
$es_admin = $es_admin['es_admin'] > 0;

if (!$es_admin) {
    echo json_encode(['error' => 'No tienes permisos de administrador']);
    exit();
}

// Obtener lista de usuarios con sus permisos
$query = "
    SELECT 
        u.id,
        u.nombre,
        a.id_usuario IS NOT NULL as es_admin
    FROM usuariosxpulseras ux
    JOIN usuarios u ON ux.id_usuario = u.id
    LEFT JOIN administradorxpulsera a ON u.id = a.id_usuario AND a.id_pulsera = '$id_pulsera'
    WHERE ux.id_pulsera = '$id_pulsera'
    ORDER BY u.nombre
";

$result = $conexion->query($query);

if (!$result) {
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $conexion->error]);
    exit();
}
    
$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}
    

echo json_encode(['usuarios' => $usuarios]);

$conexion->close();
