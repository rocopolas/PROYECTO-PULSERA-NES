<?php
// Verificaciones de autenticación y sesión
function checkAuth() {
    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ../index.php");
        exit();
    }
}

function checkTeamSelection() {
    if (!isset($_SESSION['selected_equipo'])) {
        header("Location: selector_pulsera.php");
        exit();
    }
}
?>
