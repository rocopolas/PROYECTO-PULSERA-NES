<?php
session_start();
require_once '../config/config.php';

// Verificar si es un administrador de la pulsera
$id_usuario = $_SESSION['user_id'];
$id_pulsera = $_SESSION['selected_pulsera'];

// Verificar si es administrador
$stmt = $pdo->prepare("SELECT COUNT(*) as es_admin FROM administradorxpulsera WHERE id_usuario = ? AND id_pulsera = ?");
$stmt->execute([$id_usuario, $id_pulsera]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ((int)$result['es_admin'] === 0) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para generar códigos de invitación']);
    exit;
}

try {
    // Generar un código único de 32 caracteres alfanuméricos
    $codigo = bin2hex(random_bytes(16));
    
    // Insertar el código en la base de datos
    $stmt = $pdo->prepare("INSERT INTO codigos_invitacion (id_pulsera, codigo) VALUES (?, ?)");
    $stmt->execute([$id_pulsera, $codigo]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Código de invitación generado exitosamente',
        'codigo' => $codigo
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
