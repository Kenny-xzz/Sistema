/* ============================================================
   DADOS SIMULADOS
   Substituir por chamadas à API/backend real do SGLE.
   ============================================================ */

const LABS = [
  { id: "lab-redes", nome: "Laboratório de Redes", estado: "ativo" },
  { id: "lab-prog", nome: "Laboratório de Programação", estado: "ativo" },
  { id: "lab-hard", nome: "Laboratório de Hardware", estado: "manutencao" },
  { id: "lab-multi", nome: "Laboratório Multimédia", estado: "ativo" },
];

const DIAS_SEMANA = ["Seg", "Ter", "Qua", "Qui", "Sex"];
const HORAS = ["08:00", "09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00"];
const HOJE_INDEX = 1; // simula "hoje = Terça-feira" para a vista diária

// gera ocupação simulada e determinística por laboratório
function gerarOcupacao(labId) {
  let seed = 0;
  for (const c of labId) seed += c.charCodeAt(0);
  const grelha = {};
  DIAS_SEMANA.forEach((dia, di) => {
    HORAS.forEach((hora, hi) => {
      const v = (seed + di * 7 + hi * 3) % 5;
      grelha[`${di}-${hi}`] = v === 0 ? "busy" : "free";
    });
  });
  return grelha;
}

const EQUIPAMENTOS = [
  { pat: "PAT-0118", nome: "Portátil Dell Latitude", estado: "disponivel" },
  { pat: "PAT-0231", nome: "Projetor Epson EB-X06", estado: "requisitado" },
  { pat: "PAT-0304", nome: "Router Cisco RV340", estado: "disponivel" },
  { pat: "PAT-0412", nome: "Osciloscópio Digital", estado: "manutencao" },
  { pat: "PAT-0455", nome: "Kit Arduino Uno", estado: "disponivel" },
  { pat: "PAT-0489", nome: "Monitor LG 24\"", estado: "avariado" },
  { pat: "PAT-0502", nome: "Câmara de Rede D-Link", estado: "disponivel" },
];

let requisicoes = [
  { id: 1, item: "Kit Arduino Uno — PAT-0455", tipo: "Equipamento", data: "02 Set, 14:00–15:00", estado: "approved" },
  { id: 2, item: "Projetor Epson — PAT-0231", tipo: "Equipamento", data: "28 Ago, 09:00–10:00", estado: "pending" },
  { id: 3, item: "Câmara de Rede D-Link — PAT-0502", tipo: "Equipamento", data: "21 Ago, 10:00–11:00", estado: "rejected" },
];

/* ============================================================
   ESTADO
   ============================================================ */
let currentLab = LABS[0].id;
let currentPeriod = "day"; // "day" | "week"
let equipFilter = "todos";
let equipSelecionado = null; // patrimonio do item aberto no modal

/* ============================================================
   NAVEGAÇÃO ENTRE VIEWS
   ============================================================ */
document.querySelectorAll(".nav-item").forEach(btn => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".nav-item").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    document.querySelectorAll(".view").forEach(v => v.classList.remove("active"));
    document.getElementById(`view-${btn.dataset.view}`).classList.add("active");
    if (btn.dataset.view === "requisicoes") renderRequisicoes();
    if (btn.dataset.view === "equipamentos") renderEquipamentos();
  });
});

/* ============================================================
   SELECT DE LABORATÓRIO
   ============================================================ */
const labSelect = document.getElementById("lab-select");
LABS.forEach(lab => {
  const opt = document.createElement("option");
  opt.value = lab.id;
  opt.textContent = lab.nome;
  labSelect.appendChild(opt);
});
labSelect.addEventListener("change", () => {
  currentLab = labSelect.value;
  selectedSlot = null;
  renderSchedule();
});

/* ============================================================
   TOGGLE DIA / SEMANA
   ============================================================ */
document.querySelectorAll("#period-toggle button").forEach(btn => {
  btn.addEventListener("click", () => {
    document.querySelectorAll("#period-toggle button").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    currentPeriod = btn.dataset.period;
    renderSchedule();
  });
});

/* ============================================================
   RENDER DA GRELHA (CU05 — fluxo principal 3 e 4)
   ============================================================ */
function renderSchedule() {
  const lab = LABS.find(l => l.id === currentLab);
  const emManutencao = lab.estado === "manutencao";

  document.getElementById("maint-alert").classList.toggle("show", emManutencao);
  document.getElementById("schedule-title").textContent =
    `${lab.nome} — ${currentPeriod === "day" ? DIAS_SEMANA[HOJE_INDEX] + "-feira" : "Semana atual"}`;

  const table = document.getElementById("schedule-table");
  const grelha = gerarOcupacao(currentLab);
  const diasParaMostrar = currentPeriod === "day" ? [HOJE_INDEX] : DIAS_SEMANA.map((_, i) => i);

  let thead = "<thead><tr><th>Hora</th>";
  diasParaMostrar.forEach(di => thead += `<th>${DIAS_SEMANA[di]}</th>`);
  thead += "</tr></thead>";

  let tbody = "<tbody>";
  HORAS.forEach((hora, hi) => {
    tbody += `<tr><td class="time">${hora}</td>`;
    diasParaMostrar.forEach(di => {
      const estado = emManutencao ? "maint" : grelha[`${di}-${hi}`];
      tbody += `<td><div class="slot ${estado}" data-dia="${di}" data-hora="${hi}"></div></td>`;
    });
    tbody += "</tr>";
  });
  tbody += "</tbody>";

  table.innerHTML = thead + tbody;

  updateStats();
}

