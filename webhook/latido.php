<?php
// latido.php
require_once '../config/config.php'; // Debe definir $pdo = new PDO(...)

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

// IMPORTANTE: no imprimir warnings/notices en la salida JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Asegurar zona horaria en la sesión MySQL
try {
    $pdo->exec("SET time_zone = '-03:00'");
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error configurando zona horaria', 'detalle' => $e->getMessage()]);
    exit;
}

// Leer el cuerpo
$raw = file_get_contents('php://input');
$data = null;

// Si el Content-Type es JSON, parseamos; si no, intentamos como form-data
$ct = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
if (str_contains($ct, 'application/json')) {
    $data = json_decode($raw, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido', 'detalle' => json_last_error_msg(), 'raw' => substr($raw,0,200)]);
        exit;
    }
} else {
    // fallback a POST form (x-www-form-urlencoded o multipart)
    $data = $_POST;
}

// Validar parámetros requeridos
if (!isset($data['id_pulsera'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta id_pulsera']);
    exit;
}

$id = (int)$data['id_pulsera'];
$mv = null;
if (array_key_exists('battery_mv', $data) && $data['battery_mv'] !== '' && $data['battery_mv'] !== null) {
    $mv = (int)$data['battery_mv'];
    if ($mv <= 0) { $mv = null; }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO pulseras_heartbeat (pulsera_id, battery_mv)
        VALUES (:id, :mv)
    ");
    $stmt->execute([':id' => $id, ':mv' => $mv]);

    $newId = (int)$pdo->lastInsertId();

    // Leer el registro para devolver received_at exacto
    $stmt2 = $pdo->prepare("
        SELECT received_at, battery_mv
        FROM pulseras_heartbeat
        WHERE id = :rid
        LIMIT 1
    ");
    $stmt2->execute([':rid' => $newId]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);

    $lastSeenIso = null;
    if (!empty($row['received_at'])) {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $row['received_at'], new DateTimeZone('America/Argentina/Buenos_Aires'));
        $lastSeenIso = $dt ? $dt->format(DateTime::ATOM) : null; // ej: 2025-08-19T23:22:05-03:00
    }

    echo json_encode([
        'success'     => true,
        'message'     => 'Latido insertado correctamente',
        'id'          => $newId,
        'id_pulsera'  => $id,
        'battery_mv'  => is_null($row['battery_mv']) ? null : (int)$row['battery_mv'],
        'received_at' => $lastSeenIso
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en BD', 'detalle' => $e->getMessage()]);
}
