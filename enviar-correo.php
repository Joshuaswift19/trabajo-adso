<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function enviarCorreo($destinatario, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'josue091206@gmail.com'; // Tu correo de Gmail
        $mail->Password = 'kowx wpkx zird sedw'; // Contraseña de aplicación generada en Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('josue091206@gmail.com', 'Trabajo ADSO'); // Remitente
        $mail->addAddress($destinatario); // Destinatario
        $mail->Subject = $asunto; // Asunto del correo
        $mail->isHTML(true); // Habilitar HTML
        $mail->CharSet = 'UTF-8'; // Establecer la codificación de caracteres
        $mail->Body = $mensaje; // Cuerpo del correo en HTML

        // Enviar correo
        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>