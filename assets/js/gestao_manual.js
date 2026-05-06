// Event listener pra quando digitar no campo ISBN
document.getElementById('isbn').addEventListener('keyup', (e) =>{
    const form = document.querySelector('.form-add-manual');
    if(e.target.value.length > 0 ){
        form.style.display = "flex";
    }
    else{
        form.style.display = "none";
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

    console.log(dados);
    
});

// Função mensagem de erro
const msgErro = document.getElementById('msgErro');
function mostrarMsg(cor, conteudo) {
    msgErro.style.color = cor;
    msgErro.innerText = conteudo;

    setTimeout(() => {
        msgErro.innerText = "";
    }, 2000)
}
