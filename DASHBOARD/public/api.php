<?php
header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../src/Services/AnalyticsService.php';

$action = $_GET['action'] ?? '';
$service = new AnalyticsService();

// conexão PDO necessária para algumas rotas
$database = new Database();
$pdo = $database->getConnection();

switch ($action) {

    case 'kpis_gerais':
        // Mock de retorno para os cards do topo
        echo json_encode([
            'total_respondentes' => 1284,
            'media_geral' => 8.4,
            'empresas_ativas' => 42
        ]);
        break;

    case 'benchmark':
        $dados = $service->getBenchmarkGeral();
        echo json_encode($dados);
        break;

    // ==============================
    // TRECHO 1 ADICIONADO
    // ==============================
    case 'get_dashboard_data':
        $kpis = $service->getKPIGerais();
        $benchmark = $service->getBenchmarkGeral();
        
        echo json_encode([
            'kpis' => [
                'total_respondentes' => number_format($kpis['total_respostas'], 0, ',', '.'),
                'media_satisfacao' => number_format($kpis['media_geral'], 1, ',', '.'),
                'empresas_ativas' => $kpis['total_empresas']
            ],
            'benchmark' => [
                'labels' => array_column($benchmark, 'empresa'),
                'valores' => array_column($benchmark, 'media')
            ]
        ]);
        break;

    // ==============================
    // TRECHO 2 ADICIONADO
    // ==============================
    case 'get_pergunta_detalhe':
        $pergunta_id = $_GET['pergunta_id'] ?? null;
        $empresa_id = $_GET['empresa_id'] ?? null; // Opcional, para filtrar por empresa

        if (!$pergunta_id) {
            echo json_encode(['error' => 'ID da pergunta obrigatório']);
            break;
        }

        // Busca a distribuição de respostas para esta pergunta
        $sql = "SELECT valor_resposta as label, COUNT(*) as total 
                FROM respostas r
                JOIN respondentes res ON r.respondente_id = res.id
                JOIN formularios f ON res.formulario_id = f.id
                WHERE r.pergunta_id = ? ";
        
        $params = [$pergunta_id];

        if ($empresa_id) {
            $sql .= " AND f.empresa_id = ?";
            $params[] = $empresa_id;
        }

        $sql .= " GROUP BY valor_resposta ORDER BY valor_numerico DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'labels' => array_column($dados, 'label'),
            'valores' => array_column($dados, 'total')
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Rota não encontrada']);
        break;
}
