<?php
session_start();
require_once '../config/config.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: ../index.html');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mensaje = '';

// Procesar formularios según la acción indicada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'asociar':
            if (!empty($_POST['equipo_id']) && !empty($_POST['pulsera_id'])) {
                $equipo_id = $_POST['equipo_id'];
                $pulsera_id = $_POST['pulsera_id'];
                $stmt = $pdo->prepare('SELECT id FROM pulserasxequipo WHERE equipo_id = ? AND pulsera_id = ?');
                $stmt->execute([$equipo_id, $pulsera_id]);
                if ($stmt->fetch()) {
                    $mensaje = '<div class="alert alert-warning mt-3">La pulsera ya está asociada a este equipo.</div>';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO pulserasxequipo (equipo_id, pulsera_id) VALUES (?, ?)');
                    if ($stmt->execute([$equipo_id, $pulsera_id])) {
                        $mensaje = '<div class="alert alert-success mt-3">Pulsera asociada correctamente.</div>';
                    } else {
                        $mensaje = '<div class="alert alert-danger mt-3">Error al asociar la pulsera.</div>';
                    }
                }
            }
            break;
        case 'alias':
            if (!empty($_POST['pulsera_id_alias']) && !empty($_POST['nuevo_alias'])) {
                $pulsera_id = $_POST['pulsera_id_alias'];
                $nuevo_alias = trim($_POST['nuevo_alias']);
                $stmt = $pdo->prepare('SELECT id FROM pulseras WHERE alias = ? AND id != ?');
                $stmt->execute([$nuevo_alias, $pulsera_id]);
                if ($stmt->fetch()) {
                    $mensaje = '<div class="alert alert-warning mt-3">El alias ya está en uso.</div>';
                } else {
                    $stmt = $pdo->prepare('UPDATE pulseras SET alias = ? WHERE id = ?');
                    if ($stmt->execute([$nuevo_alias, $pulsera_id])) {
                        $mensaje = '<div class="alert alert-success mt-3">Alias actualizado.</div>';
                    } else {
                        $mensaje = '<div class="alert alert-danger mt-3">Error al actualizar alias.</div>';
                    }
                }
            }
            break;
        case 'resp_multi_add':
            try {
                $equipo_id  = (int)($_POST['equipo_id_resps'] ?? 0);
                $usuario_id = (int)($_POST['usuario_id_resp_add'] ?? 0);
                if ($equipo_id <= 0 || $usuario_id <= 0) {
                    throw new Exception('Datos inválidos.');
                }

                // Evitar duplicados en equiposxrepresentantes
                $sql = "SELECT 1 FROM equiposxrepresentantes WHERE equipo_id = :e AND usuario_id = :u LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':e' => $equipo_id, ':u' => $usuario_id]);
                $yaExiste = $stmt->fetchColumn();

                if ($yaExiste) {
                    $mensaje = '<div class="alert alert-warning mt-3">Ese usuario ya figura como responsable de este equipo.</div>';
                } else {
                    $sql = "INSERT INTO equiposxrepresentantes (equipo_id, usuario_id) VALUES (:e, :u)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':e' => $equipo_id, ':u' => $usuario_id]);
                    $mensaje = '<div class="alert alert-success mt-3">Responsable agregado para el equipo.</div>';
                }
            } catch (Throwable $e) {
                $mensaje = '<div class="alert alert-danger mt-3">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            break;

        case 'resp_multi_del':
            try {
                $equipo_id  = (int)($_POST['equipo_id_resps'] ?? 0);
                $usuario_id = (int)($_POST['usuario_id_resp_del'] ?? 0);
                if ($equipo_id <= 0 || $usuario_id <= 0) {
                    throw new Exception('Datos inválidos.');
                }

                $sql = "DELETE FROM equiposxrepresentantes WHERE equipo_id = :e AND usuario_id = :u";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':e' => $equipo_id, ':u' => $usuario_id]);
                $af = $stmt->rowCount();
                if ($af > 0) {
                    $mensaje = '<div class="alert alert-success mt-3">Responsable quitado.</div>';
                } else {
                    $mensaje = '<div class="alert alert-warning mt-3">Ese usuario no era responsable de este equipo.</div>';
                }
            } catch (Throwable $e) {
                $mensaje = '<div class="alert alert-danger mt-3">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            break;


        case 'invitar':
            if (!empty($_POST['pulsera_id_inv']) && !empty($_POST['usuario_id_inv'])) {
                $pulsera_id = $_POST['pulsera_id_inv'];
                $usuario_id = $_POST['usuario_id_inv'];
                $stmt = $pdo->prepare('SELECT id FROM pulserasxinvitados WHERE pulsera_id = ? AND invitado_id = ?');
                $stmt->execute([$pulsera_id, $usuario_id]);
                if ($stmt->fetch()) {
                    $mensaje = '<div class="alert alert-warning mt-3">El usuario ya está invitado a esta pulsera.</div>';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO pulserasxinvitados (pulsera_id, invitado_id) VALUES (?, ?)');
                    if ($stmt->execute([$pulsera_id, $usuario_id])) {
                        $mensaje = '<div class="alert alert-success mt-3">Invitación agregada.</div>';
                    } else {
                        $mensaje = '<div class="alert alert-danger mt-3">Error al invitar usuario.</div>';
                    }
                }
            }
            break;
        case 'eliminar_invitado':
            if (!empty($_POST['invite_id'])) {
                $invite_id = $_POST['invite_id'];
                $stmt = $pdo->prepare('DELETE FROM pulserasxinvitados WHERE id = ?');
                if ($stmt->execute([$invite_id])) {
                    $mensaje = '<div class="alert alert-success mt-3">Invitación eliminada.</div>';
                } else {
                    $mensaje = '<div class="alert alert-danger mt-3">Error al eliminar invitación.</div>';
                }
            }
            break;
    }
}

