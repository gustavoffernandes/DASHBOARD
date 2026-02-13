<?php
require_once '../config/Database.php';

// Captura o input bruto enviado pelo Google
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Registra log para conferência em caso de falha
$log_file = 'debug_log.txt';
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Recebido: " . (strlen($input) > 0 ? "JSON OK" : "VAZIO") . PHP_EOL, FILE_APPEND);

if (!$data) exit;

try {
    $pdo = Database::getInstance();
    
    // 1. Extrair chaves e valores para montar a query dinâmica
    // Isso evita ter que escrever os 90 nomes de colunas manualmente
    $colunas = array_keys($data);
    $valores = array_values($data);
    
    // 2. Preparar a Query SQL dinâmica para a tabela respostas_consolidadas
    $sql = "INSERT INTO respostas_consolidadas (" . implode(', ', $colunas) . ") 
            VALUES (" . implode(', ', array_fill(0, count($colunas), '?')) . ")";
    
    $stmt = $pdo->prepare($sql);
    
    // 3. Executar a inserção
    $stmt->execute($valores);

    echo "Sucesso! Dados inseridos na tabela respostas_consolidadas.";

} catch (Exception $e) {
    // Registra o erro específico do MySQL no log se a inserção falhar
    file_put_contents($log_file, "ERRO SQL: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo "Erro: " . $e->getMessage();
}