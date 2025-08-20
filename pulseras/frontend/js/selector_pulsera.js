function registerBracelet() {
    const idPulsera = document.getElementById('id_pulsera').value;
    const messageBox = document.getElementById('messageBox');
    
    if (!idPulsera) {
        messageBox.className = 'alert alert-danger d-block';
        messageBox.textContent = 'Por favor ingrese el ID de la pulsera';
        return;
    }

    $.ajax({
        url: '../backend/register_pulser_manual.php',
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
        url: '../backend/validar_codigo_invitacion.php',
        method: 'POST',
        data: { codigo: codigo },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#mensajeCodigo').removeClass('d-none alert-danger').addClass('alert-success');
                $('#mensajeCodigo').text(response.message);
                
                setTimeout(function() {
                    $.ajax({
                        url: '../backend/set_pulsera.php',
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
