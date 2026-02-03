<?php
require 'config.php';

/**
 * Função de Inversão de Score
 * Aplica f(x) = 6 - x para perguntas onde 5 é um indicador negativo de saúde.
 */
function calcularScore($pergunta_id, $valor_bruto) {
    // Lista de IDs das perguntas que são negativas por natureza
    $perguntas_negativas = [2, 5, 10, 15, 20, 25, 30]; 

    if (in_array($pergunta_id, $perguntas_negativas)) {
        return 6 - $valor_bruto;
    }
    return $valor_bruto;
}

// Simulação de recebimento de dados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id = $_POST['empresa_id'];
    $respostas = $_POST['respostas']; // Espera um array: [['id' => 1, 'valor' => 4, 'eixo' => 'Gestao'], ...]

    $stmt = $pdo->prepare("INSERT INTO respostas (empresa_id, pergunta_id, valor_bruto, valor_ajustado, eixo_tematico) VALUES (?, ?, ?, ?, ?)");

    foreach ($respostas as $resp) {
        $valor_ajustado = calcularScore($resp['id'], $resp['valor']);
        
        $stmt->execute([
            $empresa_id, 
            $resp['id'], 
            $resp['valor'], 
            $valor_ajustado, 
            $resp['eixo']
        ]);
    }
    
    // Após salvar, redireciona para a dashboard
    header("Location: index.php?empresa_id=" . $empresa_id);
    exit;
}