<?php
require 'config/db.php';
require 'DAO/UtilizadorDAO.php';
require 'DAO/EstudanteDAO.php';
require 'DAO/FormadorDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];

    if (!in_array($tipo, ['estudante', 'formador'])) {
        die("Tipo de conta inválido.");
    }

    $utilizadorDAO = new UtilizadorDAO($pdo);

    if ($tipo === 'estudante') {
        $nome = trim($_POST['nome_estudante']);
        $email = trim($_POST['email_estudante']);
        $password = $_POST['password_estudante'];
        $curso = trim($_POST['curso']);
        $codigo_estudante = trim($_POST['codigo_estudante']);
        $contacto = '';
    } else {
        $nome = trim($_POST['nome_empresa']);        // Nome do Formador
        $email = trim($_POST['ramo_atividade']);      // E-mail (nome de campo antigo, mantido)
        $password = $_POST['password_empresa'];
        $codigo = trim($_POST['nuit']);               // Código (nome de campo antigo, mantido)
        $departamento_area = trim($_POST['endereco']); // Departamento/Área (nome de campo antigo, mantido)
        $cidade = trim($_POST['cidade']);
        $telefone = trim($_POST['telefone']);
    }

    if ($utilizadorDAO->emailExiste($email)) {
        echo "<script>alert('Este email já está registado'); window.location.href='Registo.html';</script>";
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $utilizador_id = $utilizadorDAO->criar($nome, $email, $password_hash, $tipo);

        if ($tipo === 'estudante') {
            $estudanteDAO = new EstudanteDAO($pdo);
            $estudanteDAO->criar($utilizador_id, $curso, $codigo_estudante, $contacto);
        } else {
            $formadorDAO = new FormadorDAO($pdo);
            $formadorDAO->criar($utilizador_id, $codigo, $departamento_area, $cidade, $telefone);
        }

        $pdo->commit();

        if ($tipo === 'formador') {
            echo "<script>alert('Conta criada! A sua conta de formador está em análise.'); window.location.href='Login.html';</script>";
        } else {
            echo "<script>alert('Conta criada com sucesso!'); window.location.href='Login.html';</script>";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao criar conta. Tente novamente.'); window.location.href='Registo.html';</script>";
    }
}
?>