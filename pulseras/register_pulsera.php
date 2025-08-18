<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_emision = $_POST['fecha_emision'];
    $version = $_POST['version'];
    $funcionamiento = $_POST['funcionamiento'];
    $alias = $_POST['alias'];
    
    try {
        // Primero verificar si el alias ya existe
        $stmt = $pdo->prepare("SELECT id FROM pulseras WHERE alias = ?");
        $stmt->execute([$alias]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "El alias ya está en uso";
            header("Location: register_pulsera.php");
            exit();
        }

        // Insertar la pulsera
        $stmt = $pdo->prepare("INSERT INTO pulseras (fecha_emision, version, funcionamiento, alias) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fecha_emision, $version, $funcionamiento, $alias]);
        
        $_SESSION['message'] = "Pulsera registrada exitosamente!";
        header("Location: register_pulsera.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error al registrar la pulsera: " . $e->getMessage();
        header("Location: register_pulsera.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Pulsera</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Registro de Pulsera</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['message']; ?></div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="fecha_emision" class="form-label">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" required>
                            </div>
                            <div class="mb-3">
                                <label for="version" class="form-label">Versión</label>
                                <input type="text" class="form-control" id="version" name="version" required>
                            </div>
                            <div class="mb-3">
                                <label for="funcionamiento" class="form-label">Estado de Funcionamiento</label>
                                <select class="form-control" id="funcionamiento" name="funcionamiento" required>
                                    <option value="">Seleccione...</option>
                                    <option value="funcionando">Funcionando</option>
                                    <option value="averiada">Averiada</option>
                                    <option value="mantenimiento">En Mantenimiento</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="alias" class="form-label">Alias</label>
                                <input type="text" class="form-control" id="alias" name="alias" required>                            </div>
                            <button type="submit" class="btn btn-primary w-100">Registrar Pulsera</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
