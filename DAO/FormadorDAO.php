<?php
class FormadorDAO {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

   public function criar($utilizadorId, $nome, $codigo, $departamentoArea, $cidade, $telefone) {
    $stmt = $this->pdo->prepare("
        INSERT INTO formadores (utilizador_id, nome, codigo, departamento_area, cidade, telefone, estado_conta)
        VALUES (?, ?, ?, ?, ?, ?, 'pendente')
    ");
    $stmt->execute([$utilizadorId, $nome, $codigo, $departamentoArea, $cidade, $telefone]);
    return $this->pdo->lastInsertId();
}
}