<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function send_mail_simple($toEmail, $subject, $body) {

    // 🔥 CHANGE THESE
    $SMTP_USER = "mail.com"; 
    $SMTP_PASS = "pass";

    $mail = new PHPMailer(true);

    try {

        // SMTP Config
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom($SMTP_USER, 'Checklist System');

        // Receiver
        $mail->addAddress($toEmail);

        // Email Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo; // remove later
        return false;
    }
}