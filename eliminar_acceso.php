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

// Verificar si se recibió el id del usuario
if (!isset($_POST['id_usuario'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "nes");

if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conexion->connect_error]);
    exit();
}

// Verificar si el usuario es administrador de esta pulsera
$id_usuario_actual = $_SESSION['user_id'];
$id_pulsera = $_SESSION['selected_pulsera'];
$query_admin = "SELECT COUNT(*) as es_admin FROM administradorxpulsera WHERE id_usuario = '$id_usuario_actual' AND id_pulsera = '$id_pulsera'";
$result_admin = $conexion->query($query_admin);
$es_admin = $result_admin->fetch_assoc();
$es_admin = $es_admin['es_admin'] > 0;

if (!$es_admin) {
    echo json_encode(['error' => 'No tienes permisos de administrador']);
    exit();
}

// Obtener datos del POST
$id_usuario = $conexion->real_escape_string($_POST['id_usuario']);

// Verificar si el usuario intenta eliminarse a sí mismo
if ($id_usuario == $id_usuario_actual) {
    echo json_encode(['error' => 'No puedes eliminarte a ti mismo']);
    exit();
}

// Eliminar el acceso del usuario
$query = "DELETE FROM usuariosxpulseras WHERE id_usuario = '$id_usuario' AND id_pulsera = '$id_pulsera'";

if ($conexion->query($query)) {
    // También eliminar permisos de administrador si existen
    $query_admin = "DELETE FROM administradorxpulsera WHERE id_usuario = '$id_usuario' AND id_pulsera = '$id_pulsera'";
    $conexion->query($query_admin);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Error al eliminar acceso: ' . $conexion->error]);
}

$conexion->close();
