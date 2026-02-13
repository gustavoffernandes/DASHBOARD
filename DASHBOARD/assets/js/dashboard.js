document.addEventListener('DOMContentLoaded', function () {

    // ===============================
    // 1. Gráfico de Rosca (Setores)
    // ===============================
    const ctxSide = document.getElementById('sectorDoughnutChart').getContext('2d');

    new Chart(ctxSide, {
        type: 'doughnut',
        data: {
            labels: ['Operacional', 'Adm', 'Vendas', 'RH'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: ['#4318ff', '#6ad2ff', '#eff4fb', '#1b2559'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });

    // ===============================
    // 2. Event listeners do gráfico de detalhe
    // ===============================
    document.querySelectorAll('.btn-chart-type').forEach(botao => {
        botao.addEventListener('click', function() {

            // Estética: mudar botão ativo
            document.querySelectorAll('.btn-chart-type')
                .forEach(b => b.classList.remove('active'));

            this.classList.add('active');

            // Mudar tipo e renderizar
            currentChartType = this.getAttribute('data-type');
            renderizarDetalhePergunta();
        });
    });

    const selectPergunta = document.getElementById('selectPergunta');
    if (selectPergunta) {
        selectPergunta.addEventListener('change', renderizarDetalhePergunta);
    }

    // ===============================
    // 3. Carregar dados iniciais
    // ===============================
    atualizarDashboard();
    renderizarDetalhePergunta(); // já carrega a primeira pergunta ao abrir
});


// ======================================
// VARIÁVEIS DO GRÁFICO DE DETALHE
// ======================================
let detalheChartInstance = null;
let currentChartType = 'bar'; // padrão


// ======================================
// FUNÇÃO PRINCIPAL DE SINCRONIZAÇÃO
// ======================================
async function atualizarDashboard() {
    try {
        const response = await fetch('api.php?action=get_dashboard_data');
        const data = await response.json();

        // ===============================
        // 1. Atualizar Cards de KPI
        // ===============================
        document.querySelector('.kpi-card:nth-child(1) .kpi-value').innerText =
            data.kpis.total_respondentes;

        document.querySelector('.kpi-card:nth-child(2) .kpi-value').innerText =
            data.kpis.media_satisfacao;

        document.querySelector('.kpi-card:nth-child(3) .kpi-value').innerText =
            data.kpis.empresas_ativas;

        // ===============================
        // 2. Atualizar Gráfico Principal
        // ===============================
        const ctx = document.getElementById('mainAnalyticsChart').getContext('2d');

        if (window.myChart) {
            window.myChart.destroy();
        }

        window.myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.benchmark.labels,
                datasets: [{
                    label: 'Média de Satisfação',
                    data: data.benchmark.valores,
                    backgroundColor: '#4318ff',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 10 },
                    x: { grid: { display: false } }
                }
            }
        });

    } catch (error) {
        console.error("Erro ao sincronizar dashboard:", error);
    }
}


// ======================================
// RENDERIZAÇÃO DO DETALHE DA PERGUNTA
// ======================================
async function renderizarDetalhePergunta() {
    const select = document.getElementById('selectPergunta');
    if (!select) return;

    const perguntaId = select.value;

    try {
        const response = await fetch(`api.php?action=get_pergunta_detalhe&pergunta_id=${perguntaId}`);
        const data = await response.json();

        const canvas = document.getElementById('perguntaDetalheChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (detalheChartInstance) {
            detalheChartInstance.destroy();
        }

        detalheChartInstance = new Chart(ctx, {
            type: currentChartType,
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Quantidade de Respostas',
                    data: data.valores,
                    backgroundColor: [
                        '#4318ff', '#6ad2ff', '#31c48d', '#ff5a5f', '#f1c40f'
                    ],
                    borderWidth: (currentChartType === 'pie' ? 1 : 0)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: (currentChartType === 'pie' || currentChartType === 'radar')
                    }
                }
            }
        });

    } catch (error) {
        console.error("Erro ao carregar detalhe:", error);
    }
}
