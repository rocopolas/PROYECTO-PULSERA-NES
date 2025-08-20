<?php
require_once '../../config/db_connection.php';
require_once '../../auth/session_checks.php';
require_once '../backend/pulsera_functions.php';

// Verificar autenticación
checkAuth();

// Obtener conexión a la base de datos
$conexion = getConnection();

$pageTitle = 'Seleccionar Equipo';
$additionalHeadContent = '<script src="js/selector_pulsera.js"></script>';

ob_start();

// Obtener todos los equipos donde el usuario es responsable
$equipos = getEquiposUsuario($conexion, $_SESSION['user_id']);

// Para cada equipo, obtener sus pulseras
$equiposTemp = [];
foreach ($equipos as $equipo) {
    $equipo['pulseras'] = getPulserasEquipo($conexion, $equipo['id']);
    $equiposTemp[] = $equipo;
$equipos = $equiposTemp; // Reemplazar el array original con el procesado
}
// Si se selecciona una pulsera, redirigir al dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seleccion'])) {
    $parts = explode(':', $_POST['seleccion']);
    if (count($parts) === 2) {
        $_SESSION['selected_equipo'] = $parts[0];
        $_SESSION['selected_pulsera'] = $parts[1];
        header("Location: dashboard.php");
        exit();
    }
}
?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../colors.css" rel="stylesheet">
</head>
<body class="">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="../../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="POST" class="p-4 border rounded bg-white shadow-sm">
                    <div class="mb-4">
                        <h3 class="mb-3">Equipos disponibles</h3>
                        <?php 
                        foreach ($equipos as $equipo):
                        ?>
                            <div class="equipo-container mb-4">
                                <h5 class="mt-3 mb-2">
                                    <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                                </h5>
                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                    <?php if (!empty($equipo['pulseras'])): ?>
                                        <?php foreach ($equipo['pulseras'] as $pulsera): ?>
                                            <div class="col">
                                                <button type="submit"
                                                        name="seleccion"
                                                        value="<?php echo $equipo['id'] . ':' . $pulsera['id_pulsera']; ?>"
                                                        class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-between p-3 rounded">
                                                    <div class="fw-bold mb-2"><?php echo htmlspecialchars($pulsera['alias']); ?></div>
                                                    <div class=""><?php echo htmlspecialchars($pulsera['funcionamiento']); ?></div>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col">
                                            <div class="alert alert-warning w-100">No hay pulseras asociadas a este equipo.</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                      </div>
                    <!-- Botones para registrar pulsera o usar código de invitación 
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerBraceletModal">
                            Registrar Pulsera
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#codigoInvitacionModal">
                            Usar Código de Invitación
                        </button>
                    </div>
                    -->
                </form>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
