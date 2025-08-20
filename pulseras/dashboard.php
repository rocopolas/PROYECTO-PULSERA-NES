<?php
session_start(); // Inicia la sesión

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.html");
    exit();
}

// Verifica si se ha seleccionado un equipo
if (!isset($_SESSION['selected_equipo'])) {
    header("Location: selector_pulsera.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "nes");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener información del equipo y la pulsera seleccionados
$id_equipo = $_SESSION['selected_equipo'];
$id_pulsera = $_SESSION['selected_pulsera'];
$query_pulsera = "SELECT p.id AS id_pulsera, p.alias, p.funcionamiento, e.nombre_equipo 
                  FROM pulseras p 
                  LEFT JOIN pulserasxequipo px ON p.id = px.pulsera_id
                  LEFT JOIN equipos e ON px.equipo_id = e.id 
                  WHERE p.id = '$id_pulsera'";
$result_pulsera = $conexion->query($query_pulsera);
$pulsera = $result_pulsera->fetch_assoc();

$nombre_equipo = $pulsera['nombre_equipo'] ?? 'Sin equipo asignado';



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
    <link href="../colors.css" rel="stylesheet">
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
                        const estado = response.estado;
                        // El estado puede ser 'activado' o 'desactivado'. Si es 'activado', el botón tendrá la clase 'btn-success' y el texto "Activado". De lo contrario, tendrá la clase 'btn-danger' y el texto "Desactivado".
                        // charAt(0) devuelve el primer carácter de la cadena y slice(1) devuelve la cadena sin el primer carácter. De esta forma, estamos obteniendo la primera letra de la cadena y concatenando el resto de la cadena sin la primera letra. Esto es para que el estado 'activado' se muestre como 'Activado' y el estado 'desactivado' se muestre como 'Desactivado'.
                        $('#estado-boton').text(estado.charAt(0).toUpperCase() + estado.slice(1));
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
            $('#adminModal').on('shown.bs.modal', function () {
                cargarUsuarios();
            });
        });
    </script>
</head>
<body class="">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Equipo: <?php echo htmlspecialchars($nombre_equipo); ?></h1>
            <div class="btn-group">
                <a href="selector_pulsera.php" class="btn btn-secondary">Cambiar Equipo</a>
                <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="row row-cols-md-2 mb-4">
            <div class="col mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Estado Actual</h5>
                        <div class="d-flex flex-column align-items-center justify-content-center h-75">
                            <p id="estado-boton" class="text-center text-primary mb-0">Cargando estado del botón...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Información del Equipo</h5>
                        <div class="d-flex flex-column justify-content-center h-75">
                            <p><strong>Equipo:</strong> <?php echo htmlspecialchars($nombre_equipo); ?></p>
                            <p><strong>Pulsera:</strong> <?php echo htmlspecialchars($pulsera['alias']); ?></p>
                            <p><strong>Funcionamiento:</strong> <?php echo htmlspecialchars($pulsera['funcionamiento']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Mapa</h5>
                        <div class="d-flex justify-content-center">
                            <iframe width="100%" height="350" src="https://www.openstreetmap.org/export/embed.html?bbox=-58.58048558235169%2C-34.5898699395%2C-58.57044339179993%2C-34.58386370936635&amp;layer=mapnik" style="border: 1px solid black"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p>

        <div class="row">
            <div class="col">
            <h3 class="mt-4">Historial de eventos</h3>
            </div>
        </div>

        <?php
        // Paginación
        $eventos_por_pagina = 10;
        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $offset = ($pagina_actual - 1) * $eventos_por_pagina;

        // Consulta con límite y desplazamiento
        $query_paginada = "SELECT timestamp, id_pulsera 
                            FROM historialxpulseras 
                            WHERE id_pulsera = '$id_pulsera' 
                            ORDER BY timestamp DESC 
                            LIMIT $eventos_por_pagina OFFSET $offset";
        $result_paginada = $conexion->query($query_paginada);

        // Total de registros para calcular el número de páginas
        $query_total = "SELECT COUNT(*) as total FROM historialxpulseras WHERE id_pulsera = '$id_pulsera'";

        $result_total = $conexion->query($query_total);
        $total_eventos = $result_total->fetch_assoc()['total'];
        $total_paginas = ceil($total_eventos / $eventos_por_pagina);
        ?>

        <?php if ($result_paginada && $result_paginada->num_rows > 0): ?>
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_paginada->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td>presionado</td>
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
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conexion->close();
?>