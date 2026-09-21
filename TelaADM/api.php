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


    if ($busca === 'requisicoes') {
        $sql = "
            SELECT 
                e.id,
                e.estado,
                e.data_uso,
                e.hora_inicio,
                e.hora_fim,
                e.hora_saida_real,
                eq.nome AS equipamento_nome,
                lab.nome AS laboratorio_nome,
                t.nome AS turma_nome,
                u.nome AS formador_nome
            FROM emprestimos e
            JOIN equipamentos eq ON e.equipamento_id = eq.id
            LEFT JOIN laboratorios lab ON e.laboratorio_id = lab.id
            LEFT JOIN turmas t ON e.turma_id = t.id
            JOIN utilizadores u ON e.utilizador_id = u.id
            ORDER BY e.data_pedido DESC
        ";
        echo json_encode($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($busca === 'equipamentos') {
        echo json_encode($pdo->query("SELECT * FROM equipamentos ORDER BY data_registo DESC")->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($busca === 'avarias') {
        $sql = "
            SELECT a.id, a.descricao, a.estado, a.data_reporte,
                   eq.patrimonio, eq.nome AS equipamento_nome,
                   u.nome AS reportado_por_nome
            FROM avarias a
            JOIN equipamentos eq ON a.equipamento_id = eq.id
            JOIN utilizadores u ON a.reportado_por = u.id
            ORDER BY a.data_reporte DESC
        ";
        echo json_encode($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
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
            $laboratorioId = !empty($_POST['laboratorio_id']) ? $_POST['laboratorio_id'] : null;
            $stmt = $pdo->prepare("INSERT INTO equipamentos (patrimonio, nome, estado, quantidade_total, quantidade_disponivel, laboratorio_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['patrimonio'], $_POST['nome'], $_POST['estado'], $_POST['quantidade_total'], $_POST['quantidade_total'], $laboratorioId]);
            echo json_encode(["sucesso" => true]);
            exit;
        }

        if ($acao === 'atualizar_equipamento') {
            $laboratorioId = !empty($_POST['laboratorio_id']) ? $_POST['laboratorio_id'] : null;

            $stmtAtual = $pdo->prepare("SELECT quantidade_total, quantidade_disponivel FROM equipamentos WHERE patrimonio = ?");
            $stmtAtual->execute([$_POST['patrimonio']]);
            $atual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

            $emprestados = $atual['quantidade_total'] - $atual['quantidade_disponivel'];
            $novoTotal = (int)$_POST['quantidade_total'];
            $novoDisponivel = max(0, $novoTotal - $emprestados);

            $stmt = $pdo->prepare("UPDATE equipamentos SET nome = ?, estado = ?, quantidade_total = ?, quantidade_disponivel = ?, laboratorio_id = ? WHERE patrimonio = ?");
            $stmt->execute([$_POST['nome'], $_POST['estado'], $novoTotal, $novoDisponivel, $laboratorioId, $_POST['patrimonio']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }

        if ($acao === 'decidirRequisicao') {
            $stmt = $pdo->prepare("UPDATE emprestimos SET estado = ? WHERE id = ?");
            $stmt->execute([$_POST['novo_estado'], $_POST['emprestimo_id']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }

        if ($acao === 'registarSaida') {
            $stmt = $pdo->prepare("
                UPDATE emprestimos
                SET hora_saida_real = CURTIME(), estado = 'devolvido'
                WHERE id = ? AND hora_saida_real IS NULL
            ");
            $stmt->execute([$_POST['emprestimo_id']]);
            echo json_encode(["sucesso" => true]);
            exit;
        }
        if ($acao === 'resolverAvaria') {
            $stmt = $pdo->prepare("SELECT equipamento_id FROM avarias WHERE id = ?");
            $stmt->execute([$_POST['avaria_id']]);
            $av = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdo->prepare("UPDATE avarias SET estado = 'resolvida', data_resolucao = NOW() WHERE id = ?")
                ->execute([$_POST['avaria_id']]);
            $pdo->prepare("UPDATE equipamentos SET estado = 'disponivel' WHERE id = ?")
                ->execute([$av['equipamento_id']]);

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
