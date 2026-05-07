// Carrega a tabela assim que carrega a pagina
carregarTabela();


// Validar para aceitar apenas números no ISBN
document.getElementById('isbn').addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});

// Event listener pra quando digitar no campo ISBN
document.getElementById('isbn').addEventListener('keyup', (e) =>{
    const form = document.querySelector('.form-add-manual');
    const isbn = e.target.value.trim();
    if(e.target.value.length > 0 ){
        form.style.display = "flex";
        form.querySelectorAll('input, select').forEach(el => el.disabled = false);
    }
    else{
        form.style.display = "none";
    }

    if(isbn.length == 10 || isbn.length == 13){
        fetch('gestao_manual.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({acao:"checar_isbn", isbn:isbn})
        }).then(response =>response.json())
        .then(data=>{
            if(data["resultado"] == true){                
                mostrarMsg("red", "ISBN já existente na base de dados");
                // desativar os campos
                form.querySelectorAll('input, select').forEach(el => el.disabled = true);
            }
        });
    }
});

// Event listener de adicionar manual
const btn_guardar_manual = document.getElementById('btn_guardar_manual');
btn_guardar_manual.addEventListener('click', ()=>{

    // Pega cada um dos campos e verifica se estão preenchidos
    const isbn = document.getElementById('isbn').value;
    const nome_manual = document.getElementById('nome_manual').value;
    const cod_manual = document.getElementById('codigo_manual').value;
    const preco_manual = parseFloat(document.getElementById('preco_manual').value.replace(',', '.'));
    const editora = document.getElementById('editora').value;
    const disciplina = document.getElementById('disciplina').value;
    const tipo_manual = document.getElementById('tipo_manual').value;

    // Checkboxes
    const agrupamentos = document.querySelectorAll('.checkbox_agrupamento:checked');
    const anos_escolares = document.querySelectorAll('.checkbox_ano_escolar:checked');
    

    // Verificar os campos vazios
    if (!nome_manual || preco_manual <= 0 || !editora || !disciplina || !tipo_manual || 
        agrupamentos.length == 0 || anos_escolares.length == 0 ||(isbn.length != 10 && isbn.length != 13) || isNaN(preco_manual)){
        mostrarMsg("red", "Verifique os campos");
        return;
    }

    // Montar o JSON pro fetch
    let ids_agrupamentos = [];
    agrupamentos.forEach(agp =>{
        ids_agrupamentos.push(agp.value);
    })

    let ids_anos_escolares = [];
    anos_escolares.forEach(ano =>{
        ids_anos_escolares.push(ano.value);
    })

    const dados = {
        "acao":"adicionar",
        "isbn":isbn,
        "nome_manual":nome_manual,
        "cod_manual":cod_manual,
        "preco_manual":preco_manual,
        "editora":editora,
        "disciplina":disciplina,
        "tipo_manual":tipo_manual,
        "agrupamentos":ids_agrupamentos,
        "anos_escolares":ids_anos_escolares
    }

    // Envia pro PHP
    fetch('gestao_manual.php', {
        method:"post",
        headers:{ 'Content-Type': 'application/json' },
        body:JSON.stringify(dados)

    }).then(response =>response.json())
    .then(data =>{
        if(data["resultado"] == "sucesso"){
            mostrarMsg("green", data["msg"]);
        }
        else if(data["resultado"] == "erro"){
            mostrarMsg("red", data["msg"]);
        }
        
    }); 

    // Limpa os inputs e esconde o formulário após a adição
    const textInputs = document.querySelectorAll('input[type="text"]');
        textInputs.forEach(input => {
            input.value = "";
        });

    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    
    const selects = document.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
        });

    document.querySelector('.form-add-manual').style.display = "none";
});


// Função que popula a tabela
function carregarTabela(){
    let dados = {};

    fetch('gestao_manual.php', {
        method:"post",
        headers:{ 'Content-Type': 'application/json' },
        body:JSON.stringify({"acao":"listar_manuais"})
    }).then(response => response.json())
    .then(data =>{
        dados = data;
        console.log(dados);
        
        renderTabela(dados);
    });

    
    
}


// Função que constroi a tabela
function renderTabela(dados){
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    // Itera sobre cada linha e adiciona à tabela
    dados.forEach(element =>{
        let linha = document.createElement('tr');

        let id_manual = document.createElement('td');
        id_manual.innerText = element.id_manual;
        linha.appendChild(id_manual);

        let isbn_manual = document.createElement('td');
        isbn_manual.innerText = element.isbn_manual;
        linha.appendChild(isbn_manual);
        
        let nome_manual = document.createElement('td');
        nome_manual.innerText = element.nome_manual;
        linha.appendChild(nome_manual);
        
        let preco_manual = document.createElement('td');
        preco_manual.innerText = element.preco_manual;
        linha.appendChild(preco_manual);
        
        let cod_manual = document.createElement('td');
        cod_manual.innerText = element.cod_manual;
        linha.appendChild(cod_manual);
        
        let nome_editora = document.createElement('td');
        nome_editora.innerText = element.nome_editora;
        linha.appendChild(nome_editora);

        // Vai ao servidor buscar todos os anos escolares associados ao livro
        fetch('gestao_manual.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({"acao":"checar_anos_escolares", "id_manual":element.id_manual})
        }).then(response => response.json())
        .then(data =>{
            let anos_escolares = document.createElement('td');
            anos_escolares.innerText = data.resultado.join('\n');
            linha.appendChild(anos_escolares);            
        });

        tbody.appendChild(linha);
    })
}


// Função mensagem de erro
const msgErro = document.getElementById('msgErro');
function mostrarMsg(cor, conteudo) {
    msgErro.style.color = cor;
    msgErro.innerText = conteudo;

    setTimeout(() => {
        msgErro.innerText = "";
    }, 2000)
}














