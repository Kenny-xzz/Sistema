<?php
class EstagioDAO {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listarTodos() {
        $stmt = $this->pdo->prepare("
            SELECT 
                e.id,
                e.empresa_id,
                e.titulo,
                e.area,
                e.descricao,
                e.requisitos,
                e.vagas,
                e.data_inicio,
                e.data_fim,
                e.duracao_meses,
                e.estado,
                emp.nome_empresa,
                emp.ramo_atividade,
                emp.morada,
                emp.cidade,
                emp.telefone,
                emp.email_institucional
            FROM estagios e
            JOIN empresas emp ON e.empresa_id = emp.id
            WHERE e.estado = 'aberto'
            ORDER BY e.data_inicio
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}