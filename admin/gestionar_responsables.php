<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// Manejo de solicitud GET para obtener responsables
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID de equipo no proporcionado']);
        exit();
    }

    $equipo_id = (int)$_GET['id'];

    try {
        // Obtener usuarios que ya son responsables del equipo
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre
            FROM usuarios u
            JOIN equiposxrepresentantes er ON u.id = er.usuario_id
            WHERE er.equipo_id = ?
            ORDER BY u.nombre
        ");
        $stmt->execute([$equipo_id]);
        $assigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener usuarios que no son responsables del equipo
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre
            FROM usuarios u
            WHERE u.id NOT IN (
                SELECT usuario_id 
                FROM equiposxrepresentantes 
                WHERE equipo_id = ?
            )
            ORDER BY u.nombre
        ");
        $stmt->execute([$equipo_id]);
        $unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'assigned' => $assigned,
            'unassigned' => $unassigned
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Manejo de solicitud POST para actualizar responsables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['equipo_id']) || !isset($data['assigned'])) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit();
    }

    $equipo_id = (int)$data['equipo_id'];
    $assigned_users = array_map('intval', $data['assigned']);

    try {
        $pdo->beginTransaction();

        // Eliminar todos los responsables actuales del equipo
        $stmt = $pdo->prepare("DELETE FROM equiposxrepresentantes WHERE equipo_id = ?");
        $stmt->execute([$equipo_id]);

        // Insertar los nuevos responsables
        if (!empty($assigned_users)) {
            $stmt = $pdo->prepare("INSERT INTO equiposxrepresentantes (equipo_id, usuario_id) VALUES (?, ?)");
            foreach ($assigned_users as $user_id) {
                $stmt->execute([$equipo_id, $user_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}