/* ============================================================
   CARDS DE RESUMO
   ============================================================ */
function updateStats() {
  const disponiveis = LABS.filter(l => l.estado !== "manutencao").length;
  const manutencao = LABS.filter(l => l.estado === "manutencao").length;
  const pendentes = requisicoes.filter(r => r.estado === "pending").length;

  document.getElementById("stat-disponiveis").textContent = disponiveis;
  document.getElementById("stat-manutencao").textContent = manutencao;
  document.getElementById("stat-pendentes").textContent = pendentes;
}

/* ============================================================
   VIEW: MINHAS REQUISIÇÕES
   ============================================================ */
const BADGE_LABEL = { pending: "Pendente", approved: "Aprovada", rejected: "Recusada" };

function renderRequisicoes() {
  const tbody = document.getElementById("req-tbody");
  const empty = document.getElementById("req-empty");

  if (requisicoes.length === 0) {
    tbody.innerHTML = "";
    empty.style.display = "block";
    return;
  }
  empty.style.display = "none";

  tbody.innerHTML = requisicoes.map(r => `
    <tr>
      <td>${r.item}</td>
      <td>${r.tipo}</td>
      <td>${r.data}</td>
      <td><span class="badge ${r.estado}">${BADGE_LABEL[r.estado]}</span></td>
    </tr>
  `).join("");
}

/* ============================================================
   VIEW: EQUIPAMENTOS (requisição de equipamento pelo Aluno)
   ============================================================ */
const EQUIP_BADGE_LABEL = {
  disponivel: "Disponível",
  requisitado: "Requisitado",
  manutencao: "Em Manutenção",
  avariado: "Avariado",
};

document.getElementById("equip-filter").addEventListener("change", (e) => {
  equipFilter = e.target.value;
  renderEquipamentos();
});

function renderEquipamentos() {
  const tbody = document.getElementById("equip-tbody");
  const lista = equipFilter === "todos"
    ? EQUIPAMENTOS
    : EQUIPAMENTOS.filter(eq => eq.estado === equipFilter);

  if (lista.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4" class="empty" style="padding:36px 20px;">Nenhum equipamento encontrado.</td></tr>`;
    return;
  }

  tbody.innerHTML = lista.map(eq => `
    <tr>
      <td class="patrimonio">${eq.pat}</td>
      <td>${eq.nome}</td>
      <td><span class="badge ${eq.estado}">${EQUIP_BADGE_LABEL[eq.estado]}</span></td>
      <td>
        <button class="btn-small" data-pat="${eq.pat}" ${eq.estado !== "disponivel" ? "disabled" : ""}>
          Requisitar
        </button>
      </td>
    </tr>
  `).join("");

  tbody.querySelectorAll(".btn-small:not(:disabled)").forEach(btn => {
    btn.addEventListener("click", () => abrirModalEquipamento(btn.dataset.pat));
  });
}

/* ---- modal de requisição de equipamento ---- */
const equipModal = document.getElementById("equip-modal");

function abrirModalEquipamento(pat) {
  const eq = EQUIPAMENTOS.find(e => e.pat === pat);
  equipSelecionado = pat;
  document.getElementById("modal-item-name").textContent = `${eq.nome} — ${eq.pat}`;
  document.getElementById("modal-data").value = "";
  document.getElementById("modal-inicio").value = "";
  document.getElementById("modal-fim").value = "";
  equipModal.classList.add("show");
}

function fecharModalEquipamento() {
  equipModal.classList.remove("show");
  equipSelecionado = null;
}

document.getElementById("modal-close").addEventListener("click", fecharModalEquipamento);
document.getElementById("modal-cancel").addEventListener("click", fecharModalEquipamento);
equipModal.addEventListener("click", (e) => {
  if (e.target === equipModal) fecharModalEquipamento();
});

document.getElementById("modal-confirm").addEventListener("click", () => {
  const data = document.getElementById("modal-data").value;
  const inicio = document.getElementById("modal-inicio").value;
  const fim = document.getElementById("modal-fim").value;

  if (!data || !inicio || !fim) {
    showToast("Preencha data, hora de saída e previsão de devolução.");
    return;
  }

  const eq = EQUIPAMENTOS.find(e => e.pat === equipSelecionado);
  eq.estado = "requisitado"; // reflete de imediato na listagem (RN03)

  requisicoes.unshift({
    id: Date.now(),
    item: `${eq.nome} — ${eq.pat}`,
    tipo: "Equipamento",
    data: `${data}, ${inicio}–${fim}`,
    estado: "pending",
  });

  showToast(`Pedido enviado para ${eq.nome}. Estado: Pendente.`);
  fecharModalEquipamento();
  renderEquipamentos();
  updateStats();
});

/* ============================================================
   TOAST
   ============================================================ */
let toastTimer;
function showToast(msg) {
  const toast = document.getElementById("toast");
  document.getElementById("toast-text").textContent = msg;
  toast.classList.add("show");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove("show"), 3200);
}

/* ============================================================
   INIT
   ============================================================ */
renderSchedule();
renderRequisicoes();
