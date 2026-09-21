<?php
class EmprestimoDAO {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function getJanelaTurno($turmaId) {
        $stmt = $this->pdo->prepare("
            SELECT th.hora_inicio, th.hora_fim
            FROM turmas t
            JOIN turnos_horario th ON t.turno = th.turno
            WHERE t.id = ?
        ");
        $stmt->execute([$turmaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getQuantidadeReservada($equipamentoId, $dataUso, $horaInicio, $horaFim) {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(quantidade), 0) AS total
            FROM emprestimos
            WHERE equipamento_id = ?
              AND data_uso = ?
              AND estado IN ('pendente','aprovado','emprestado')
              AND NOT (hora_fim <= ? OR hora_inicio >= ?)
        ");
        $stmt->execute([$equipamentoId, $dataUso, $horaInicio, $horaFim]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function criar($dados) {
        $dados['hora_inicio'] = strlen($dados['hora_inicio']) === 5 ? $dados['hora_inicio'] . ':00' : $dados['hora_inicio'];
        $dados['hora_fim'] = strlen($dados['hora_fim']) === 5 ? $dados['hora_fim'] . ':00' : $dados['hora_fim'];

        if ($dados['hora_fim'] <= $dados['hora_inicio']) {
            return ['erro' => 'A hora de fim tem de ser depois da hora de início, e no mesmo dia.'];
        }

        if ($dados['hora_inicio'] < '07:00:00' || $dados['hora_fim'] > '21:10:00') {
            return ['erro' => 'Fora do horário de funcionamento (07:00 - 21:10).'];
        }

        $janela = $this->getJanelaTurno($dados['turma_id']);
        if (!$janela) {
            return ['erro' => 'Turma inválida.'];
        }
        if ($dados['hora_inicio'] < $janela['hora_inicio'] || $dados['hora_fim'] > $janela['hora_fim']) {
            return ['erro' => 'Horário fora do turno da turma (' . substr($janela['hora_inicio'],0,5) . ' - ' . substr($janela['hora_fim'],0,5) . ').'];
        }

        $reservado = $this->getQuantidadeReservada(
            $dados['equipamento_id'], $dados['data_uso'], $dados['hora_inicio'], $dados['hora_fim']
        );

                $stmtQtd = $this->pdo->prepare("SELECT quantidade_total FROM equipamentos WHERE id = ?");
        $stmtQtd->execute([$dados['equipamento_id']]);
        $eq = $stmtQtd->fetch(PDO::FETCH_ASSOC);
        if (!$eq) {
            return ['erro' => 'Equipamento inválido.'];
        }

        if (($reservado + $dados['quantidade']) > $eq['quantidade_total']) {
            return ['erro' => 'Não há unidades suficientes disponíveis nesse horário.'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO emprestimos (equipamento_id, utilizador_id, quantidade, laboratorio_id, turma_id, data_uso, hora_inicio, hora_fim, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente')
        ");
        $stmt->execute([
            $dados['equipamento_id'], $dados['utilizador_id'], $dados['quantidade'],
            $dados['laboratorio_id'], $dados['turma_id'], $dados['data_uso'],
            $dados['hora_inicio'], $dados['hora_fim']
        ]);

        return ['sucesso' => true];
    }
}
    
