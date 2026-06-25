const CORES_ESTADOS = {
    'registada': 'darkred',
    'pedida':    'orange',
    'concluida': 'goldenrod',
    'entregue':  'green',
    'cancelada': 'red'
};

async function get_encomendas_ano() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_encomendas_ano" })
    });
    return await response.json();
}

async function get_encomendas_estado() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_encomendas_estado" })
    });
    return await response.json();
}

async function renderGrafico() {
    const dados = await get_encomendas_ano();

    const labels = dados.map(d => d.nome_ano_escolar);
    const quantidades = dados.map(d => parseInt(d.quantidade));

    const ctx = document.getElementById('grafico');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nº de Encomendas',
                data: quantidades,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

async function renderCardsEstado() {
    const dados = await get_encomendas_estado();
    const container = document.getElementById('estado-cards');

    const quantidades = {};
    dados.forEach(d => {
        quantidades[d.estado_encomenda] = parseInt(d.quantidade);
    });

    const estados = Object.keys(CORES_ESTADOS);

    container.innerHTML = estados.map(estado => {
        const cor = CORES_ESTADOS[estado];
        const quantidade = quantidades[estado] ?? 0;
        return `
            <div class="estado-card" style="border-color: ${cor}; color: ${cor};">
                <span class="estado-nome">${estado}</span>
                <span class="estado-count">${quantidade}</span>
            </div>`;
    }).join('');
}

renderGrafico();
renderCardsEstado();

