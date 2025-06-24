<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debe estar logueado']);
    exit;
}

// Verificar que sea un administrador de la pulsera
$id_usuario_actual = $_SESSION['user_id'];
$id_pulsera = $_SESSION['selected_pulsera'];
$id_nuevo_admin = $_POST['id_nuevo_admin'];

// Verificar que el usuario actual es administrador
$stmt = $pdo->prepare("SELECT COUNT(*) as es_admin FROM administradorxpulsera WHERE id_usuario = ? AND id_pulsera = ?");
$stmt->execute([$id_usuario_actual, $id_pulsera]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ((int)$result['es_admin'] === 0) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para cambiar el administrador']);
    exit;
}

// Verificar que el nuevo administrador está vinculado a la pulsera
$stmt = $pdo->prepare("SELECT COUNT(*) as tiene_pulsera FROM usuariosxpulseras WHERE id_usuario = ? AND id_pulsera = ?");
$stmt->execute([$id_nuevo_admin, $id_pulsera]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ((int)$result['tiene_pulsera'] === 0) {
    echo json_encode(['success' => false, 'message' => 'El usuario no está vinculado a esta pulsera']);
    exit;
}

// Verificar que el nuevo administrador no sea el mismo que el actual
if ($id_usuario_actual == $id_nuevo_admin) {
    echo json_encode(['success' => false, 'message' => 'No puedes cederte la administración a ti mismo']);
    exit;
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Eliminar el administrador actual
    $stmt = $pdo->prepare("DELETE FROM administradorxpulsera WHERE id_pulsera = ?");
    $stmt->execute([$id_pulsera]);
    
    // Insertar el nuevo administrador
    $stmt = $pdo->prepare("INSERT INTO administradorxpulsera (id_usuario, id_pulsera, fecha_creacion) VALUES (?, ?, NOW())");
    $stmt->execute([$id_nuevo_admin, $id_pulsera]);
    
    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Administrador cambiado exitosamente'
    ]);
} catch(PDOException $e) {
    // Revertir transacción en caso de error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al cambiar el administrador: ' . $e->getMessage()]);
}
