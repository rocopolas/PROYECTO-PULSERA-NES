<?php
session_start();
require_once '../config/config.php';

// Verifica si el usuario está autenticado y es administrador
if (!isset($_SESSION['username'])) {
    header("Location: ../index.html");
    exit();
}

// Obtener equipos y pulseras para el formulario
$equipos = $pdo->query("SELECT id, nombre_equipo FROM equipos ORDER BY nombre_equipo")->fetchAll(PDO::FETCH_ASSOC);
$pulseras = $pdo->query("SELECT id, alias FROM pulseras ORDER BY alias")->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipo_id'], $_POST['pulsera_id'])) {
    $equipo_id = $_POST['equipo_id'];
    $pulsera_id = $_POST['pulsera_id'];
    // Verificar si ya existe la relación
    $stmt = $pdo->prepare("SELECT id FROM pulserasxequipo WHERE equipo_id = ? AND pulsera_id = ?");
    $stmt->execute([$equipo_id, $pulsera_id]);
    if ($stmt->fetch()) {
        $mensaje = '<div class="alert alert-warning mt-3">La pulsera ya está asociada a este equipo.</div>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO pulserasxequipo (equipo_id, pulsera_id) VALUES (?, ?)");
        if ($stmt->execute([$equipo_id, $pulsera_id])) {
            $mensaje = '<div class="alert alert-success mt-3">Pulsera asociada correctamente al equipo.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger mt-3">Error al asociar la pulsera.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asociar Pulsera a Equipo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../colors.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../pulseras/dashboard.php">Panel de Administración</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../pulseras/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/register.php">Registrar Usuario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pulseras/register_pulsera.php">Registrar Pulsera</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registrar_equipo.php">Registrar Equipo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios_pulseras.php">Administrar Usuarios y Pulseras</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="asociar_pulsera_equipo.php">Asociar Pulsera a Equipo</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link text-light">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Asociar Pulsera a Equipo</h3>
        <form method="POST" class="row g-3 p-3 border rounded bg-light shadow-sm">
            <div class="col-md-6">
                <label for="equipo_id" class="form-label">Equipo</label>
                <select name="equipo_id" id="equipo_id" class="form-select" required>
                    <option value="">Seleccione un equipo</option>
                    <?php foreach ($equipos as $equipo): ?>
                        <option value="<?php echo $equipo['id']; ?>"><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="pulsera_id" class="form-label">Pulsera</label>
                <select name="pulsera_id" id="pulsera_id" class="form-select" required>
                    <option value="">Seleccione una pulsera</option>
                    <?php foreach ($pulseras as $pulsera): ?>
                        <option value="<?php echo $pulsera['id']; ?>"><?php echo htmlspecialchars($pulsera['alias']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Asociar Pulsera</button>
            </div>
        </form>
        <?php echo $mensaje; ?>
    </div>
</body>
</html>
