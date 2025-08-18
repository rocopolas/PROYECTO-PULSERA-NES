<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['user_id'];
    $id_pulsera = $_POST['id_pulsera'];
    
    try {
        // Verificar si la pulsera existe
        $stmt = $pdo->prepare("SELECT * FROM pulseras WHERE id = ?");
        $stmt->execute([$id_pulsera]);
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'La pulsera no existe']);
            exit;
        }

        // Verificar si el usuario ya tiene la pulsera
        $stmt = $pdo->prepare("SELECT * FROM usuariosxpulseras WHERE id_usuario = ? AND id_pulsera = ?");
        $stmt->execute([$id_usuario, $id_pulsera]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya tienes asignada esta pulsera']);
            exit;
        }

        // Verificar si ya existe un administrador para esta pulsera
        $stmt_admin = $pdo->prepare("SELECT COUNT(*) as tiene_admin FROM administradorxpulsera WHERE id_pulsera = ?");
        $stmt_admin->execute([$id_pulsera]);
        $result_admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
        
        if ((int)$result_admin['tiene_admin'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Esta pulsera ya tiene un administrador asignado']);
            exit;
        }

        // Registrar la pulsera
        $stmt = $pdo->prepare("INSERT INTO usuariosxpulseras (id_usuario, id_pulsera, fecha_asignacion) VALUES (?, ?, NOW())");
        $stmt->execute([$id_usuario, $id_pulsera]);

        // Como no había administrador, este usuario se convierte automáticamente en administrador
        $stmt_admin = $pdo->prepare("INSERT INTO administradorxpulsera (id_usuario, id_pulsera, fecha_creacion) VALUES (?, ?, NOW())");
        $stmt_admin->execute([$id_usuario, $id_pulsera]);
        
        echo json_encode(['success' => true, 'message' => 'Pulsera asignada exitosamente. Eres el administrador de esta pulsera.']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
