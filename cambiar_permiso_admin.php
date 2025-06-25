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

// Verificar si se recibieron los datos necesarios
if (!isset($_POST['id_usuario']) || !isset($_POST['es_admin'])) {
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
$es_admin = intval($_POST['es_admin']);  // Convertir a número

// Verificar si el usuario intenta modificarse a sí mismo
if ($id_usuario == $id_usuario_actual) {
    echo json_encode(['error' => 'No puedes modificar tus propios permisos']);
    exit();
}

// Verificar si el usuario ya tiene el permiso que se intenta asignar
$query_check = "SELECT COUNT(*) as count FROM administradorxpulsera WHERE id_usuario = '$id_usuario' AND id_pulsera = '$id_pulsera'";
$result_check = $conexion->query($query_check);
$has_permission = $result_check->fetch_assoc()['count'] > 0;

// Si se está asignando administrador (es_admin = 1) y ya lo es, o si se está removiendo (es_admin = 0) y no lo es, no hacer nada
if (($es_admin === 1 && $has_permission) || ($es_admin === 0 && !$has_permission)) {
    echo json_encode(['success' => true]);
    exit();
}

// Verificar si el usuario intenta modificarse a sí mismo
if ($id_usuario == $id_usuario_actual) {
    echo json_encode(['error' => 'No puedes modificar tus propios permisos']);
    exit();
}

// Si se está asignando administrador, insertar en la tabla
if ($es_admin) {
    $query = "INSERT INTO administradorxpulsera (id_usuario, id_pulsera) VALUES ('$id_usuario', '$id_pulsera')";
} else {
    // Si se está removiendo administrador, eliminar de la tabla
    $query = "DELETE FROM administradorxpulsera WHERE id_usuario = '$id_usuario' AND id_pulsera = '$id_pulsera'";
}

if ($conexion->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Error al cambiar permisos: ' . $conexion->error]);
}

$conexion->close();
