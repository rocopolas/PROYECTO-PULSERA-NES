<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre  = htmlspecialchars($_POST['nombre']);
    $email   = htmlspecialchars($_POST['email']);
    $asunto  = htmlspecialchars($_POST['asunto']);
    $mensaje = htmlspecialchars($_POST['mensaje']);

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'xxxxxxxx@murialdo.edu.ar'; // tu correo
        $mail->Password = 'XXXXXXXXXXXXXXXXXX'; // tu clave de aplicación de Gmail

        // Remitente y destinatario
        $mail->setFrom($email, $nombre); // El remitente será el usuario
        $mail->addAddress($email); // Tu correo receptor

        // Contenido del correo
        $mail->Subject = $asunto;
        $mail->isHTML(true);
        $mail->Body = "
            <h3>Nuevo mensaje desde el formulario</h3>
            <p><strong>Nombre:</strong> {$nombre}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Mensaje:</strong><br>{$mensaje}</p>
        ";

        // Enviar
        $mail->send();
        echo "✅ El mensaje fue enviado correctamente.";

    } catch (Exception $e) {
        echo "❌ Error al enviar el mensaje: {$mail->ErrorInfo}";
    }
} else {
    echo "Acceso no válido.";
}