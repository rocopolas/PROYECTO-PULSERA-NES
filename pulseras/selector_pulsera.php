<?php
session_start();
require_once '../config/config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.html");
    exit();
}

// Obtener los equipos y pulseras del usuario
$stmt = $pdo->prepare("
    SELECT e.id AS equipo_id, e.nombre_equipo, p.id AS id_pulsera, p.alias, p.funcionamiento
    FROM equipos e
    JOIN pulseras p ON p.equipo_id = e.id
    WHERE e.responsable_equipo = ?
    ORDER BY e.nombre_equipo, p.alias");
$stmt->execute([$_SESSION['user_id']]);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$equipos = [];
foreach ($filas as $fila) {
    $eid = $fila['equipo_id'];
    if (!isset($equipos[$eid])) {
        $equipos[$eid] = [
            'nombre_equipo' => $fila['nombre_equipo'],
            'pulseras' => []
        ];
    }
    $equipos[$eid]['pulseras'][] = [
        'id_pulsera' => $fila['id_pulsera'],
        'alias' => $fila['alias'],
        'funcionamiento' => $fila['funcionamiento']
    ];
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Equipo</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function registerBracelet() {
            const idPulsera = document.getElementById('id_pulsera').value;
            const messageBox = document.getElementById('messageBox');
            
            if (!idPulsera) {
                messageBox.className = 'alert alert-danger d-block';
                messageBox.textContent = 'Por favor ingrese el ID de la pulsera';
                return;
            }

            $.ajax({
                url: 'register_pulser_manual.php',
                method: 'POST',
                data: { id_pulsera: idPulsera },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        messageBox.className = 'alert alert-success d-block';
                        messageBox.textContent = response.message;
                        document.getElementById('id_pulsera').value = '';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        messageBox.className = 'alert alert-danger d-block';
                        messageBox.textContent = response.message;
                    }
                },
                error: function() {
                    messageBox.className = 'alert alert-danger d-block';
                    messageBox.textContent = 'Error al registrar la pulsera. Por favor intente nuevamente.';
                }
            });
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../colors.css" rel="stylesheet">
</head>
<body class="">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="POST" class="p-4 border rounded bg-white shadow-sm">
                      <div class="mb-4">
                          <h3 class="mb-3">Equipos disponibles</h3>
                          <?php foreach ($equipos as $id_equipo => $equipo): ?>
                              <h5 class="mt-3 mb-2"><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></h5>
                              <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-3">
                                  <?php foreach ($equipo['pulseras'] as $pulsera): ?>
                                      <div class="col">
                                          <button type="submit"
                                                  name="seleccion"
                                                  value="<?php echo $id_equipo . ':' . $pulsera['id_pulsera']; ?>"
                                                  class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-between p-3 rounded">
                                              <div class="fw-bold mb-2"><?php echo htmlspecialchars($pulsera['alias']); ?></div>
                                              <div class=""><?php echo htmlspecialchars($pulsera['funcionamiento']); ?></div>
                                          </button>
                                      </div>
                                  <?php endforeach; ?>
                              </div>
                          <?php endforeach; ?>
                      </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerBraceletModal">
                            Registrar Pulsera
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#codigoInvitacionModal">
                            Usar Código de Invitación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para registrar pulsera -->
    <div class="modal fade" id="registerBraceletModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pulsera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_pulsera" class="form-label">ID de la Pulsera</label>
                        <input type="text" class="form-control" id="id_pulsera" required>
                    </div>
                    <div id="messageBox" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="registerBracelet()">Registrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para usar código de invitación -->
    <div class="modal fade" id="codigoInvitacionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Usar Código de Invitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código de Invitación:</label>
                        <input type="text" class="form-control" id="codigo" required>
                        <div id="mensajeCodigo" class="alert alert-danger d-none mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="usarCodigo()">Usar Código</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function registerBracelet() {
            const idPulsera = document.getElementById('id_pulsera').value;
            const messageBox = document.getElementById('messageBox');
            
            if (!idPulsera) {
                messageBox.className = 'alert alert-danger d-block';
                messageBox.textContent = 'Por favor ingrese el ID de la pulsera';
                return;
            }

            $.ajax({
                url: 'register_pulser_manual.php',
                method: 'POST',
                data: { id_pulsera: idPulsera },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        messageBox.className = 'alert alert-success d-block';
                        messageBox.textContent = response.message;
                        document.getElementById('id_pulsera').value = '';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        messageBox.className = 'alert alert-danger d-block';
                        messageBox.textContent = response.message;
                    }
                },
                error: function() {
                    messageBox.className = 'alert alert-danger d-block';
                    messageBox.textContent = 'Error al registrar la pulsera. Por favor intente nuevamente.';
                }
            });
        }
        function usarCodigo() {
            const codigo = $('#codigo').val();
            if (!codigo) {
                $('#mensajeCodigo').removeClass('d-none');
                $('#mensajeCodigo').text('Por favor ingrese un código de invitación');
                return;
            }

            $.ajax({
                url: 'validar_codigo_invitacion.php',
                method: 'POST',
                data: { codigo: codigo },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Mostrar mensaje de éxito
                        $('#mensajeCodigo').removeClass('d-none');
                        $('#mensajeCodigo').removeClass('alert-danger');
                        $('#mensajeCodigo').addClass('alert-success');
                        $('#mensajeCodigo').text(response.message);
                        
                        // Guardar la pulsera en la sesión y redirigir después de un breve delay
                        setTimeout(function() {
                            $.ajax({
                                url: 'set_pulsera.php',
                                method: 'POST',
                                data: { id_pulsera: response.id_pulsera },
                                success: function() {
                                    window.location.href = 'dashboard.php';
                                }
                            });
                        }, 1000);
                    } else {
                        $('#mensajeCodigo').removeClass('d-none');
                        $('#mensajeCodigo').text(response.message);
                    }
                },
                error: function() {
                    $('#mensajeCodigo').removeClass('d-none');
                    $('#mensajeCodigo').text('Error al validar el código de invitación');
                }
            });
        }
    </script>
</body>
</html>
