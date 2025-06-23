<?php
session_start();
require_once 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debe estar logueado para usar un código de invitación']);
    exit;
}

// Obtener el código del POST
$codigo = $_POST['codigo'];
$id_usuario = $_SESSION['user_id'];

try {
    // Verificar si el código es válido y no ha sido usado
    $stmt = $pdo->prepare("SELECT id_pulsera FROM codigos_invitacion WHERE codigo = ? AND fecha_uso IS NULL");
    $stmt->execute([$codigo]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Código de invitación inválido o ya usado']);
        exit;
    }

    // Verificar si el usuario ya tiene esta pulsera
    $stmt = $pdo->prepare("SELECT COUNT(*) as tiene_pulsera FROM usuariosxpulseras WHERE id_usuario = ? AND id_pulsera = ?");
    $stmt->execute([$id_usuario, $result['id_pulsera']]);
    $tiene_pulsera = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ((int)$tiene_pulsera['tiene_pulsera'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya tienes asignada esta pulsera']);
        exit;
    }

    // Registrar la pulsera para el usuario
    $stmt = $pdo->prepare("INSERT INTO usuariosxpulseras (id_usuario, id_pulsera, fecha_asignacion) VALUES (?, ?, NOW())");
    $stmt->execute([$id_usuario, $result['id_pulsera']]);

    // Marcar el código como usado
    $stmt = $pdo->prepare("UPDATE codigos_invitacion SET fecha_uso = NOW(), id_usuario_uso = ? WHERE codigo = ?");
    $stmt->execute([$id_usuario, $codigo]);

    echo json_encode([
        'success' => true,
        'message' => 'Pulsera asignada exitosamente usando el código de invitación'
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
