<?php
// Funciones para obtener información de pulseras y equipos
function getPulseraInfo($conexion, $id_pulsera) {
    $query_pulsera = "SELECT p.id AS id_pulsera, p.alias, p.funcionamiento, e.nombre_equipo 
                      FROM pulseras p 
                      LEFT JOIN pulserasxequipo px ON p.id = px.pulsera_id
                      LEFT JOIN equipos e ON px.equipo_id = e.id 
                      WHERE p.id = ?";
    
    $stmt = $conexion->prepare($query_pulsera);
    $stmt->bind_param("s", $id_pulsera);
    $stmt->execute();
    $result = $stmt->get_result();
    $pulsera = $result->fetch_assoc();
    $stmt->close();
    
    return $pulsera;
}

function getHistorialPulsera($conexion, $id_pulsera, $offset, $limit) {
    $query = "SELECT timestamp, id_pulsera 
              FROM historialxpulseras 
              WHERE id_pulsera = ? 
              ORDER BY timestamp DESC 
              LIMIT ? OFFSET ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sii", $id_pulsera, $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

function getTotalEventos($conexion, $id_pulsera) {
    $query = "SELECT COUNT(*) as total FROM historialxpulseras WHERE id_pulsera = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $id_pulsera);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function getEquiposUsuario($conexion, $user_id) {
    $query = "SELECT id, nombre_equipo FROM equipos WHERE responsable_equipo = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $equipos;
}

function getPulserasEquipo($conexion, $equipo_id) {
    $query = "SELECT p.id AS id_pulsera, p.alias, p.funcionamiento
              FROM pulseras p
              INNER JOIN pulserasxequipo px ON p.id = px.pulsera_id
              WHERE px.equipo_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $equipo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pulseras = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $pulseras;
}
?>
