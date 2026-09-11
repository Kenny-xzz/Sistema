<?php
// api.php - Restful API para Equipamentos e Laboratórios
header('Content-Type: application/json');

$host = "localhost";
$db = "gestao_estagios";
$user = "root";
$pass = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["sucesso" => false, "mensagem" => "Sem ligação à BD."]);
    exit;
}

// ------------------- OPERAÇÕES LEITURA (GET) -------------------
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['buscar'])) {
    $busca = $_GET['buscar'];



    if ($busca === 'equipamentos') {
        echo json_encode($pdo->query("SELECT * FROM equipamentos ORDER BY data_registo DESC")->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($busca === 'laboratorios') {
        echo json_encode($pdo->query("SELECT * FROM laboratorios ORDER BY codigo ASC")->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($busca === 'contadores') {
        $disp = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado='disponivel'")->fetchColumn();
        $uso = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado='em_uso'")->fetchColumn();
        $man = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado='manutencao'")->fetchColumn();
        echo json_encode(["disponivel" => (int)$disp, "em_uso" => (int)$uso, "manutencao" => (int)$man]);
        exit;
    }
}

// ------------------- OPERAÇÕES ESCRITA / EDIÇÃO (POST) -------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST['acao'] ?? '';

    try {
        // CRUD Equipamentos
        if ($acao === 'cadastrar_equipamento') {
            $stmt = $pdo->prepare("INSERT INTO equipamentos (patrimonio, nome, estado) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['patrimonio'], $_POST['nome'], $_POST['estado']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }
        if ($acao === 'atualizar_equipamento') {
            $stmt = $pdo->prepare("UPDATE equipamentos SET nome = ?, estado = ? WHERE patrimonio = ?");
            $stmt->execute([$_POST['nome'], $_POST['estado'], $_POST['patrimonio']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }

        if ($acao === 'excluir_equipamento') {
            $stmt = $pdo->prepare("DELETE FROM equipamentos WHERE patrimonio = ?");
            $stmt->execute([$_POST['patrimonio']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }
        if ($acao === 'excluir_laboratorio') {
            $stmt = $pdo->prepare("DELETE FROM laboratorios WHERE codigo = ?");
            $stmt->execute([$_POST['codigo']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }

        // CRUD Laboratórios
        if ($acao === 'cadastrar_laboratorio') {
            $stmt = $pdo->prepare("INSERT INTO laboratorios (codigo, nome, capacidade) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['codigo'], $_POST['nome'], $_POST['capacidade']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }
        if ($acao === 'atualizar_laboratorio') {
            $stmt = $pdo->prepare("UPDATE laboratorios SET nome = ?, capacidade = ? WHERE codigo = ?");
            $stmt->execute([$_POST['nome'], $_POST['capacidade'], $_POST['codigo']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["sucesso" => false, "mensagem" => $e->getMessage()]);
        exit;
    }
}
