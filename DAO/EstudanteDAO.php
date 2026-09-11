<?php
class EstudanteDAO {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function criar($utilizadorId, $curso, $codigoEstudante, $contacto) {
        $stmt = $this->pdo->prepare("
            INSERT INTO estudantes (utilizador_id, curso, codigo_estudante, contacto) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$utilizadorId, $curso, $codigoEstudante, $contacto]);
        return $this->pdo->lastInsertId();
    }

    public function contarTodos() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM estudantes");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    public function listarTodos() {
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id, 
                u.nome, 
                u.email,
                e.curso, 
                e.codigo_estudante,
                e.contacto
            FROM estudantes e
            JOIN utilizadores u ON e.utilizador_id = u.id
            ORDER BY u.nome
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}