// Obtener datos para los formularios
$equipos = $pdo->query('SELECT id, nombre_equipo FROM equipos ORDER BY nombre_equipo')->fetchAll(PDO::FETCH_ASSOC);
$pulseras = $pdo->query('SELECT id, alias FROM pulseras ORDER BY alias')->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query('SELECT id, nombre FROM usuarios ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
$invitaciones = $pdo->query('SELECT i.id, p.alias, u.nombre FROM pulserasxinvitados i JOIN pulseras p ON i.pulsera_id = p.id JOIN usuarios u ON i.invitado_id = u.id ORDER BY p.alias, u.nombre')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../colors.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../pulseras/dashboard.php">Panel de Administración</a>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-4">Portal de Empresa</h3>
        <?php echo $mensaje; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Asociar Pulsera a Equipo</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="asociar">
                            <div class="mb-3">
                                <label for="equipo_id" class="form-label">Equipo</label>
                                <select id="equipo_id" name="equipo_id" class="form-select" required>
                                    <option value="">Seleccione un equipo</option>
                                    <?php foreach ($equipos as $eq): ?>
                                        <option value="<?php echo $eq['id']; ?>"><?php echo htmlspecialchars($eq['nombre_equipo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="pulsera_id" class="form-label">Pulsera</label>
                                <select id="pulsera_id" name="pulsera_id" class="form-select" required>
                                    <option value="">Seleccione una pulsera</option>
                                    <?php foreach ($pulseras as $pul): ?>
                                        <option value="<?php echo $pul['id']; ?>"><?php echo htmlspecialchars($pul['alias']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Asociar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Editar Alias de Pulsera</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="alias">
                            <div class="mb-3">
                                <label for="pulsera_id_alias" class="form-label">Pulsera</label>
                                <select id="pulsera_id_alias" name="pulsera_id_alias" class="form-select" required>
                                    <option value="">Seleccione una pulsera</option>
                                    <?php foreach ($pulseras as $pul): ?>
                                        <option value="<?php echo $pul['id']; ?>"><?php echo htmlspecialchars($pul['alias']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nuevo_alias" class="form-label">Nuevo Alias</label>
                                <input type="text" id="nuevo_alias" name="nuevo_alias" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar Alias</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
            <div class="card h-100">
            <div class="card-body">
            <h5 class="card-title">Gestionar Responsables de Equipo</h5>

            <!-- Formulario para agregar -->
            <form method="POST" class="mb-3">
            <input type="hidden" name="action" value="resp_multi_add">
            <div class="mb-3">
            <label for="equipo_id_resps" class="form-label">Equipo</label>
            <select id="equipo_id_resps" name="equipo_id_resps" class="form-select" required>
            <option value="">Seleccione un equipo</option>
            <?php foreach ($equipos as $eq): ?>
            <option value="<?php echo $eq['id']; ?>">
            <?php echo htmlspecialchars($eq['nombre_equipo']); ?>
            </option>
            <?php endforeach; ?>
            </select>
            </div>
            <div class="mb-3">
            <label for="usuario_id_resp_add" class="form-label">Nuevo Responsable</label>
            <select id="usuario_id_resp_add" name="usuario_id_resp_add" class="form-select" required>
            <option value="">Seleccione un usuario</option>
            <?php foreach ($usuarios as $us): ?>
            <option value="<?php echo $us['id']; ?>">
            <?php echo htmlspecialchars($us['nombre']); ?>
            </option>
            <?php endforeach; ?>
            </select>
            </div>
            <button type="submit" class="btn btn-primary">Agregar Responsable</button>
            </form>

            <!-- Selector para ver responsables actuales del equipo elegido (GET) -->
            <form method="GET" class="mb-3">
            <div class="mb-3">
            <label for="equipo_id_resps" class="form-label">Ver responsables de:</label>
            <select id="equipo_id_resps" name="equipo_id_resps" class="form-select" required>
            <option value="">Seleccione un equipo</option>
            <?php foreach ($equipos as $eq): ?>
            <option value="<?php echo $eq['id']; ?>"
            <?php echo (!empty($_GET['equipo_id_resps']) && (int)$_GET['equipo_id_resps'] === (int)$eq['id']) ? 'selected' : '' ; ?>>
            <?php echo htmlspecialchars($eq['nombre_equipo']); ?>
            </option>
            <?php endforeach; ?>
            </select>
            </div>
            <button type="submit" class="btn btn-outline-secondary">Mostrar</button>
            </form>

            <!-- Listado de responsables actuales usando equiposxrepresentantes -->
            <?php
            $responsables = [];
            $equipo_id_sel = null;

            if (!empty($_GET['equipo_id_resps'])) {
                $equipo_id_sel = (int)$_GET['equipo_id_resps'];
                $sql = "SELECT u.id, u.nombre
                        FROM equiposxrepresentantes exr
                        JOIN usuarios u ON exr.usuario_id = u.id
                        WHERE exr.equipo_id = ?
                        ORDER BY u.nombre";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$equipo_id_sel]);
                $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            ?>

            <?php if (!empty($equipo_id_sel) && !empty($responsables)): ?>
            <table class="table table-sm">
            <thead>
            <tr>
            <th>Responsable</th>
            <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($responsables as $resp): ?>
            <tr>
            <td><?php echo htmlspecialchars($resp['nombre']); ?></td>
            <td>
            <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="resp_multi_del">
            <input type="hidden" name="equipo_id_resps" value="<?php echo $equipo_id_sel; ?>">
            <input type="hidden" name="usuario_id_resp_del" value="<?php echo $resp['id']; ?>">
            <button type="submit" class="btn btn-sm btn-danger">Quitar</button>
            </form>
            </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
            <?php elseif (!empty($equipo_id_sel)): ?>
            <p class="text-muted">Este equipo no tiene responsables cargados.</p>
            <?php else: ?>
            <p class="text-muted">Seleccione un equipo y presione “Mostrar”.</p>
            <?php endif; ?>
            </div>
            </div>
            </div>




            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Invitados de Pulsera</h5>
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="invitar">
                            <div class="mb-3">
                                <label for="pulsera_id_inv" class="form-label">Pulsera</label>
                                <select id="pulsera_id_inv" name="pulsera_id_inv" class="form-select" required>
                                    <option value="">Seleccione una pulsera</option>
                                    <?php foreach ($pulseras as $pul): ?>
                                        <option value="<?php echo $pul['id']; ?>"><?php echo htmlspecialchars($pul['alias']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="usuario_id_inv" class="form-label">Usuario</label>
                                <select id="usuario_id_inv" name="usuario_id_inv" class="form-select" required>
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach ($usuarios as $us): ?>
                                        <option value="<?php echo $us['id']; ?>"><?php echo htmlspecialchars($us['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Invitar Usuario</button>
                        </form>
                        <?php if (!empty($invitaciones)): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Pulsera</th>
                                        <th>Usuario</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invitaciones as $inv): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inv['alias']); ?></td>
                                            <td><?php echo htmlspecialchars($inv['nombre']); ?></td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="eliminar_invitado">
                                                    <input type="hidden" name="invite_id" value="<?php echo $inv['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No hay invitaciones registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
