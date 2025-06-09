<?php
session_start();
require_once 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Obtener las pulseras del usuario
$stmt = $pdo->prepare("
    SELECT p.id, p.alias, p.funcionamiento 
    FROM pulseras p 
    JOIN usuariosxpulseras up ON p.id = up.id_pulsera 
    WHERE up.id_usuario = ?
    ORDER BY p.alias");
$stmt->execute([$_SESSION['user_id']]);
$pulseras = $stmt->fetchAll();

// Si se selecciona una pulsera, redirigir al dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pulsera'])) {
    $_SESSION['selected_pulsera'] = $_POST['id_pulsera'];
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Pulsera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <h2 class="text-center mb-4">Selecciona una pulsera para ver su dashboard</h2>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="POST" class="p-4 border rounded bg-white shadow-sm">
                    <div class="mb-3">
                        <label for="pulsera" class="form-label">Pulseras disponibles:</label>
                        <select class="form-select" id="pulsera" name="id_pulsera" required>
                            <option value="">Seleccione una pulsera...</option>
                            <?php foreach ($pulseras as $pulsera): ?>
                                <option value="<?php echo $pulsera['id']; ?>">
                                    <?php echo htmlspecialchars($pulsera['alias']); ?> (<?php echo htmlspecialchars($pulsera['funcionamiento']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ver Dashboard</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
