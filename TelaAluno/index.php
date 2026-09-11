<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'estudante') {
    header("Location: ../Login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-MZ">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal do Aluno - SGLE</title>
  <link rel="stylesheet" href="aluno.css">
</head>

<body>

  <div class="app">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar">
      <div class="brand">
        <div class="logo">ITC</div>
        <div class="brand-name">SGLE - Aluno</div>
        <span class="role-badge">Aluno</span>
      </div>

      <nav>
        <button class="nav-item active" data-view="salas">
          <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-door-open-icon lucide-door-open">
              <path d="M10 21H2" />
              <path d="M10 4a2 2 0 012.36-1.968l5.41.992A1.5 1.5 0 0119 4.5V21l-7.876.992A1 1 0 0110 21z" />
              <path d="M10.268 3H7a2 2 0 00-2 2v16" />
              <path d="M14 12h.01" />
              <path d="M22 21h-3" />
            </svg></span> Consultar Salas
        </button>
        <button class="nav-item" data-view="equipamentos">
          <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-cable-icon lucide-cable">
              <path d="M17 19a1 1 0 0 1-1-1v-2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a1 1 0 0 1-1 1z" />
              <path d="M17 21v-2" />
              <path d="M19 14V6.5a1 1 0 0 0-7 0v11a1 1 0 0 1-7 0V10" />
              <path d="M21 21v-2" />
              <path d="M3 5V3" />
              <path d="M4 10a2 2 0 0 1-2-2V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a2 2 0 0 1-2 2z" />
              <path d="M7 5V3" />
            </svg></span> Equipamentos
        </button>
        <button class="nav-item" data-view="requisicoes">
          <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-check-icon lucide-check">
              <path d="M20 6 9 17l-5-5" />
            </svg></span> Minhas Requisições
        </button>
      </nav>
    </aside>

    <!-- ==== MAIN ==== -->
    <main>

      <!-- -- cards de resumo -- -->
      <section class="stats">
        <div class="stat-card">
          <span class="stat-label">Salas Disponíveis Agora</span>
          <strong class="stat-value green" id="stat-disponiveis">0</strong>
        </div>
        <div class="stat-card">
          <span class="stat-label">Minhas Requisições Pendentes</span>
          <strong class="stat-value blue" id="stat-pendentes">0</strong>
        </div>
        <div class="stat-card">
          <span class="stat-label">Salas em Manutenção</span>
          <strong class="stat-value red" id="stat-manutencao">0</strong>
        </div>
      </section>

      <!-- ===== VIEW: Consultar Salas (CU05) ===== -->
      <section class="view active" id="view-salas">
        <div class="section-head">
          <h1>Consultar Salas</h1>
        </div>

        <div class="controls">
          <div class="field">
            <label for="lab-select">Laboratório</label>
            <select id="lab-select"></select>
          </div>
          <div class="toggle-group" id="period-toggle">
            <button class="active" data-period="day">Hoje</button>
            <button data-period="week">Semana</button>
          </div>
        </div>

        <div class="alert" id="maint-alert">
          <span class="alert-icon">⚠️</span>
          <span>Este laboratório está marcado como <strong>Em Manutenção</strong> e encontra-se temporariamente
            interdito. Não é possível efectuar requisições para este espaço.</span>
        </div>

        <div class="panel">
          <div class="panel-head">
            <h2 id="schedule-title">Grelha de horários</h2>
            <div class="legend">
              <span class="legend-item"><span class="dot free"></span>Livre</span>
              <span class="legend-item"><span class="dot busy"></span>Requisitado</span>
              <span class="legend-item"><span class="dot maint"></span>Manutenção</span>
            </div>
          </div>

          <div class="grid-wrap">
            <table class="schedule" id="schedule-table"></table>
          </div>

          <div class="panel-foot">
            <span class="selection-note">Esta grelha é apenas para consulta. Para reservar um equipamento, vai a
              "Equipamentos".</span>
          </div>
        </div>
      </section>

      <!-- ===== VIEW: Equipamentos ===== -->
      <section class="view" id="view-equipamentos">
        <div class="section-head">
          <h1>Requisitar Equipamento</h1>
        </div>

        <div class="controls">
          <div class="field">
            <label for="equip-filter">Estado</label>
            <select id="equip-filter">
              <option value="todos">Todos</option>
              <option value="disponivel">Disponível</option>
              <option value="requisitado">Requisitado</option>
              <option value="manutencao">Em Manutenção</option>
              <option value="avariado">Avariado</option>
            </select>
          </div>
        </div>

        <div class="panel table-panel">
          <table class="data-table">
            <thead>
              <tr>
                <th>Património</th>
                <th>Nome do Ativo</th>
                <th>Estado</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody id="equip-tbody"></tbody>
          </table>
        </div>
      </section>

      <!-- ===== VIEW: Minhas Requisições ===== -->
      <section class="view" id="view-requisicoes">
        <div class="section-head">
          <h1>Minhas Requisições</h1>
        </div>

        <div class="panel table-panel">
          <table class="data-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Tipo</th>
                <th>Data / Horário</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="req-tbody"></tbody>
          </table>
          <div class="empty" id="req-empty" style="display:none;">Ainda não fez nenhuma requisição.</div>
        </div>
      </section>

    </main>
  </div>

  <div class="toast" id="toast">
    <span>✅</span>
    <span id="toast-text"></span>
  </div>

  <!-- ===== MODAL: Requisitar Equipamento ===== -->
  <div class="modal-backdrop" id="equip-modal">
    <div class="modal">
      <div class="modal-head">
        <h3>Requisitar Equipamento</h3>
        <button class="modal-close" id="modal-close">&times;</button>
      </div>
      <p class="modal-item-name" id="modal-item-name"></p>

      <div class="modal-field">
        <label for="modal-data">Data</label>
        <input type="date" id="modal-data">
      </div>
      <div class="modal-row">
        <div class="modal-field">
          <label for="modal-inicio">Hora de saída</label>
          <input type="time" id="modal-inicio">
        </div>
        <div class="modal-field">
          <label for="modal-fim">Previsão de devolução</label>
          <input type="time" id="modal-fim">
        </div>
      </div>

      <div class="modal-foot">
        <button class="btn-ghost" id="modal-cancel">Cancelar</button>
        <button class="btn-primary" id="modal-confirm">Confirmar Requisição</button>
      </div>
    </div>
  </div>

  <script src="aluno.js"></script>
</body>

</html>