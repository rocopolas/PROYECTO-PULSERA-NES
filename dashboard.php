<?php
session_start(); // Inicia la sesión

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: index.html"); // Redirige al formulario de inicio de sesión si no está autenticado
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener el id del usuario logueado (puedes ajustar esto según tu estructura de sesión)
$username = $_SESSION['username'];
$query_usuario = "SELECT id FROM usuarios2 WHERE username = '$username'";
$result_usuario = $conexion->query($query_usuario);

if ($result_usuario->num_rows > 0) {
    $row_usuario = $result_usuario->fetch_assoc();
    $id_usuario = $row_usuario['id'];

    // Obtener los registros del usuario desde la tabla registro_botones
    $query_registros = "SELECT id_evento, timestamp, ip_usuario, estado 
                        FROM registro_botones 
                        WHERE id_usuario = $id_usuario 
                        ORDER BY timestamp DESC";
    $result_registros = $conexion->query($query_registros);
} else {
    $id_usuario = null;
    $result_registros = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                        } 
                    }
                },
                error: function() {
                    $('#estado-boton').text('Error al obtener el estado del botón.');
                }
            });
        }

        // Actualiza el estado cada 1 segundos
        setInterval(actualizarEstado, 1000);

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
    <h3>Historial de eventos</h3>
    <?php
    // Paginación
    $eventos_por_pagina = 10;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $offset = ($pagina_actual - 1) * $eventos_por_pagina;

    // Consulta con límite y desplazamiento
    $query_paginada = "SELECT id_evento, timestamp, ip_usuario, estado 
                       FROM registro_botones 
                       WHERE id_usuario = $id_usuario 
                       ORDER BY timestamp DESC 
                       LIMIT $eventos_por_pagina OFFSET $offset";
    $result_paginada = $conexion->query($query_paginada);

    // Total de registros para calcular el número de páginas
    $query_total = "SELECT COUNT(*) as total FROM registro_botones WHERE id_usuario = $id_usuario";
    $result_total = $conexion->query($query_total);
    $total_eventos = $result_total->fetch_assoc()['total'];
    $total_paginas = ceil($total_eventos / $eventos_por_pagina);
    ?>

    <?php if ($result_paginada && $result_paginada->num_rows > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID Evento</th>
                    <th>Fecha y Hora</th>
                    <th>IP Usuario</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_paginada->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_evento']); ?></td>
                        <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($row['ip_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['estado']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Navegación de páginas -->
        <div>
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>" <?php if ($i == $pagina_actual) echo 'style="font-weight: bold;"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>No hay registros disponibles para este usuario.</p>
    <?php endif; ?>
    <br>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>

<?php
$conexion->close();
?>