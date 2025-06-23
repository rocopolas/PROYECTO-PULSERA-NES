<?php
session_start();
require_once 'config.php';

try {
    // Borrar todas las filas de la tabla administradorxpulsera
    $stmt = $pdo->prepare("DELETE FROM administradorxpulsera");
    $stmt->execute();

    echo "Tabla administradorxpulsera limpiada exitosamente!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
