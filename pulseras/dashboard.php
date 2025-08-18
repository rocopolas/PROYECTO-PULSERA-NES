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

// Verificar si el usuario es administrador de esta pulsera
$id_usuario = $_SESSION['user_id'];
$query_admin = "SELECT COUNT(*) as es_admin FROM administradorxpulsera WHERE id_usuario = '$id_usuario' AND id_pulsera = '$id_pulsera'";
$result_admin = $conexion->query($query_admin);
$es_admin = $result_admin->fetch_assoc();
$es_admin = $es_admin['es_admin'] > 0;

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
        // Función para cargar usuarios y sus permisos
        function cargarUsuarios() {
            $.ajax({
                url: '../admin/get_usuarios_admin.php',
                method: 'GET',
                data: { id_pulsera: <?php echo $id_pulsera; ?> },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        $('#adminError').removeClass('d-none').text(response.error);
                        return;
                    }
                    
                    let html = '';
                    response.usuarios.forEach(usuario => {
                        // Use the boolean value directly from the server response
                        const esAdmin = usuario.es_admin;
                        html += `
                            <tr>
                                <td>${usuario.nombre}</td>
                                <td>
                                    <span class="badge ${esAdmin ? 'bg-success' : 'bg-secondary'}" data-user-id="${usuario.id}">
                                        ${esAdmin ? 'Sí' : 'No'}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm ${esAdmin ? 'btn-danger' : 'btn-success'} ${usuario.id == <?php echo $id_usuario; ?> ? 'disabled' : ''} btn-admin" 
                                                data-user-id="${usuario.id}">
                                            ${esAdmin ? 'Quitar Administrador' : 'Dar Administrador'}
                                        </button>
                                        <button class="btn btn-sm btn-danger ${usuario.id == <?php echo $id_usuario; ?> ? 'disabled' : ''} btn-delete" data-user-id="${usuario.id}">
                                            Eliminar acceso
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#usuariosList').html(html);

                    // Añadir eventos de clic después de cargar los usuarios
                    $('.btn-admin').off('click').on('click', function() {
                        const userId = $(this).data('user-id');
                        const esAdmin = $(this).hasClass('btn-danger');
                        // Usamos el valor opuesto de esAdmin ya que estamos cambiando el estado
                        cambiarPermiso(userId, !esAdmin);
                    });

                    $('.btn-delete').off('click').on('click', function() {
                        const userId = $(this).data('user-id');
                        eliminarPermiso(userId);
                    });
                },
                error: function() {
                    $('#adminError').removeClass('d-none').text('Error al cargar los usuarios');
                }
            });
        }

        // Función para cambiar permisos de administrador
        function cambiarPermiso(userId, esAdmin) {
            console.log('Cambiando permiso para usuario:', userId, 'esAdmin:', esAdmin);
            
            // Deshabilitar el botón mientras se procesa
            const button = $(`button[data-user-id="${userId}"]`);
            button.prop('disabled', true);
            
            $.ajax({
                url: '../admin/cambiar_permiso_admin.php',
                method: 'POST',
                data: { 
                    id_usuario: userId, 
                    id_pulsera: <?php echo $id_pulsera; ?>,
                    es_admin: esAdmin ? 1 : 0  // 1 para dar administrador, 0 para quitar
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                    if (response.error) {
                        alert('Error: ' + response.error);
                        button.prop('disabled', false);
                        return;
                    }
                    
                    // Recargar los usuarios para actualizar el estado
                    cargarUsuarios();
                    
                    // Habilitar el botón
                    button.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    alert('Error al cambiar permisos: ' + error);
                    button.prop('disabled', false);
                }
            });
        }

        // Función para eliminar acceso
        function eliminarPermiso(userId) {
            if (confirm('¿Estás seguro de que quieres eliminar el acceso de este usuario?')) {
                    $.ajax({
                        url: '../admin/eliminar_acceso.php',
                    method: 'POST',
                    data: { 
                        id_usuario: userId, 
                        id_pulsera: <?php echo $id_pulsera; ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            alert('Error: ' + response.error);
                        } else {
                            cargarUsuarios();
                        }
                    },
                    error: function() {
                        alert('Error al eliminar acceso');
                    }
                });
            }
        }

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

        // Función para generar código de invitación
        function generarCodigo() {
            $.ajax({
                url: '../admin/generar_codigo_invitacion.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#codigoGenerado').removeClass('d-none');
                        $('#codigoTexto').text(response.codigo);
                        $('#mensajeError').addClass('d-none');
                    } else {
                        $('#mensajeError').removeClass('d-none');
                        $('#mensajeError').text(response.message);
                    }
                },
                error: function() {
                    $('#mensajeError').removeClass('d-none');
                    $('#mensajeError').text('Error al generar el código de invitación');
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
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card mb-2">
                    <div class="card-body">
                        <h5 class="card-title">Estado Actual</h5>
                        <p id="estado-boton" class="text-center text-primary">Cargando estado del botón...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Información del Equipo</h5>
                        <p><strong>Equipo:</strong> <?php echo htmlspecialchars($nombre_equipo); ?></p>
                        <p><strong>Pulsera:</strong> <?php echo htmlspecialchars($pulsera['alias']); ?></p>
                        <p><strong>Funcionamiento:</strong> <?php echo htmlspecialchars($pulsera['funcionamiento']); ?></p>
                        <?php if ($es_admin): ?>
                            <p class="text-primary"><strong>Eres administrador de este equipo</strong></p>
                            <div class="d-flex flex-column flex-md-row mt-3">
                                <button type="button" class="btn btn-secondary mb-2 mb-md-0 mx-2" data-bs-toggle="modal" data-bs-target="#generarCodigoModal">
                                    Generar Código de Invitación
                                </button>
                                <button type="button" class="btn btn-primary ms-2 ms-md-0 mx-2" data-bs-toggle="modal" data-bs-target="#adminModal">
                                    Administrar Usuarios
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para administrar usuarios -->
        <div class="modal fade" id="adminModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Administrar Usuarios del Equipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="usuariosAdmin" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Es Administrador</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usuariosList">
                                    <!-- Los usuarios se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        <div id="adminError" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para generar código de invitación -->
        <div class="modal fade" id="generarCodigoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generar Código de Invitación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="codigoGenerado" class="alert alert-info d-none">
                            <p><strong>Código de invitación generado:</strong> <span id="codigoTexto"></span></p>
                            <p>Comparte este código con la persona que deseas que vea la pulsera. El código se usará una sola vez y luego se deshabilitará.</p>
                        </div>
                        <div id="mensajeError" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="generarCodigo()">Generar Código</button>
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
        $query_paginada = "SELECT timestamp, id_pulsera 
                            FROM historialxpulseras 
                            WHERE id_pulsera = '$id_pulsera' 
                            ORDER BY timestamp DESC 
                            LIMIT $eventos_por_pagina OFFSET $offset";
        $result_paginada = $conexion->query($query_paginada);

        // Total de registros para calcular el número de páginas
        $query_total = "SELECT COUNT(*) as total FROM historialxpulseras WHERE id_pulsera = '$id_pulsera'";
        echo "ID de la pulsera: $id_pulsera\n";

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