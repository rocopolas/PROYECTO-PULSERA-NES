<?php
session_start();
require_once '../config/config.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $equipo_id = isset($_GET['equipo_id']) ? (int)$_GET['equipo_id'] : 0;

    try {
        // Get unassigned bracelets
        $stmt = $pdo->prepare("
            SELECT p.id, p.alias 
            FROM pulseras p 
            LEFT JOIN pulserasxequipo pe ON p.id = pe.pulsera_id 
            WHERE pe.id IS NULL
            ORDER BY p.alias
        ");
        $stmt->execute();
        $unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get bracelets assigned to the selected team
        $assigned = [];
        if ($equipo_id > 0) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.alias 
                FROM pulseras p 
                JOIN pulserasxequipo pe ON p.id = pe.pulsera_id 
                WHERE pe.equipo_id = ?
                ORDER BY p.alias
            ");
            $stmt->execute([$equipo_id]);
            $assigned = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'success' => true,
            'unassigned' => $unassigned,
            'assigned' => $assigned
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener los datos'
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['equipo_id']) || !isset($data['assigned'])) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $equipo_id = (int)$data['equipo_id'];
    $assigned_ids = array_map('intval', $data['assigned']);

    try {
        $pdo->beginTransaction();

        // Remove all current assignments for this team
        $stmt = $pdo->prepare('DELETE FROM pulserasxequipo WHERE equipo_id = ?');
        $stmt->execute([$equipo_id]);

        // Add new assignments
        if (!empty($assigned_ids)) {
            $stmt = $pdo->prepare('INSERT INTO pulserasxequipo (equipo_id, pulsera_id) VALUES (?, ?)');
            foreach ($assigned_ids as $pulsera_id) {
                $stmt->execute([$equipo_id, $pulsera_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Error al guardar los cambios']);
    }
    exit;
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
