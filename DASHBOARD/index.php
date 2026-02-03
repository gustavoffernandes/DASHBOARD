<?php
require 'config.php';

// --- 1. Lógica de Dados (Mantida Intacta para Integridade do Sistema) ---
$empresa_ids = $_GET['empresa_ids'] ?? [1];
$questao_selecionada = $_GET['pergunta_id'] ?? 1;
$tipo_grafico = $_GET['tipo_grafico'] ?? 'bar';

$empresas_lista = $pdo->query("SELECT * FROM empresas")->fetchAll();

$placeholders = implode(',', array_fill(0, count($empresa_ids), '?'));
$sql_transversal = "SELECT empresa_id, eixo_tematico, AVG(valor_ajustado) as media 
                    FROM respostas 
                    WHERE empresa_id IN ($placeholders) 
                    GROUP BY empresa_id, eixo_tematico";

$stmt = $pdo->prepare($sql_transversal);
$stmt->execute($empresa_ids);
$resultados_brutos = $stmt->fetchAll();

$series_radar = [];
foreach ($empresa_ids as $id) {
    $nome_emp = "Empresa $id";
    foreach ($empresas_lista as $e) if ($e['id'] == $id) $nome_emp = $e['nome'];
    $medias_eixos = [0, 0, 0, 0]; 
    foreach ($resultados_brutos as $row) {
        if ($row['empresa_id'] == $id) {
            $mapa = ['Contexto' => 0, 'Gestao' => 1, 'Vivencias' => 2, 'Danos' => 3];
            $medias_eixos[$mapa[$row['eixo_tematico']]] = round($row['media'], 2);
        }
    }
    $series_radar[] = ['name' => $nome_emp, 'data' => $medias_eixos];
}

$primeira_empresa = $empresa_ids[0];
$sql_questao = "SELECT valor_bruto, COUNT(*) as total FROM respostas WHERE empresa_id = ? AND pergunta_id = ? GROUP BY valor_bruto ORDER BY valor_bruto ASC";
$stmt = $pdo->prepare($sql_questao);
$stmt->execute([$primeira_empresa, $questao_selecionada]);
$distribuicao = $stmt->fetchAll();

