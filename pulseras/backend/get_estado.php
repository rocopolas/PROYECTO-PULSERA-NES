<?php
require_once '../../config/db_connection.php';
require_once '../../auth/session_checks.php';
require_once '../backend/pulsera_functions.php';

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');


// 1) Autenticación
checkAuth();

// 2) Pulsera seleccionada 
if (!isset($_SESSION['selected_pulsera'])) {
    echo json_encode(['error' => 'No hay pulsera seleccionada', 'estado-boton' => 'deshabilitado']);
    exit();
}

$id_pulsera = intval($_SESSION['selected_pulsera']);

// 3) Conexión a la BD
$conn = getConnection();
$conn->set_charset('utf8mb4');

// 4) Traer el último latido de esa pulsera
$sql = "
    SELECT received_at, battery_mv, latitude, longitude
    FROM pulseras_heartbeat
    WHERE pulsera_id = ?
    ORDER BY received_at DESC
    LIMIT 1
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Error preparando la consulta']);
    $conn->close();
    exit();
}

$stmt->bind_param('i', $id_pulsera);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $last_seen   = $row['received_at']; // 'Y-m-d H:i:s'
    $battery_mv  = is_null($row['battery_mv']) ? null : intval($row['battery_mv']);
    $latitude    = is_null($row['latitude']) ? null : (float)$row['latitude'];
    $longitude   = is_null($row['longitude']) ? null : (float)$row['longitude'];

    // 5) Calcular minutos transcurridos y estado
    $now = new DateTime('now');
    $seen = DateTime::createFromFormat('Y-m-d H:i:s', $last_seen);
    $minutes_since = ($seen) ? floor(($now->getTimestamp() - $seen->getTimestamp()) / 60) : null;

    $umbral_min = 7; // considerá online si recibió latido en los últimos 7 minutos
    $estado = ($minutes_since !== null && $minutes_since <= $umbral_min) ? 'online' : 'offline';

    echo json_encode([
        'success'        => true,
        'pulsera_id'     => $id_pulsera,
        'last_seen'      => $last_seen,
        'battery_mv'     => $battery_mv,
        'minutes_since'  => $minutes_since,
        'estado'         => $estado,
        'latitude'       => $latitude,
        'longitude'      => $longitude
    ]);
} else {
    // No hay latidos registrados para esa pulsera
    echo json_encode([
        'success'       => true,
        'pulsera_id'    => $id_pulsera,
        'last_seen'     => null,
        'battery_mv'    => null,
        'minutes_since' => null,
        'estado'        => 'sin datos',
        'latitude'      => null,
        'longitude'     => null
    ]);
}

$stmt->close();
$conn->close();
