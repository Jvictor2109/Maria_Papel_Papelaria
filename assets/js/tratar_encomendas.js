// busca todas as encomendas somente 1x na base de dados
// Coloca a tabela de encomendas a tratar como ativa

let encomendas = [];
let tabela_ativa = "a_tratar";

(async ()=>{
    encomendas = await getEncomendas();
    filtrar_encomendas();
})();

// Event listener pros botoes de tipo de tabela
document.getElementById('btn_a_tratar').addEventListener('click', ()=>{
    tabela_ativa = "a_tratar";
    filtrar_encomendas();
});

document.getElementById('btn_tratadas').addEventListener('click', ()=>{
    tabela_ativa = "tratadas";
    filtrar_encomendas();
});


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
    const ids_anos_selecionados = [...document.querySelectorAll('.filtroAno:checked')].map(check => Number(check.value));    

    let encomendas_selecionadas = encomendas;

    // Filtro por tabela ativa
    if(tabela_ativa == "a_tratar"){
        encomendas_selecionadas = encomendas_selecionadas.filter(encomenda=> 
            encomenda.estado_encomenda != "concluida" &&
            encomenda.estado_encomenda != "entregue" &&
            encomenda.estado_encomenda != "cancelada"
        );
    }
    else if(tabela_ativa == "tratadas"){        
        encomendas_selecionadas  =encomendas_selecionadas.filter(encomenda=>
            encomenda.estado_encomenda == "concluida" ||
            encomenda.estado_encomenda == "entregue"
        )

        
    }

    if(ids_anos_selecionados.length > 0){
        encomendas_selecionadas = encomendas_selecionadas.filter(encomenda=>ids_anos_selecionados.includes(encomenda.id_ano_encomenda));
    }
    
    renderTabela(encomendas_selecionadas);
}
