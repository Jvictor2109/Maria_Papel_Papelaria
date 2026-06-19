filtrar_encomendas();

// Event listener pra todos os botões de filtro
const btnsFiltro = document.querySelectorAll('.filtroAno');
btnsFiltro.forEach(btn=>{
    btn.addEventListener('change', filtrar_encomendas);
})


// Buscar todas as encomendas por tratar à base de dados
async function getEncomendas(){
    const response = await fetch('tratar_encomendas.php', {
        method:"post",
        headers: { 'Content-Type': 'application/json' },
        body:JSON.stringify({
            acao : "get_encomendas"
        })
    });

    const data = await response.json();

    return data.encomendas;
}


// Renderizar tabelas
async function renderTabela(encomendas){
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    const num_encomendas_tratar = document.getElementById('num_encomendas_tratar');
    num_encomendas_tratar.innerText = encomendas.length;

    encomendas.forEach(encomenda => {
        const linha = document.createElement('tr');

        const id_encomenda = document.createElement('td');
        id_encomenda.innerText = encomenda.id_encomenda;
        linha.appendChild(id_encomenda);

        const num_encomenda = document.createElement('td');
        num_encomenda.innerText = encomenda.num_encomenda;
        linha.appendChild(num_encomenda);
        
        const data_encomenda = document.createElement('td');
        data_encomenda.innerText = encomenda.data_encomenda;
        linha.appendChild(data_encomenda);

        const dias_espera = document.createElement('td');
        dias_espera.innerText = encomenda.datediff;
        linha.appendChild(dias_espera);

        
        const detalhes_encomenda = document.createElement('td');
        const link_encomenda = document.createElement('a');

        link_encomenda.href = `editar_encomenda.php?id=${encomenda.id_encomenda}`;
        link_encomenda.innerText = 'Editar encomenda';

        detalhes_encomenda.appendChild(link_encomenda);
        linha.appendChild(detalhes_encomenda);

        tbody.appendChild(linha);
    });
}

// Filtrar encomendas
async function filtrar_encomendas(){
    const encomendas = await getEncomendas();

    const ids_anos_selecionados = [...document.querySelectorAll('.filtroAno:checked')].map(check => Number(check.value));

    let encomendas_selecionadas;
    if(ids_anos_selecionados.length == 0){
        encomendas_selecionadas = encomendas;
    }
    else{
        // Pra cada encomenda, verifica se o id do ano escolar dela está dentre os ids selecionados
        // Filtra somente as que estão
        encomendas_selecionadas = encomendas.filter(encomenda => ids_anos_selecionados.includes(encomenda.id_ano_encomenda));
    }

    renderTabela(encomendas_selecionadas);
}