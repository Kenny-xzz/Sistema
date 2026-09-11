<?php
require 'config/db.php';
require 'config/mail.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilizador) {
        $token = bin2hex(random_bytes(32));
        $expira_em = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("INSERT INTO reset_tokens (utilizador_id, token, expira_em) VALUES (?, ?, ?)");
        $stmt->execute([$utilizador['id'], $token, $expira_em]);
        $link = "http://10.215.61.154/Sistema/resetPass.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $mail_host;
            $mail->SMTPAuth = true;
            $mail->Username = $mail_username;
            $mail->Password = $mail_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $mail_port;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($mail_username, $mail_from_name);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Password - SGLE ITC';
            $mail->Body = "Olá {$utilizador['nome']},<br><br>Clica no link abaixo para definires uma nova password:<br><br><a href='$link'>$link</a><br><br>Este link expira em 1 hora.";

            $mail->send();
        } catch (Exception $e) {
            echo "<script>alert('Erro ao enviar email: {$mail->ErrorInfo}'); window.location.href='esqueci-senha.html';</script>";
            exit;
        }
    }

    echo "<script>alert('Se o email existir no sistema, receberás um link de recuperação.'); window.location.href='Login.html';</script>";
}
