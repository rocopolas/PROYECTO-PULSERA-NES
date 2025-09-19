<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de pulsera no proporcionado']);
    exit();
}

$pulsera_id = (int)$_GET['id'];

try {
    // Obtener usuarios que ya están invitados a la pulsera
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre
        FROM usuarios u
        JOIN pulserasxinvitados pi ON u.id = pi.invitado_id
        WHERE pi.pulsera_id = ?
        ORDER BY u.nombre
    ");
    $stmt->execute([$pulsera_id]);
    $assigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener usuarios que no están invitados a la pulsera
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre
        FROM usuarios u
        WHERE u.id NOT IN (
            SELECT invitado_id 
            FROM pulserasxinvitados 
            WHERE pulsera_id = ?
        )
        ORDER BY u.nombre
    ");
    $stmt->execute([$pulsera_id]);
    $unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'assigned' => $assigned,
        'unassigned' => $unassigned
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}