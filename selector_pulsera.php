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
                url: 'register_bracelet.php',
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
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
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
                    <button type="button" class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#registerBraceletModal">
                        Registrar Pulsera
                    </button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="registerBracelet()">Registrar</button>
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
    </script>
    
</body>
</html>
