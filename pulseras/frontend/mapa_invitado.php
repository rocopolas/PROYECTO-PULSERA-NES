<?php
require_once '../../config/db_connection.php';
require_once '../../auth/session_checks.php';
require_once '../backend/pulsera_functions.php';

checkAuth();
if (!isset($_SESSION['selected_pulsera'])) {
    header('Location: selector_pulsera.php');
    exit();
}

$conexion = getConnection();
$id_pulsera = $_SESSION['selected_pulsera'];
$pulsera = getPulseraInfo($conexion, $id_pulsera);

$pageTitle = 'Mapa de Pulsera';
$additionalHeadContent = '<script src="js/mapa_invitado.js"></script>';

ob_start();
?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Pulsera: <?php echo htmlspecialchars($pulsera['alias']); ?></h1>
        <div class="btn-group">
            <a href="selector_pulsera.php" class="btn btn-secondary">Volver</a>
            <a href="../../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Mapa</h5>
            <div class="d-flex justify-content-center">
                <iframe id="map" width="100%" height="350" style="border: 1px solid black"></iframe>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
