// Función para obtener el estado del botón desde la base de datos
function actualizarEstado() {
    $.ajax({
        url: '../backend/get_estado.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                $('#estado-boton').text('Error: ' + response.error);
            } else {
                const estado = response.estado;
                $('#estado-boton').text(estado.charAt(0).toUpperCase() + estado.slice(1));
            }
        },
        error: function() {
            $('#estado-boton').text('Error al obtener el estado del botón.');
        }
    });
}

// Actualiza el estado cada 1 segundo
setInterval(actualizarEstado, 1000);

// Llama a la función al cargar la página
$(document).ready(function() {
    actualizarEstado();
});
