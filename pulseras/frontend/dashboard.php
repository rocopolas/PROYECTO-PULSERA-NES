<?php
require_once '../../config/db_connection.php';
require_once '../../auth/session_checks.php';
require_once '../backend/pulsera_functions.php';

// Verificar autenticación y selección de equipo
checkAuth();
checkTeamSelection();

// Obtener conexión a la base de datos
$conexion = getConnection();

// Obtener información del equipo y la pulsera seleccionados
$id_equipo = $_SESSION['selected_equipo'];
$id_pulsera = $_SESSION['selected_pulsera'];
$pulsera = getPulseraInfo($conexion, $id_pulsera);

$nombre_equipo = $pulsera['nombre_equipo'] ?? 'Sin equipo asignado';

// Definir contenido adicional para el header
$additionalHeadContent = '<script src="js/dashboard.js"></script>';

// Iniciar el buffer de salida
ob_start();
?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Equipo: <?php echo htmlspecialchars($nombre_equipo); ?></h1>
            <div class="btn-group">
                <a href="selector_pulsera.php" class="btn btn-secondary">Cambiar Equipo</a>
                <a href="../../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
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

        // Obtener historial paginado
        $result_paginada = getHistorialPulsera($conexion, $id_pulsera, $offset, $eventos_por_pagina);

        // Total de registros para calcular el número de páginas
        $total_eventos = getTotalEventos($conexion, $id_pulsera);
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
        

<?php
    // Obtener el contenido del buffer y limpiarlo
    $content = ob_get_clean();
    
    // Incluir el layout con el contenido
    require_once 'templates/layout.php';
    
    // Cerrar la conexión
    $conexion->close();
?>