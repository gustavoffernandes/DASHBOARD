<?php
require 'config.php';

// 1. Limpeza de Segurança
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE respostas;");
$pdo->exec("TRUNCATE TABLE empresas;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "Limpando tabelas para novos dados corporativos... <br>";

// 2. Criação das Empresas
$empresas = [
    ['nome' => 'PROATIVA Matriz', 'segmento' => 'Consultoria'],
    ['nome' => 'TechLog Logística', 'segmento' => 'Transportes'],
    ['nome' => 'Construtora Norte', 'segmento' => 'Construção Civil'],
    ['nome' => 'Hospital Vida', 'segmento' => 'Saúde']
];

$stmtEmpresa = $pdo->prepare("INSERT INTO empresas (nome, segmento) VALUES (?, ?)");
foreach ($empresas as $emp) {
    $stmtEmpresa->execute([$emp['nome'], $emp['segmento']]);
}

$empresaIds = $pdo->query("SELECT id FROM empresas")->fetchAll(PDO::FETCH_COLUMN);

// 3. Configuração de Eixos
$eixos = [
    'Contexto' => range(1, 15),
    'Gestao' => range(16, 30),
    'Vivencias' => range(31, 45),
    'Danos' => range(46, 60)
];

$preguntas_negativas = [2, 5, 10, 15, 20, 35, 50, 55, 60];

// --- NOVA LÓGICA DE VOLUME ---
$respostas_por_pergunta = 20; // Simula 20 funcionários respondendo cada questão
echo "Gerando " . (count($empresaIds) * 60 * $respostas_por_pergunta) . " registros de respostas... <br>";

$stmtResp = $pdo->prepare("INSERT INTO respostas (empresa_id, pergunta_id, valor_bruto, valor_ajustado, eixo_tematico) VALUES (?, ?, ?, ?, ?)");

foreach ($empresaIds as $id) {
    foreach ($eixos as $nomeEixo => $idsPerguntas) {
        foreach ($idsPerguntas as $pId) {
            
            // Adicionamos este loop para criar MÚLTIPLAS respostas por pergunta
            for ($i = 0; $i < $respostas_por_pergunta; $i++) {
                $valor_bruto = rand(1, 5);
                
                // Aplica a lógica de inversão SST
                $valor_ajustado = in_array($pId, $preguntas_negativas) ? (6 - $valor_bruto) : $valor_bruto;

                $stmtResp->execute([$id, $pId, $valor_bruto, $valor_ajustado, $nomeEixo]);
            }
        }
    }
}

echo "<strong>Sucesso Total!</strong> O banco agora contém uma amostragem real de 20 funcionários por empresa.<br>";
echo "<a href='index.php'>Ver Dashboard Atualizada</a>";