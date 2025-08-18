<?php
session_start();
require_once '../config/config.php';

// Obtener lista de usuarios y pulseras disponibles
try {
    $usuariosStmt = $pdo->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
    $usuarios = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);

    // Pulseras que aún no están asociadas a un equipo
    $pulserasStmt = $pdo->query("SELECT id, alias FROM pulseras WHERE id NOT IN (SELECT pulsera_id FROM equipos)");
    $pulseras = $pulserasStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_equipo'] ?? '';
    $responsable = $_POST['responsable'] ?? '';
    $pulsera_id = $_POST['pulsera_id'] ?? '';

    if ($nombre && $responsable && $pulsera_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO equipos (nombre_equipo, responsable_equipo, pulsera_id) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $responsable, $pulsera_id]);
            $_SESSION['message'] = 'Equipo registrado exitosamente';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error al registrar el equipo: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Todos los campos son obligatorios';
    }
    header('Location: registrar_equipo.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Equipo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="mb-4">Registrar Equipo</h1>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <form method="POST" class="p-4 border rounded bg-white">
        <div class="mb-3">
            <label for="nombre_equipo" class="form-label">Nombre del Equipo</label>
            <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" required>
        </div>
        <div class="mb-3">
            <label for="responsable" class="form-label">Responsable</label>
            <select id="responsable" name="responsable" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id']; ?>"><?= htmlspecialchars($usuario['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="pulsera_id" class="form-label">Pulsera</label>
            <select id="pulsera_id" name="pulsera_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($pulseras as $pulsera): ?>
                    <option value="<?= $pulsera['id']; ?>"><?= htmlspecialchars($pulsera['alias']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
    </form>
</div>
</body>
</html>
