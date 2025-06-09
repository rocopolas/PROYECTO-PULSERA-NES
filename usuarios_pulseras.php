<?php
session_start();
require_once 'config.php';

// Función para obtener todos los usuarios
function getUsuarios($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, email FROM usuarios ORDER BY nombre");
    return $stmt->fetchAll();
}

// Función para obtener todas las pulseras
function getPulseras($pdo) {
    $stmt = $pdo->query("SELECT id, alias, funcionamiento FROM pulseras ORDER BY alias");
    return $stmt->fetchAll();
}

// Función para obtener las pulseras de un usuario
function getPulserasUsuario($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT id_pulsera FROM usuariosxpulseras WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Procesar acciones (agregar o eliminar asignación)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $id_pulsera = $_POST['id_pulsera'];
    $accion = $_POST['accion'];
    
    try {
        if ($accion === 'agregar') {
            $stmt = $pdo->prepare("INSERT INTO usuariosxpulseras (id_usuario, id_pulsera) VALUES (?, ?)");
            $stmt->execute([$id_usuario, $id_pulsera]);
            $_SESSION['message'] = "Pulsera asignada exitosamente";
        } else if ($accion === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM usuariosxpulseras WHERE id_usuario = ? AND id_pulsera = ?");
            $stmt->execute([$id_usuario, $id_pulsera]);
            $_SESSION['message'] = "Pulsera desasignada exitosamente";
        }
        
        header("Location: usuarios_pulseras.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: usuarios_pulseras.php");
        exit();
    }
}

// Obtener datos
$usuarios = getUsuarios($pdo);
$pulseras = getPulseras($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios y Pulseras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Gestión de Usuarios y Pulseras</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Pulseras Asignadas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <?php
                                $pulseras_usuario = getPulserasUsuario($pdo, $usuario['id']);
                                foreach ($pulseras as $pulsera):
                                    if (in_array($pulsera['id'], $pulseras_usuario)):
                                        echo htmlspecialchars($pulsera['alias']) . " (" . htmlspecialchars($pulsera['funcionamiento']) . ")<br>";
                                    endif;
                                endforeach;
                                ?>
                            </td>
                            <td>
                                <div class="btn-group-vertical w-100">
                                    <?php foreach ($pulseras as $pulsera): ?>
                                        <?php if (in_array($pulsera['id'], $pulseras_usuario)): ?>
                                            <form method="POST" action="" class="m-1">
                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                                <input type="hidden" name="id_pulsera" value="<?php echo $pulsera['id']; ?>">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    Desasignar <?php echo htmlspecialchars($pulsera['alias']); ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="" class="m-1">
                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                                <input type="hidden" name="id_pulsera" value="<?php echo $pulsera['id']; ?>">
                                                <input type="hidden" name="accion" value="agregar">
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    Asignar <?php echo htmlspecialchars($pulsera['alias']); ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
