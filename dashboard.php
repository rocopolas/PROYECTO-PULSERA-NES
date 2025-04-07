<?php
session_start(); // Inicia la sesión

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: index.html"); // Redirige al formulario de inicio de sesión si no está autenticado
    exit();
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'usuarios');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$mensaje = ""; // Variable para mostrar mensajes
$username = $_SESSION['username']; // Usuario autenticado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Procesar la acción recibida
        if ($action === 'encender') {
            // Actualizar estado_boton a 1 (activado) para el usuario autenticado
            $stmt = $conn->prepare("UPDATE usuarios2 SET estado_boton = 1 WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $mensaje = "El botón fue encendido.";
        } elseif ($action === 'apagar') {
            // Actualizar estado_boton a 0 (desactivado) para el usuario autenticado
            $stmt = $conn->prepare("UPDATE usuarios2 SET estado_boton = 0 WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $mensaje = "El botón fue apagado.";
        } else {
            $mensaje = "Acción desconocida.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/push.js/0.0.11/push.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }
        });
        

        // Función para obtener el estado del botón desde la base de datos
        function actualizarEstado() {
            $.ajax({
                url: 'get_estado.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        $('#estado-boton').text('Error: ' + response.error);
                    } else {
                        const estado = response.estado_boton == 1 ? 'Encendido' : 'Apagado';
                        $('#estado-boton').text('Estado del botón: ' + estado);
                        if(response.estado_boton == 1) {
                            console.log('El botón está encendido');
                            if (Notification.permission === "granted") {
                                Push.create("El botón está encendido", {
                                    body: "El botón ha sido encendido.",
                                    timeout: 4000,
                                    onClick: function () {
                                        window.focus();
                                        this.close();
                                    }
                                });
                            } else {
                                console.log('Permiso de notificación no concedido');
                            }
                        } 
                    }
                },
                error: function() {
                    $('#estado-boton').text('Error al obtener el estado del botón.');
                }
            });
        }

        // Actualiza el estado cada 2 segundos
        setInterval(actualizarEstado, 2000);

        // Llama a la función al cargar la página
        $(document).ready(function() {
            actualizarEstado();
        });
    </script>
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Aca veras toda la informacion de la pulsera</p>

    <p id="estado-boton">Cargando estado del botón...</p>

    <form method="POST" action="dashboard.php">
        <button type="submit" name="action" value="encender">Encender</button>
        <button type="submit" name="action" value="apagar">Apagar</button>
    </form>

    <a href="logout.php">Cerrar sesión</a>
</body>
</html>