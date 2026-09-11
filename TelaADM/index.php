
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../Login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - SGLE ITC</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Barra Lateral Administrativa -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="" alt="ITC Logo" class="brand-logo">
            <h2>SGLE - Admin</h2>
            <span class="user-role-badge">Responsável de Laboratório</span>
        </div>
        <nav class="menu-items">
            <a href="#" class="tab-link active" onclick="switchTab(event, 'equipamentos')"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-cable-icon lucide-cable">
                    <path d="M17 19a1 1 0 0 1-1-1v-2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a1 1 0 0 1-1 1z" />
                    <path d="M17 21v-2" />
                    <path d="M19 14V6.5a1 1 0 0 0-7 0v11a1 1 0 0 1-7 0V10" />
                    <path d="M21 21v-2" />
                    <path d="M3 5V3" />
                    <path d="M4 10a2 2 0 0 1-2-2V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a2 2 0 0 1-2 2z" />
                    <path d="M7 5V3" />
                </svg> Gerir Equipamentos</a>
            <a href="#" class="tab-link" onclick="switchTab(event, 'laboratorios')"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-door-closed-locked-icon lucide-door-closed-locked">
                    <path d="M19 8V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                    <path d="M2 21h8" />
                    <path d="M20 16v-2a2 2 0 00-4 0v2" />
                    <path d="M9 12h.01" />
                    <rect x="14" y="16" width="8" height="5" rx="1" />
                </svg> Gerir Laboratórios</a>
            <a href="#" class="tab-link" onclick="switchTab(event, 'requisicoes')">📥 Processar Requisições</a>
            <a href="#" class="tab-link" onclick="switchTab(event, 'manutencoes')"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-rotate-ccw-clock-icon lucide-rotate-ccw-clock">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                    <path d="M3 3v5h5" />
                    <path d="M12 7v5l4 2" />
                </svg> Histórico de Manutenções</a>
        </nav>
    </div>

    <!-- Conteúdo Principal -->
    <div class="main-content">

        <!-- Indicadores Analíticos baseados na DB -->
        <div class="dashboard-cards">
            <div class="card">
                <h3>Disponíveis</h3>
                <p class="num text-disponivel" id="total-disponiveis">...</p>
            </div>
            <div class="card">
                <h3>Em Uso</h3>
                <p class="num text-uso" id="total-em-uso">...</p>
            </div>
            <div class="card">
                <h3>Em Manutenção</h3>
                <p class="num text-manutencao" id="total-manutencao">...</p>
            </div>
        </div>

        <!-- ABA 1: EQUIPAMENTOS -->
        <div id="equipamentos" class="tab-content active">
            <div class="header-section">
                <h2>Inventário de Equipamentos Técnicos</h2>
                <button class="btn btn-primary" onclick="openModalEquipamentoCadastro()">+ Cadastrar
                    Equipamento</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Património</th>
                        <th>Nome do Ativo</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-equipamentos"></tbody>
            </table>
        </div>

        <!-- ABA 2: LABORATÓRIOS -->
        <div id="laboratorios" class="tab-content">
            <div class="header-section">
                <h2>Gestão de Laboratórios</h2>
                <button class="btn btn-primary" onclick="openModalLaboratorioCadastro()">+ Cadastrar
                    Laboratório</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID/Código</th>
                        <th>Nome do Espaço</th>
                        <th>Capacidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-laboratorios"></tbody>
            </table>
        </div>

        <!-- ABA 3: REQUISIÇÕES -->
        <div id="requisicoes" class="tab-content">
            <div class="header-section">
                <h2>Pedidos de Requisição Pendentes</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Solicitante</th>
                        <th>Recurso Requisitado</th>
                        <th>Período/Data</th>
                        <th>Ações Decisórias</th>
                    </tr>
                </thead>
                <tbody id="tabela-requisicoes"></tbody>
            </table>
        </div>

        <!-- ABA 4: MANUTENÇÕES -->
        <div id="manutencoes" class="tab-content">
            <div class="header-section">
                <h2>Histórico e Registos de Manutenções</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código do Item</th>
                        <th>Descrição da Intervenção</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody id="tabela-manutencoes"></tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: EQUIPAMENTOS -->
    <div id="modal-equipamento" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('modal-equipamento')">&times;</span>
            <h3 id="modal-eq-titulo">Registar Equipamento</h3>
            <form id="form-equipamento" onsubmit="submeterEquipamento(event)">
                <input type="hidden" name="acao" id="form-eq-acao" value="cadastrar_equipamento">
                <label>Número de Património Único:</label>
                <input type="text" name="patrimonio" id="form-eq-patrimonio" required placeholder="Ex: ITC-EQ-999">
                <label>Designação/Nome:</label>
                <input type="text" name="nome" id="form-eq-nome" required>
                <label>Estado do Equipamento:</label>
                <select name="estado" id="form-eq-estado">
                    <option value="disponivel">Disponível</option>
                    <option value="em_uso">Em Uso</option>
                    <option value="manutencao">Em Manutenção</option>
                </select>
                <button type="submit" class="btn-submit">Gravar Dados</button>
            </form>
        </div>
    </div>

    <!-- MODAL: LABORATÓRIOS -->
    <div id="modal-laboratorio" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('modal-laboratorio')">&times;</span>
            <h3 id="modal-lab-titulo">Registar Laboratório</h3>
            <form id="form-laboratorio" onsubmit="submeterLaboratorio(event)">
                <input type="hidden" name="acao" id="form-lab-acao" value="cadastrar_laboratorio">
                <label>Código da Sala:</label>
                <input type="text" name="codigo" id="form-lab-codigo" required placeholder="Ex: LAB-01">
                <label>Nome do Espaço:</label>
                <input type="text" name="nome" id="form-lab-nome" required>
                <label>Capacidade Máxima (Alunos):</label>
                <input type="number" name="capacidade" id="form-lab-capacidade" required min="1">
                <button type="submit" class="btn-submit">Gravar Dados</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>