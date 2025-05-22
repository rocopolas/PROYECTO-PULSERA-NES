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
$query_usuario = "SELECT id FROM users WHERE username = '$username'";
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p class="text-center">Aca veras toda la informacion de la pulsera</p>
        <h3 class="mt-4">Historial de eventos</h3>
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
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
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
            <nav>
                <ul class="pagination justify-content-center">
                    <?php
                    // Determinar el rango de páginas a mostrar
                    $rango_paginas = 10;
                    $inicio_rango = floor(($pagina_actual - 1) / $rango_paginas) * $rango_paginas + 1;
                    $fin_rango = min($inicio_rango + $rango_paginas - 1, $total_paginas);
                    ?>

                    <?php if ($inicio_rango > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $inicio_rango - 1; ?>">Anterior</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = $inicio_rango; $i <= $fin_rango; $i++): ?>
                        <li class="page-item <?php if ($i == $pagina_actual) echo 'active'; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($fin_rango < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $fin_rango + 1; ?>">Siguiente</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php else: ?>
            <p class="text-center text-danger">No hay registros disponibles para este usuario.</p>
        <?php endif; ?>
        <br>
        <div class="text-center mb-3">
            <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conexion->close();
?>