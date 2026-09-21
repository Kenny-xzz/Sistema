<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'formador') {
    header("Location: ../Login.html");
    exit;
}

require '../config/db.php';

$codigo = trim($_POST['codigo']);
$descricao = trim($_POST['descricao']);

$stmt = $pdo->prepare("SELECT id FROM equipamentos WHERE patrimonio = ?");
$stmt->execute([$codigo]);
$eq = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$eq) {
    header("Location: index.php?erro=" . urlencode("Equipamento com código '$codigo' não encontrado."));
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO avarias (equipamento_id, reportado_por, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$eq['id'], $_SESSION['user_id'], $descricao]);

    $stmt = $pdo->prepare("UPDATE equipamentos SET estado = 'manutencao' WHERE id = ?");
    $stmt->execute([$eq['id']]);

    $pdo->commit();
    header("Location: index.php?sucesso=1");
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: index.php?erro=" . urlencode("Erro ao registar avaria."));
}
exit;