<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']); // ajusta se o campo "email" for na verdade o email
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilizador && password_verify($password, $utilizador['password'])) {

        // se for formador, verifica o estado da conta antes de deixar entrar
        if ($utilizador['tipo'] === 'formador') {
            $stmtFormador = $pdo->prepare("SELECT estado_conta FROM formadores WHERE utilizador_id = ?");
            $stmtFormador->execute([$utilizador['id']]);
            $formador = $stmtFormador->fetch(PDO::FETCH_ASSOC);

            if ($formador['estado_conta'] === 'pendente') {
                echo "<script>alert('A sua conta ainda está em análise. Aguarde aprovação do administrador.'); window.location.href='Login.html';</script>";
                exit;
            }
            if ($formador['estado_conta'] === 'rejeitado') {
                echo "<script>alert('O seu registo foi rejeitado. Contacte o administrador.'); window.location.href='Login.html';</script>";
                exit;
            }
        }

        $_SESSION['user_id'] = $utilizador['id'];
        $_SESSION['tipo'] = $utilizador['tipo'];
        $_SESSION['nome'] = $utilizador['nome'];
        $_SESSION['email'] = $utilizador['email'];
        // redireciona conforme o tipo
        // redireciona conforme o tipo
        if ($utilizador['tipo'] === 'admin') {
            header("Location: TelaADM/index.php");
        } elseif ($utilizador['tipo'] === 'formador') {
            header("Location: TelaFormador/index.php");
        } else {
            header("Location: TelaAluno/index.php");
        }
        exit;
    } else {
        echo "<script>alert('Email ou password incorretos'); window.location.href='Login.html';</script>";
    }
}
