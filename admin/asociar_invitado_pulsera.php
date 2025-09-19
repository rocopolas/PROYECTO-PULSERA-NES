<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['pulsera_id']) || !isset($data['assigned'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

$pulsera_id = (int)$data['pulsera_id'];
$assigned_users = array_map('intval', $data['assigned']);

try {
    $pdo->beginTransaction();

    // Eliminar todas las invitaciones actuales para esta pulsera
    $stmt = $pdo->prepare("DELETE FROM pulserasxinvitados WHERE pulsera_id = ?");
    $stmt->execute([$pulsera_id]);

    // Insertar las nuevas invitaciones
    if (!empty($assigned_users)) {
        $stmt = $pdo->prepare("INSERT INTO pulserasxinvitados (pulsera_id, invitado_id) VALUES (?, ?)");
        foreach ($assigned_users as $user_id) {
            $stmt->execute([$pulsera_id, $user_id]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}