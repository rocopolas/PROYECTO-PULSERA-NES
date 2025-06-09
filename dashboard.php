<?php
session_start(); // Inicia la sesión

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Verifica si se ha seleccionado una pulsera
if (!isset($_SESSION['selected_pulsera'])) {
    header("Location: selector_pulsera.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "nes");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener información de la pulsera seleccionada
$id_pulsera = $_SESSION['selected_pulsera'];
$query_pulsera = "SELECT alias, funcionamiento FROM pulseras WHERE id = '$id_pulsera'";
$result_pulsera = $conexion->query($query_pulsera);
$pulsera = $result_pulsera->fetch_assoc();

// Obtener el historial de la pulsera
$query_historial = "SELECT timestamp, estado_pulsera 
                    FROM historialpulseras 
                    WHERE id_pulsera = '$id_pulsera' 
                    ORDER BY timestamp DESC";

$result_historial = $conexion->query($query_historial);
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Pulsera: <?php echo htmlspecialchars($pulsera['alias']); ?></h1>
            <div class="btn-group">
                <a href="selector_pulsera.php" class="btn btn-secondary">Cambiar Pulsera</a>
                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Estado Actual</h5>
                        <p id="estado-boton" class="text-center text-primary">Cargando estado del botón...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Información de la Pulsera</h5>
                        <p><strong>Alias:</strong> <?php echo htmlspecialchars($pulsera['alias']); ?></p>
                        <p><strong>Funcionamiento:</strong> <?php echo htmlspecialchars($pulsera['funcionamiento']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-4">Historial de eventos</h3>
        <?php
        // Paginación
        $eventos_por_pagina = 10;
        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $offset = ($pagina_actual - 1) * $eventos_por_pagina;

        // Consulta con límite y desplazamiento
        $query_paginada = "SELECT timestamp, estado_pulsera 
                            FROM historialpulseras 
                            WHERE id_pulsera = '$id_pulsera' 
                            ORDER BY timestamp DESC 
                            LIMIT $eventos_por_pagina OFFSET $offset";
        $result_paginada = $conexion->query($query_paginada);

        // Total de registros para calcular el número de páginas
        $query_total = "SELECT COUNT(*) as total FROM historialpulseras WHERE id_pulsera = '$id_pulsera'";
        $result_total = $conexion->query($query_total);
        $total_eventos = $result_total->fetch_assoc()['total'];
        $total_paginas = ceil($total_eventos / $eventos_por_pagina);
        ?>

        <?php if ($result_historial && $result_historial->num_rows > 0): ?>
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_historial->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado_pulsera']); ?></td>
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