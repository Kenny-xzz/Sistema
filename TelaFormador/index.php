<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'formador') {
    header("Location: ../Login.html");
    exit;
}
?>


<?php
require '../config/db.php';

$utilizador_id = $_SESSION['user_id'];

// 1. Equipamentos Disponíveis
$stmtDisp = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'disponivel' AND laboratorio_id IS NULL");
$totalDisponiveis = $stmtDisp->fetchColumn();

// 2. Minhas Requisições (do formador logado)
$stmtReq = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE utilizador_id = ?");
$stmtReq->execute([$utilizador_id]);
$totalRequisicoes = $stmtReq->fetchColumn();

// 3. Equipamentos Avariados (usamos 'manutencao' como equivalente)
$stmtAv = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'manutencao'");
$totalAvariados = $stmtAv->fetchColumn();

// 4. Requisições Recentes do formador logado
$stmtTabela = $pdo->prepare("
    SELECT e.*, eq.nome as item_nome 
    FROM emprestimos e
    JOIN equipamentos eq ON e.equipamento_id = eq.id
    WHERE e.utilizador_id = ?
    ORDER BY e.data_pedido DESC
");
$stmtTabela->execute([$utilizador_id]);
$requisicoes = $stmtTabela->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Formador - SGLE</title>
    <link rel="stylesheet" href="style.css">
    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <!-- BARRA SUPERIOR -->
    <header class="topo">
        <div class="bolinhas">
            <span class="bolinha vermelha"></span>
            <span class="bolinha amarela"></span>
            <span class="bolinha verde"></span>
        </div>
        <div class="url">
            🔒 https://itc.gov.mz/sgle/formador
        </div>
        <button class="btn-sair">
            <i class="fa-solid fa-right-from-bracket"></i> Terminar Sessão
        </button>
    </header>

    <div class="container">
        <!-- MENU LATERAL -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fa-solid fa-computer"></i>
                <h2>SGLE</h2>
                <p>Sistema de Gestão de Laboratórios e Equipamentos</p>
                <span class="perfil">
                    <i class="fa-solid fa-user"></i> FORMADOR
                </span>
            </div>
            <div class="linha"></div>
            <ul class="menu">
                <li><a href="#" class="ativo"><i class="fa-solid fa-table-columns"></i> Painel de Controlo</a></li>
                <li><a href="#"><i class="fa-solid fa-laptop"></i> Equipamentos</a></li>
                <li><a href="#"><i class="fa-solid fa-building"></i> Laboratórios</a></li>
                <li><a href="#"><i class="fa-solid fa-clipboard-list"></i> Minhas Requisições</a></li>
                <li><a href="#"><i class="fa-solid fa-calendar-days"></i> Disponibilidade</a></li>
                <li><a href="#" onclick="abrirAvaria()"><i class="fa-solid fa-triangle-exclamation"></i> Reportar Avaria</a></li>
                <li><a href="#"><i class="fa-solid fa-bell"></i> Notificações</a></li>
                <li><a href="#"><i class="fa-solid fa-user"></i> Meu Perfil</a></li>
            </ul>
        </aside>

        <!-- ÁREA PRINCIPAL -->
        <main class="principal">
            <div class="cabecalho">
                <?php if (isset($_GET['erro'])): ?>
                    <div class="alerta erro"><?php echo htmlspecialchars($_GET['erro']); ?></div>
                <?php elseif (isset($_GET['sucesso'])): ?>
                    <div class="alerta sucesso">Requisição submetida com sucesso!</div>
                <?php endif; ?>
                <h1>Olá, Formador!</h1>
                <p>Consulte equipamentos, laboratórios e acompanhe as suas requisições.</p>
            </div>

            <!-- CARTÕES DINÂMICOS -->
            <section class="cards">
                <div class="card">
                    <div class="icone-card verde-card"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <h3>Equipamentos Disponíveis</h3>
                        <div class="numero texto-verde"><?php echo $totalDisponiveis; ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="icone-card azul-card"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div>
                        <h3>Minhas Requisições</h3>
                        <div class="numero texto-azul"><?php echo $totalRequisicoes; ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="icone-card vermelho-card"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h3>Equipamentos Avariados</h3>
                        <div class="numero texto-vermelho"><?php echo $totalAvariados; ?></div>
                    </div>
                </div>
            </section>

            <!-- BOTÕES -->
            <div class="acoes">
                <button class="acao nova-requisicao" onclick="abrirRequisicao()">
                    <i class="fa-solid fa-plus"></i> Nova Requisição
                </button>
                <button class="acao consultar">
                    <i class="fa-solid fa-magnifying-glass"></i> Consultar Disponibilidade
                </button>
                <button class="acao reportar" onclick="abrirAvaria()">
                    <i class="fa-solid fa-triangle-exclamation"></i> Reportar Avaria
                </button>
            </div>

            <!-- TABELA DINÂMICA -->
            <section class="secao-tabela">
                <h2>Minhas Requisições Recentes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Equipamento / Laboratório</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaRequisicoes">
                        <?php if (count($requisicoes) > 0): ?>
                            <?php foreach ($requisicoes as $req): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($req['item_nome']); ?></td>
                                    <td><?php echo $req['data_uso'] ? date('d/m/Y', strtotime($req['data_uso'])) : '-'; ?></td>
                                    <td><?php echo ($req['hora_inicio'] && $req['hora_fim']) ? substr($req['hora_inicio'], 0, 5) . ' - ' . substr($req['hora_fim'], 0, 5) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $mapaEstado = [
                                            'pendente' => 'pendente',
                                            'aprovado' => 'aprovada',
                                            'emprestado' => 'aprovada',
                                            'devolvido' => 'aprovada',
                                            'rejeitado' => 'rejeitada'
                                        ];
                                        $classeEstado = $mapaEstado[$req['estado']] ?? 'pendente';
                                        $labelEstado = ucfirst($req['estado']);
                                        ?>
                                        <span class="estado <?php echo $classeEstado; ?>">
                                            <?php echo htmlspecialchars($labelEstado); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Nenhuma requisição encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- MODAL NOVA REQUISIÇÃO -->
    <div class="modal" id="modalRequisicao">
        <div class="modal-conteudo">
            <span class="fechar" onclick="fecharRequisicao()">&times;</span>
            <h2>Nova Requisição</h2>
            <form action="processar_requisicao.php" method="POST">

               

                <label>Equipamento / Laboratório</label>
                <select name="item_id" id="item">
                    <?php
                    $itens = $pdo->query("
        SELECT id, nome FROM equipamentos
        WHERE estado = 'disponivel' AND laboratorio_id IS NULL
    ")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($itens as $it) {
                        echo "<option value='{$it['id']}'>{$it['nome']}</option>";
                    }
                    ?>
                </select>
                <label>Laboratório</label>
                <select name="laboratorio_id" id="laboratorio" required>
                    <?php
                    $labs = $pdo->query("SELECT id, nome FROM laboratorios")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($labs as $lab) {
                        echo "<option value='{$lab['id']}'>{$lab['nome']}</option>";
                    }
                    ?>
                </select>

                <label>Turma</label>
                <select name="turma_id" id="turma" required>
                    <?php
                    $turmas = $pdo->query("SELECT id, nome FROM turmas")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($turmas as $t) {
                        echo "<option value='{$t['id']}'>{$t['nome']}</option>";
                    }
                    ?>
                </select>

                <label>Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" min="1" value="1" required>
                <label>Data</label>
                <input type="date" name="data_requisicao" id="dataRequisicao" required>

                <label>Hora de início</label>
                <input type="time" name="hora_inicio" id="horaInicio" required>

                <label>Previsão de devolução</label>
                <input type="time" name="hora_fim" id="horaFim" required>

                <button type="submit" class="btn-enviar">Submeter Requisição</button>
            </form>
        </div>
    </div>

    <!-- MODAL REPORTAR AVARIA -->
    <div class="modal" id="modalAvaria">
        <div class="modal-conteudo">
            <span class="fechar" onclick="fecharAvaria()">&times;</span>
            <h2>Reportar Avaria</h2>
            <form action="processar_avaria.php" method="POST">
                <label>Código do Equipamento</label>
                <input type="text" name="codigo" id="codigoEquipamento" placeholder="Ex: EQ-001" required>

                <label>Descrição da avaria</label>
                <textarea name="descricao" id="descricaoAvaria" placeholder="Ex: O equipamento não liga..." required></textarea>

                <button type="submit" class="btn-enviar">Submeter Avaria</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>