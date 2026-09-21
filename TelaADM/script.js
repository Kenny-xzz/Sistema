document.addEventListener("DOMContentLoaded", () => { carregarPainelCompleto(); });

function carregarPainelCompleto() {
    atualizarContadores();
    listarEquipamentos();
    listarLaboratorios();
    listarRequisicoesEManutencoes();
    preencherDropdownLaboratorios();
}

function switchTab(event, tabId) {
    event.preventDefault();
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

// ------------------- EQUIPAMENTOS -------------------
function listarEquipamentos() {
    fetch('api.php?buscar=equipamentos').then(res => res.json()).then(dados => {
        const tbody = document.getElementById('tabela-equipamentos');
        tbody.innerHTML = dados.length === 0 ? '<tr><td colspan="5">Nenhum registo.</td></tr>' : '';
        dados.forEach(eq => {
            const labelEstado = eq.estado === 'disponivel' ? 'Disponível' : (eq.estado === 'em_uso' ? 'Em Uso' : 'Em Manutenção');
            tbody.innerHTML += `<tr>
                <td>${escapeHTML(eq.patrimonio)}</td>
                <td>${escapeHTML(eq.nome)}</td>
                <td>${eq.quantidade_disponivel} / ${eq.quantidade_total}</td>
                <td><span class="status-badge ${eq.estado}">${labelEstado}</span></td>
                <td>
    <button class="btn-action" onclick="openModalEquipamentoEdicao('${escapeHTML(eq.patrimonio)}','${escapeHTML(eq.nome)}','${eq.estado}',${eq.quantidade_total},${eq.laboratorio_id || 'null'})">Editar</button>
    <button class="btn-action" style="background:#fee2e2;color:#dc2626;" onclick="excluirEquipamento('${escapeHTML(eq.patrimonio)}')">Excluir</button>
</td>
            </tr>`;
        });
    });
}

function submeterEquipamento(e) {
    e.preventDefault();
    fetch('api.php', { method: 'POST', body: new FormData(document.getElementById('form-equipamento')) })
        .then(res => res.json()).then(res => { if (res.sucesso) { closeModal('modal-equipamento'); carregarPainelCompleto(); } });
}

function openModalEquipamentoCadastro() {
    document.getElementById('form-eq-acao').value = 'cadastrar_equipamento';
    document.getElementById('modal-eq-titulo').innerText = 'Registar Equipamento';
    document.getElementById('form-eq-patrimonio').readOnly = false;
    document.getElementById('form-equipamento').reset();
    document.getElementById('modal-equipamento').style.display = 'flex';
}

function openModalEquipamentoEdicao(patrimonio, nome, estado, quantidadeTotal, laboratorioId) {
    document.getElementById('form-eq-acao').value = 'atualizar_equipamento';
    document.getElementById('modal-eq-titulo').innerText = 'Editar Equipamento';
    document.getElementById('form-eq-patrimonio').value = patrimonio;
    document.getElementById('form-eq-patrimonio').readOnly = true;
    document.getElementById('form-eq-nome').value = nome;
    document.getElementById('form-eq-estado').value = estado;
    document.getElementById('form-eq-quantidade').value = quantidadeTotal;
    document.getElementById('form-eq-laboratorio').value = laboratorioId || '';
    document.getElementById('modal-equipamento').style.display = 'flex';
}

// ------------------- LABORATÓRIOS -------------------
function listarLaboratorios() {
    fetch('api.php?buscar=laboratorios').then(res => res.json()).then(dados => {
        const tbody = document.getElementById('tabela-laboratorios');
        tbody.innerHTML = dados.length === 0 ? '<tr><td colspan="4">Nenhum registo.</td></tr>' : '';
        dados.forEach(lab => {
            tbody.innerHTML += `<tr>
                <td>${escapeHTML(lab.codigo)}</td>
                <td>${escapeHTML(lab.nome)}</td>
                <td>${parseInt(lab.capacidade)} Alunos</td>
                <td>
    <button class="btn-action" onclick="openModalLaboratorioEdicao('${escapeHTML(lab.codigo)}','${escapeHTML(lab.nome)}',${lab.capacidade})">Editar</button>
    <button class="btn-action" style="background:#fee2e2;color:#dc2626;" onclick="excluirLaboratorio('${escapeHTML(lab.codigo)}')">Excluir</button>
</td>
                
            </tr>`;
        });
    });
}

function submeterLaboratorio(e) {
    e.preventDefault();
    fetch('api.php', { method: 'POST', body: new FormData(document.getElementById('form-laboratorio')) })
        .then(res => res.json()).then(res => { if (res.sucesso) { closeModal('modal-laboratorio'); carregarPainelCompleto(); } });
}

function openModalLaboratorioCadastro() {
    document.getElementById('form-lab-acao').value = 'cadastrar_laboratorio';
    document.getElementById('modal-lab-titulo').innerText = 'Registar Laboratório';
    document.getElementById('form-lab-codigo').readOnly = false;
    document.getElementById('form-laboratorio').reset();
    document.getElementById('modal-laboratorio').style.display = 'flex';
}

function openModalLaboratorioEdicao(codigo, nome, capacidade) {
    document.getElementById('form-lab-acao').value = 'atualizar_laboratorio';
    document.getElementById('modal-lab-titulo').innerText = 'Editar Laboratório';
    document.getElementById('form-lab-codigo').value = codigo;
    document.getElementById('form-lab-codigo').readOnly = true;
    document.getElementById('form-lab-nome').value = nome;
    document.getElementById('form-lab-capacidade').value = capacidade;
    document.getElementById('modal-laboratorio').style.display = 'flex';
}

// ------------------- AUXILIARES -------------------
function atualizarContadores() {
    fetch('api.php?buscar=contadores').then(res => res.json()).then(d => {
        document.getElementById('total-disponiveis').innerText = d.disponivel;
        document.getElementById('total-em-uso').innerText = d.em_uso;
        document.getElementById('total-manutencao').innerText = d.manutencao;
    });
}

function preencherDropdownLaboratorios() {
    fetch('api.php?buscar=laboratorios').then(res => res.json()).then(dados => {
        const select = document.getElementById('form-eq-laboratorio');
        select.innerHTML = '<option value="">— Circulante (via requisição) —</option>';
        dados.forEach(lab => {
            select.innerHTML += `<option value="${lab.id}">${escapeHTML(lab.nome)}</option>`;
        });
    });
}

function listarRequisicoesEManutencoes() {
    listarRequisicoes();
    listarManutencoes();
}

function listarManutencoes() {
    fetch('api.php?buscar=avarias').then(res => res.json()).then(dados => {
        const tbody = document.getElementById('tabela-manutencoes');
        tbody.innerHTML = dados.length === 0 ? '<tr><td colspan="3">Nenhuma avaria reportada.</td></tr>' : '';
        dados.forEach(av => {
            const acoes = av.estado === 'resolvida'
                ? '<span class="status-badge">Resolvida</span>'
                : `<button class="btn-action" onclick="resolverAvaria(${av.id})">Marcar Resolvida</button>`;

            tbody.innerHTML += `<tr>
                <td>${escapeHTML(av.patrimonio)} — ${escapeHTML(av.equipamento_nome)}</td>
                <td>${escapeHTML(av.descricao)}</td>
                <td><span class="status-badge ${av.estado === 'resolvida' ? '' : 'manutencao'}">${escapeHTML(av.estado)}</span> ${acoes}</td>
            </tr>`;
        });
    });
}

function resolverAvaria(id) {
    const dados = new FormData();
    dados.append('acao', 'resolverAvaria');
    dados.append('avaria_id', id);

    fetch('api.php', { method: 'POST', body: dados })
        .then(res => res.json())
        .then(res => { if (res.sucesso) carregarPainelCompleto(); });
}

function listarRequisicoes() {
    fetch('api.php?buscar=requisicoes').then(res => res.json()).then(dados => {
        const tbody = document.getElementById('tabela-requisicoes');
        tbody.innerHTML = dados.length === 0 ? '<tr><td colspan="4">Nenhum registo.</td></tr>' : '';
        dados.forEach(req => {
            const dataFormatada = req.data_uso ? new Date(req.data_uso).toLocaleDateString('pt-PT') : '-';
            const horario = (req.hora_inicio && req.hora_fim) ? `${req.hora_inicio.substring(0, 5)} - ${req.hora_fim.substring(0, 5)}` : '-';
            const recurso = req.laboratorio_nome ? `${escapeHTML(req.equipamento_nome)} (${escapeHTML(req.laboratorio_nome)})` : escapeHTML(req.equipamento_nome);

            let acoes = '';
            if (req.estado === 'pendente') {
                acoes = `
                    <button class="btn-action" onclick="decidirRequisicao(${req.id}, 'aprovado')">Aprovar</button>
                    <button class="btn-action" style="background:#fee2e2;color:#dc2626;" onclick="decidirRequisicao(${req.id}, 'rejeitado')">Rejeitar</button>
                `;
            } else if ((req.estado === 'aprovado' || req.estado === 'emprestado') && !req.hora_saida_real) {
                acoes = `<button class="btn-action" onclick="registarSaida(${req.id})">Registar Saída</button>`;
            } else {
                acoes = `<span class="status-badge">${escapeHTML(req.estado)}</span>`;
            }

            tbody.innerHTML += `<tr>
                <td>${escapeHTML(req.formador_nome)}${req.turma_nome ? ' — ' + escapeHTML(req.turma_nome) : ''}</td>
                <td>${recurso}</td>
                <td>${dataFormatada} | ${horario}</td>
                <td>${acoes}</td>
            </tr>`;
        });
    });
}

function decidirRequisicao(id, novoEstado) {
    const dados = new FormData();
    dados.append('acao', 'decidirRequisicao');
    dados.append('emprestimo_id', id);
    dados.append('novo_estado', novoEstado);

    fetch('api.php', { method: 'POST', body: dados })
        .then(res => res.json())
        .then(res => { if (res.sucesso) carregarPainelCompleto(); });
}

function registarSaida(id) {
    if (!confirm('Confirmar devolução deste equipamento agora?')) return;

    const dados = new FormData();
    dados.append('acao', 'registarSaida');
    dados.append('emprestimo_id', id);

    fetch('api.php', { method: 'POST', body: dados })
        .then(res => res.json())
        .then(res => { if (res.sucesso) carregarPainelCompleto(); });
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function escapeHTML(s) { return s.replace(/[&<>'"]/g, t => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[t] || t)); }

function excluirEquipamento(patrimonio) {
    if (!confirm('Tens a certeza que queres excluir este equipamento?')) return;

    const dados = new FormData();
    dados.append('acao', 'excluir_equipamento');
    dados.append('patrimonio', patrimonio);

    fetch('api.php', { method: 'POST', body: dados })
        .then(res => res.json())
        .then(res => { if (res.sucesso) carregarPainelCompleto(); });
}

function excluirLaboratorio(codigo) {
    if (!confirm('Tens a certeza que queres excluir este laboratório?')) return;

    const dados = new FormData();
    dados.append('acao', 'excluir_laboratorio');
    dados.append('codigo', codigo);

    fetch('api.php', { method: 'POST', body: dados })
        .then(res => res.json())
        .then(res => { if (res.sucesso) carregarPainelCompleto(); });
}