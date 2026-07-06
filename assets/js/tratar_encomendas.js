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
    document.getElementById('btnAvisar').style.display = "none";
    filtrar_encomendas();
});

document.getElementById('btn_tratadas_por_avisar').addEventListener('click', ()=>{
    tabela_ativa = "tratadas_por_avisar";
    document.getElementById('btnAvisar').style.display = "flex";
    filtrar_encomendas();
});
document.getElementById('btn_tratadas_avisadas').addEventListener('click', ()=>{
    tabela_ativa = "tratadas_avisadas";
    document.getElementById('btnAvisar').style.display = "none";
    filtrar_encomendas();
});

// Event listener pro botao de avisar
const btnAvisar = document.getElementById('btnAvisar');
btnAvisar.addEventListener('click', async function(){
    try{
        const response = await fetch('tratar_encomendas.php', {
            method:"post",
            headers:{'Content-type':'application/json'},
            body:JSON.stringify({
                "acao":"avisar"
            })
        });

        if(!response.ok){        
            throw new Error('Erro ao gerar pdf');
        }

        const blob = await response.blob();

        const url = window.URL.createObjectURL(blob);
        
        const download = document.createElement('a');
        download.href = url;
        download.download = 'encomendas_a_avisar.pdf';
        document.body.appendChild(download);
        download.click();

        document.body.removeChild(download);
        window.URL.revokeObjectURL(url);

    }
    catch(erro){
        console.log("Erro - " + erro)
        alert("Não foi possível gerar o pdf");
        return;
    }

    alert("Encomendas avisadas com sucesso");
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
function renderTabela_a_tratar(encomendas){
    // Altera o cabeçalho
    const thead = document.querySelector('thead');
    const headers = ["Id", "Num. encomenda", "Data da encomenda", "Dias em espera", " "];

    thead.innerHTML = '';
    const linha_cabecalho = document.createElement('tr');

    headers.forEach(header=>{
        const th = document.createElement('th');
        th.innerText = header
        linha_cabecalho.appendChild(th);
    })

    thead.appendChild(linha_cabecalho);


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

// Renderiza a tabela de encomendas por avisar
function renderTabela_por_avisar(encomendas){
    // Altera o cabeçalho
    const thead = document.querySelector('thead');
    const headers = ["Id", "Num. encomenda", "Data", "Email", "Telefone", " "];

    thead.innerHTML = '';
    const linha_cabecalho = document.createElement('tr');

    headers.forEach(header=>{
        const th = document.createElement('th');
        th.innerText = header
        linha_cabecalho.appendChild(th);
    })

    thead.appendChild(linha_cabecalho);

    // Limpa o tbody e constroi a tabela
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    const num_encomendas_tratar = document.getElementById('num_encomendas_tratar');
    num_encomendas_tratar.innerText = encomendas.length;

    encomendas.forEach(encomenda=>{
        const linha = document.createElement('tr');

        const id = document.createElement('td');
        id.innerText = encomenda.id_encomenda;
        linha.appendChild(id);

        const num_encomenda = document.createElement('td');
        num_encomenda.innerText = encomenda.num_encomenda;
        linha.appendChild(num_encomenda);

        const data_encomenda = document.createElement('td');
        data_encomenda.innerText = encomenda.data_encomenda;
        linha.appendChild(data_encomenda);

        const email = document.createElement('td');
        if(encomenda.email){
            email.innerText = encomenda.email;
        }
        else{
            email.innerText = "-";
        }
        linha.appendChild(email)

        const telefone = document.createElement('td');
        if(encomenda.telefone_encomenda){
            telefone.innerText = encomenda.telefone_encomenda;
        }
        else{
            telefone.innerText = "-";
        }
        linha.appendChild(telefone);

        const detalhes_encomenda = document.createElement('td');
        const link_encomenda = document.createElement('a');
        
        link_encomenda.href = `detalhe_encomenda.php?id=${encomenda.id_encomenda}`;
        link_encomenda.innerText = 'Ver encomenda';
        detalhes_encomenda.appendChild(link_encomenda);
        linha.appendChild(detalhes_encomenda);

        tbody.appendChild(linha);
    });

}
function renderTabela_avisadas(encomendas){
    // Altera o cabeçalho
    const thead = document.querySelector('thead');
    const headers = ["Id", "Num. encomenda", "Data aviso", "Utilizador", " "];

    thead.innerHTML = '';
    const linha_cabecalho = document.createElement('tr');

    headers.forEach(header=>{
        const th = document.createElement('th');
        th.innerText = header
        linha_cabecalho.appendChild(th);
    })

    thead.appendChild(linha_cabecalho);

    // Limpa o tbody e constroi a tabela
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    const num_encomendas_tratar = document.getElementById('num_encomendas_tratar');
    num_encomendas_tratar.innerText = encomendas.length;

    encomendas.forEach(encomenda=>{
        const linha = document.createElement('tr');

        const id = document.createElement('td');
        id.innerText = encomenda.id_encomenda;
        linha.appendChild(id);

        const num_encomenda = document.createElement('td');
        num_encomenda.innerText = encomenda.num_encomenda;
        linha.appendChild(num_encomenda);

        const data_encomenda = document.createElement('td');
        data_encomenda.innerText = encomenda.data_encomenda;
        linha.appendChild(data_encomenda);

       const user_avisado = document.createElement('td');
       user_avisado.innerText = encomenda.username;
       linha.appendChild(user_avisado);
       
        const detalhes_encomenda = document.createElement('td');
        const link_encomenda = document.createElement('a');
        
        link_encomenda.href = `detalhe_encomenda.php?id=${encomenda.id_encomenda}`;
        link_encomenda.innerText = 'Ver encomenda';
        detalhes_encomenda.appendChild(link_encomenda);
        linha.appendChild(detalhes_encomenda);

        tbody.appendChild(linha);
    });

}

// Filtrar encomendas
async function filtrar_encomendas(){
    const ids_anos_selecionados = [...document.querySelectorAll('.filtroAno:checked')].map(check => Number(check.value));    

    let encomendas_selecionadas = encomendas;
    

    if(ids_anos_selecionados.length > 0){
        encomendas_selecionadas = encomendas_selecionadas.filter(encomenda=>ids_anos_selecionados.includes(encomenda.id_ano_encomenda));
    }

    console.log(encomendas_selecionadas);
    // Filtro por tabela ativa
    if(tabela_ativa == "a_tratar"){
        encomendas_selecionadas = encomendas_selecionadas.filter(encomenda=> 
            encomenda.estado_encomenda == "registada" ||
            encomenda.estado_encomenda == "pedida"
        );

        renderTabela_a_tratar(encomendas_selecionadas);
    }
    else if(tabela_ativa == "tratadas_por_avisar"){        
        encomendas_selecionadas  =encomendas_selecionadas.filter(encomenda=>
            encomenda.estado_encomenda == "concluida" && encomenda.avisado == 0
        );        
        renderTabela_por_avisar(encomendas_selecionadas);
        console.log(encomendas_selecionadas);
        
    }
    else if(tabela_ativa == "tratadas_avisadas"){
        encomendas_selecionadas = encomendas_selecionadas.filter(encomenda=>
            encomenda.estado_encomenda == "concluida" && encomenda.avisado == 1
        );        
        renderTabela_avisadas(encomendas_selecionadas);
    }
}
