<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'formador') {
    header("Location: ../Login.html");
    exit;
}

require '../config/db.php';
require '../DAO/EmprestimoDAO.php';

$dados = [
    'equipamento_id'  => $_POST['item_id'],
    'utilizador_id'   => $_SESSION['user_id'],
    'quantidade'      => $_POST['quantidade'],
    'laboratorio_id'  => $_POST['laboratorio_id'],
    'turma_id'        => $_POST['turma_id'],
    'data_uso'        => $_POST['data_requisicao'],
    'hora_inicio'     => $_POST['hora_inicio'],
    'hora_fim'        => $_POST['hora_fim'],
];

$dao = new EmprestimoDAO($pdo);
$resultado = $dao->criar($dados);

if (isset($resultado['erro'])) {
    header("Location: index.php?erro=" . urlencode($resultado['erro']));
} else {
    header("Location: index.php?sucesso=1");
}
exit;