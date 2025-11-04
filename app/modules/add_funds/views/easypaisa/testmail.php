<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'mail.alibuy.pk'; // Your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'support@alibuy.pk'; // SMTP username
    $mail->Password = 'Aliabbas321@'; // SMTP password

    // SSL on port 465
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('support@alibuy.pk', 'Test Email');
    $mail->addAddress('aliabbaszounr213@gmail.com'); // Your test email address

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = '<h3>This is a test email sent using PHPMailer with SSL on port 465.</h3>';

    // Send email
    if ($mail->send()) {
        echo 'Test email has been sent successfully.';
    } else {
        echo 'Failed to send test email.';
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
