// Controle do modal de carregar manual
// Mostrar modal
const modal = document.getElementById('modal_carregar_manuais');
const btnCarregarManual = document.getElementById('btn_carregar_manual');
btnCarregarManual.addEventListener('click', function (e){
    e.preventDefault();
    modal.style.display ="flex";

})
// Fechar manual
const btnCloseModal = document.getElementById('close-modal');
btnCloseModal.addEventListener('click', ()=>{
    modal.style.display = "none";
})
// Fecha o modal ao clicar fora da caixa do modal
window.addEventListener('click', function (e) {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});


// Validar para aceitar apenas números no ISBN e preço
document.getElementById('isbn').addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});
document.getElementById('preco_manual').addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/[^0-9.,]/g, '');
});

// Event listener pra quando digitar no campo ISBN
document.getElementById('isbn').addEventListener('keyup', (e) => {
    const form = document.querySelector('.form-add-manual');
    const isbn = e.target.value.trim();
    if (e.target.value.length > 0) {
        form.style.display = "flex";
        form.querySelectorAll('input, select').forEach(el => el.disabled = false);
    }
    else {
        form.style.display = "none";
    }

    if (isbn.length == 10 || isbn.length == 13) {
        fetch('gestao_manual.php', {
            method: "post",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ acao: "checar_isbn", isbn: isbn })
        }).then(response => response.json())
            .then(data => {
                if (data["resultado"] == true) {
                    mostrarMsg("red", "ISBN já existente na base de dados");
                    // desativar os campos
                    form.querySelectorAll('input, select').forEach(el => el.disabled = true);
                }
            });
    }
});

// Event listener de adicionar manual
const btn_guardar_manual = document.getElementById('btn_guardar_manual');
btn_guardar_manual.addEventListener('click', () => {

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
    if (isbn.length != 10 && isbn.length != 13) {
        mostrarMsg("red", "ISBN inválido");
        return;
    }

    if (!nome_manual) {
        mostrarMsg("red", "Falta o campo Nome do manual");
        return;
    }

    if(preco_manual <= 0 || isNaN(preco_manual)){
        mostrarMsg("red", "Preço do manual é invalido");
        return;
    }

    if(!editora){
        mostrarMsg("red", "Falta o campo editora");
        return;
    }

    if(!disciplina){
        mostrarMsg("red", "Falta o campo disciplina");
        return;
    }

    if(!tipo_manual){
        mostrarMsg("red", "Falta o campo Tipo de manual");
        return;
    }

    if(agrupamentos.length == 0){
        mostrarMsg("red", "Selecione pelo menos 1 agrupamento");
        return;
    }

    if(anos_escolares.length == 0){
        mostrarMsg("red", "Selecione pelo menos 1 ano escolar");
        return;
    }

    // Montar o JSON pro fetch
    let ids_agrupamentos = [];
    agrupamentos.forEach(agp => {
        ids_agrupamentos.push(agp.value);
    })

    let ids_anos_escolares = [];
    anos_escolares.forEach(ano => {
        ids_anos_escolares.push(ano.value);
    })

    const dados = {
        "acao": "adicionar",
        "isbn": isbn,
        "nome_manual": nome_manual,
        "cod_manual": cod_manual,
        "preco_manual": preco_manual,
        "editora": editora,
        "disciplina": disciplina,
        "tipo_manual": tipo_manual,
        "agrupamentos": ids_agrupamentos,
        "anos_escolares": ids_anos_escolares
    }

    // Envia pro PHP
    fetch('gestao_manual.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)

    }).then(response => response.json())
        .then(data => {
            if (data["resultado"] == "sucesso") {
                mostrarMsg("green", data["msg"]);
            }
            else if (data["resultado"] == "erro") {
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


// Event listener de adicionar Excel com manuais
const submit_manuais_bulk = document.getElementById('submit_manuais_bulk');
submit_manuais_bulk.addEventListener('click', ()=>{
    const xlsx = document.getElementById('xlsx_manuais');
    const formData = new FormData();
    formData.append('xlsx', xlsx.files[0]);
    formData.append('acao', 'upload_xlsx');

    fetch('carregar_manuais.php', {
        method:"post",
        body:formData
    }).then(response => response.json())
    .then(data =>{
        if(data['resultado'] == "sucesso"){
            window.location.href = "carregar_manuais.php";
        }
        else{
            const modalError = document.getElementById('modalError');
            modalError.style.color = "red";
            modalError.innerText = `${data['msg']}`;

            setTimeout(() => {
                modalError.innerText = "";
            }, 2000);

        }
    })
})


// Função que popula a tabela
function carregarTabela() {
    let dados = {};

    fetch('gestao_manual.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "listar_manuais" })
    }).then(response => response.json())
        .then(data => {
            dados = data;
            renderTabela(dados);
        });
}


// Função que constroi a tabela
function renderTabela(dados) {
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    // Itera sobre cada linha e adiciona à tabela
    dados.forEach(element => {
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
            method: "post",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ "acao": "checar_anos_escolares", "id_manual": element.id_manual })
        }).then(response => response.json())
            .then(data => {
                let anos_escolares = document.createElement('td');
                anos_escolares.innerText = data.resultado.join('\n');
                linha.appendChild(anos_escolares);
            });

        tbody.appendChild(linha);
    })
}



// Função mensagem de erro
// A mensagem só sai caso altere o campo
const msgErro = document.getElementById('msgErro');
document.querySelectorAll('input').forEach(el=>{
    el.addEventListener('input', ()=>{
        msgErro.style.display = "none";
    })
})
document.querySelectorAll('select').forEach(el=>{
    el.addEventListener('change', ()=>{
        msgErro.style.display = "none";
    })
})

function mostrarMsg(cor, conteudo) {
    msgErro.style.color = cor;
    msgErro.innerText = conteudo;
    msgErro.style.display = "flex";

    // Se for mensagem de sucesso, deve desaparecer dps
    if(cor == "green"){
        setTimeout(() => {
            msgErro.style.display = "none"
        }, 2000);
    }
}














