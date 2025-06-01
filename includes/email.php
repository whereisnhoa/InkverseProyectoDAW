<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye el autoload de Composer para usar PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Envía un correo de verificación al usuario
 *
 * @param string $para Correo del destinatario
 * @param string $nombre Nombre del usuario destinatario
 * @param string $link_verificacion Enlace de verificación
 * @return bool true si se envió, false si hubo error
 */
function enviarCorreoVerificacion($para, $nombre, $link_verificacion) 
{
    $mail = new PHPMailer(true);

    try {
        // CONFIGURACIÓN DE MAILTRAP SMTP
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Host de Mailtrap
        $mail->SMTPAuth   = true;
        $mail->Username   = '9f0e43dffe2385'; // Username de Mailtrap
        $mail->Password   = '8fff4764141894';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525;

        // Remitente
        $mail->setFrom('no-reply@inkverse.com', 'InkVerse');
        $mail->addAddress($para, $nombre);

        $mail->Subject = 'Verifica tu correo electrónico - InkVerse';
        $mail->isHTML(true);
        $mail->Body = "
            <h2>¡Hola $nombre!</h2>
            <p>Gracias por registrarte en <b>InkVerse</b>.</p>
            <p>Para activar tu cuenta, haz clic en el siguiente enlace:</p>
            <a href='$link_verificacion' style='background:#8c52ff;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;font-weight:bold;'>Verificar mi correo</a>
            <p style='font-size:0.95em;color:#888;margin-top:18px;'>Si no te registraste tú, ignora este mensaje.</p>
        ";
        $mail->AltBody = "Hola $nombre,\n\nGracias por registrarte en InkVerse.\nVerifica tu cuenta aquí: $link_verificacion";

        $mail->send();
        return true;

    } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        return false;
    }
}