$valores_detalhado = [0, 0, 0, 0, 0];
foreach ($distribuicao as $d) {
    $valores_detalhado[$d['valor_bruto'] - 1] = (int)$d['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROATIVA | Inteligência em Gestão de SST</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: radial-gradient(circle at 50% 50%, #1e293b 0%, #0f172a 100%); }
        .glass-effect { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.8); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    <nav class="fixed top-0 z-50 w-full bg-slate-900/95 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 h-20 flex justify-between items-center text-white">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <span class="text-slate-900 font-bold text-xl">P</span>
                </div>
                <span class="text-2xl font-bold tracking-tight italic">PROATIVA</span>
            </div>
            <div class="hidden md:flex gap-8 text-sm font-medium">
                <a href="#inicio" class="hover:text-emerald-400 transition-colors">Início</a>
                <a href="#beneficios" class="hover:text-emerald-400 transition-colors">Benefícios</a>
                <a href="#analise" class="hover:text-emerald-400 transition-colors">Painel Analítico</a>
            </div>
            <a href="#analise" class="px-5 py-2.5 bg-emerald-600 text-white rounded-full hover:bg-emerald-500 transition-all text-sm font-bold shadow-lg shadow-emerald-900/40">Acessar Sistema</a>
        </div>
    </nav>

    <section id="inicio" class="hero-shape hero-gradient pt-48 pb-32 px-4 relative overflow-hidden">
        <div class="max-w-7xl mx-auto text-center relative z-10">
            <span class="inline-block py-1 px-3 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-bold tracking-widest uppercase mb-6 border border-emerald-500/20">
                Liderança em Monitoramento Ocupacional
            </span>
            <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight mb-8">
                Inteligência de Dados para uma <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-blue-400">Gestão de SST Impecável</span>
            </h1>
            <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-10 italic">
                Transforme questionários complexos em insights visuais imediatos. Monitore o bem-estar ocupacional com precisão científica e tome decisões baseadas em dados reais.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#analise" class="px-10 py-5 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-500 transition-all transform hover:-translate-y-1 shadow-xl shadow-emerald-900/40">
                    Ver Painel de Análise
                </a>
                <a href="#beneficios" class="px-10 py-5 bg-white/10 text-white border border-white/20 rounded-2xl font-bold hover:bg-white/20 transition-all">
                    Descobrir Benefícios
                </a>
            </div>
        </div>
    </section>

    <section id="beneficios" class="py-24 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">A excelência técnica que sua gestão merece</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">A PROATIVA combina metodologia científica com tecnologia de ponta para entregar o panorama mais preciso da saúde ocupacional.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-12">
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold">Precisão nos Dados</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Algoritmos de inversão de score que eliminam distorções, garantindo que maiores índices sempre reflitam melhor saúde organizacional.</p>
                </div>
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold">Conformidade com Normas</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Mapeamento estruturado nos eixos fundamentais de SST, facilitando auditorias e o cumprimento de requisitos legais de segurança.</p>
                </div>
                <div class="space-y-4">
                    <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1a1 1 0 112 0v1a1 1 0 11-2 0zM13.536 14.95a1 1 0 011.414-1.414l.707.707a1 1 0 01-1.414 1.414l-.707-.707zM14.95 5.05a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold">Visão Transversal</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Análise comparativa multienquadramento que permite identificar discrepâncias entre unidades e estabelecer benchmarks internos.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="analise" class="py-24 px-4 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto">
            <div class="text-center md:text-left mb-12">
                <h2 class="text-3xl font-bold text-slate-900">Painel Analítico Transversal</h2>
                <p class="text-slate-500 italic mt-2">Selecione as unidades para comparar indicadores em tempo real.</p>
            </div>

            <div class="glass-effect rounded-[2.5rem] p-8 mb-12 shadow-xl">
                <form method="GET" class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <?php foreach($empresas_lista as $e): ?>
                        <label class="flex items-center gap-3 px-6 py-3 rounded-2xl border-2 transition-all cursor-pointer <?= in_array($e['id'], $empresa_ids) ? 'bg-slate-900 border-emerald-500 text-white shadow-lg' : 'bg-white border-slate-100 text-slate-600 hover:border-emerald-200' ?>">
                            <input type="checkbox" name="empresa_ids[]" value="<?= $e['id'] ?>" 
                                   <?= in_array($e['id'], $empresa_ids) ? 'checked' : '' ?> 
                                   onchange="this.form.submit()" class="hidden">
                            <span class="text-sm font-bold"><?= htmlspecialchars($e['nome']) ?></span>
                        </label>
                    <?php endforeach; ?>
                    <input type="hidden" name="pergunta_id" value="<?= $questao_selecionada ?>">
                    <input type="hidden" name="tipo_grafico" value="<?= $tipo_grafico ?>">
                </form>
            </div>

            <div class="grid lg:grid-cols-12 gap-8 mb-12">
                <div class="lg:col-span-8 glass-effect p-8 rounded-[2.5rem] shadow-xl">
                    <h3 class="text-xl font-bold text-slate-800 mb-8 border-l-4 border-emerald-500 pl-4 uppercase tracking-tight">Comparativo de Eixos Temáticos</h3>
                    <div id="chart-radar"></div>
                </div>
                <div class="lg:col-span-4 bg-slate-900 text-white p-8 rounded-[2.5rem] shadow-2xl flex flex-col justify-center">
                    <h4 class="text-emerald-400 font-bold mb-4 uppercase text-xs tracking-widest">Metodologia de Análise</h4>
                    <p class="text-slate-400 text-sm leading-relaxed italic">
                        A sobreposição métrica permite identificar instantaneamente qual unidade apresenta maior exposição a riscos psicossociais e danos à saúde.
                    </p>
                    <div class="mt-8 p-4 bg-white/5 rounded-2xl border border-white/10 text-xs text-slate-400">
                        <strong>Nota Técnica:</strong> Os dados são consolidados a partir de amostragem real e processados via PDO para máxima segurança e integridade.
                    </div>
                </div>
            </div>

            <div class="glass-effect rounded-[2.5rem] p-10 shadow-xl">
                <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
                    <h3 class="text-2xl font-bold text-slate-800 uppercase tracking-tighter italic">Frequência por Item</h3>
                    <form method="GET" class="flex flex-wrap gap-4 justify-center">
                        <?php foreach($empresa_ids as $id): ?><input type="hidden" name="empresa_ids[]" value="<?= $id ?>"><?php endforeach; ?>
                        <select name="pergunta_id" onchange="this.form.submit()" class="bg-slate-100 border-none py-3 px-6 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-emerald-500">
                            <?php for($i=1; $i<=60; $i++): ?><option value="<?= $i ?>" <?= $i == $questao_selecionada ? 'selected' : '' ?>>Item <?= $i ?></option><?php endfor; ?>
                        </select>
                        <select name="tipo_grafico" onchange="this.form.submit()" class="bg-slate-100 border-none py-3 px-6 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="bar" <?= $tipo_grafico == 'bar' ? 'selected' : '' ?>>📊 Barras</option>
                            <option value="line" <?= $tipo_grafico == 'line' ? 'selected' : '' ?>>📈 Linhas</option>
                            <option value="pie" <?= $tipo_grafico == 'pie' ? 'selected' : '' ?>>🍕 Pizza</option>
                        </select>
                    </form>
                </div>
                <div id="chart-detalhado"></div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 py-20 px-4 text-slate-300">
        <div class="max-w-7xl mx-auto grid md:grid-cols-4 gap-12 border-b border-slate-800 pb-16 mb-10 text-center md:text-left">
            <div class="space-y-6">
                <div class="flex items-center justify-center md:justify-start gap-2 text-white font-bold text-2xl italic">PROATIVA</div>
                <p class="text-sm opacity-60 leading-relaxed">Referência em soluções para Saúde e Segurança do Trabalho, transformando dados em proteção e qualidade de vida.</p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-6">Links Rápidos</h4>
                <ul class="text-sm space-y-4 opacity-70">
                    <li><a href="#inicio" class="hover:text-emerald-400 transition-colors">Início</a></li>
                    <li><a href="#beneficios" class="hover:text-emerald-400 transition-colors">Benefícios</a></li>
                    <li><a href="#analise" class="hover:text-emerald-400 transition-colors">Painel Analítico</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-6">Contato</h4>
                <ul class="text-sm space-y-4 opacity-70 italic">
                    <li>contato@proativa.com.br</li>
                    <li>(93) 9999-9999</li>
                    <li>Santarém, Pará</li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-6">Redes Sociais</h4>
                <div class="flex justify-center md:justify-start gap-4">
                    <div class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-emerald-600 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </div>
                    <div class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-emerald-600 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.23 0H1.77C.8 0 0 .8 0 1.77v20.46C0 23.2.8 24 1.77 24h20.46c.97 0 1.77-.8 1.77-1.77V1.77C24 .8 23.2 0 22.23 0zM7.27 20.1H3.65V9.24h3.62V20.1zM5.47 7.76c-1.14 0-2.05-.91-2.05-2.05 0-1.14.91-2.05 2.05-2.05 1.14 0 2.05.91 2.05 2.05 0 1.14-.91 2.05-2.05 2.05zm14.63 12.34h-3.62v-5.64c0-1.34-.03-3.07-1.87-3.07-1.88 0-2.17 1.46-2.17 2.97v5.74h-3.62V9.24h3.48v1.56h.05c.48-.92 1.67-1.89 3.44-1.89 3.68 0 4.36 2.42 4.36 5.57v5.62z"/></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center text-xs opacity-40 uppercase tracking-[0.2em]">
            © 2026 PROATIVA SST • Dashboard Analítico • PIAPE/UFOPA
        </div>
    </footer>

    <script>
        // Configurações Globais ApexCharts
        window.Apex = { chart: { fontFamily: 'Inter, sans-serif', toolbar: { show: false } } };

        // RADAR TRANSVERSAL
        new ApexCharts(document.querySelector("#chart-radar"), {
            series: <?= json_encode($series_radar) ?>,
            chart: { height: 450, type: 'radar', background: 'transparent' },
            colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
            stroke: { width: 3 },
            fill: { opacity: 0.15 },
            markers: { size: 4 },
            xaxis: { 
                categories: ['Contexto', 'Gestão', 'Vivências', 'Danos'],
                labels: { style: { colors: '#64748b', fontWeight: 600 } }
            },
            yaxis: { max: 5, stepSize: 1, labels: { show: false } }
        }).render();

        // GRÁFICO DETALHADO DINÂMICO
        new ApexCharts(document.querySelector("#chart-detalhado"), {
            chart: { 
                type: '<?= $tipo_grafico ?>', 
                height: 400,
                animations: { enabled: true }
            },
            series: <?= ($tipo_grafico === 'pie') ? json_encode($valores_detalhado) : "[{ name: 'Total de Respostas', data: " . json_encode($valores_detalhado) . " }]" ?>,
            labels: ['Péssimo', 'Ruim', 'Médio', 'Bom', 'Excelente'],
            colors: ['#ef4444', '#f59e0b', '#94a3b8', '#10b981', '#3b82f6'],
            plotOptions: { 
                bar: { borderRadius: 10, columnWidth: '40%', distributed: true },
                line: { curve: 'smooth' }
            },
            stroke: { width: <?= ($tipo_grafico === 'line') ? 4 : 0 ?>, curve: 'smooth' },
            legend: { position: 'bottom', fontWeight: 600 }
        }).render();
    </script>
</body>
</html>