<?php
class AnalyticsService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Calcula o Benchmark: Média de satisfação por empresa
    public function getBenchmarkGeral() {
        $sql = "SELECT e.nome as empresa, AVG(r.valor_numerico) as media 
                FROM empresas e
                JOIN formularios f ON e.id = f.empresa_id
                JOIN respondentes res ON f.id = res.formulario_id
                JOIN respostas r ON res.id = r.respondente_id
                JOIN perguntas p ON r.pergunta_id = p.id
                WHERE p.tipo_pergunta = 'escala'
                GROUP BY e.id
                ORDER BY media DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    // Dados demográficos: Média por Setor
    public function getMediaPorSetor($empresa_id) {
        $sql = "SELECT res.setor, AVG(r.valor_numerico) as media
                FROM respondentes res
                JOIN respostas r ON res.id = r.respondente_id
                JOIN formularios f ON res.formulario_id = f.id
                WHERE f.empresa_id = ?
                GROUP BY res.setor";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresa_id]);
        return $stmt->fetchAll();
    }

    public function getKPIGerais() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM respondentes) as total_respostas,
                    (SELECT COUNT(*) FROM empresas) as total_empresas,
                    (SELECT AVG(valor_numerico) FROM respostas WHERE valor_numerico IS NOT NULL) as media_geral";
        return $this->db->query($sql)->fetch();
    }

    public function getDistribuicaoPorPergunta($pergunta_id) {
        // Conta quantas vezes cada valor (1 a 5, por exemplo) apareceu
        $sql = "SELECT valor_resposta as etiqueta, COUNT(*) as total 
                FROM respostas 
                WHERE pergunta_id = ? 
                GROUP BY valor_resposta";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pergunta_id]);
        return $stmt->fetchAll();
    }

}