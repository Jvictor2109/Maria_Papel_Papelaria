// Event listener pro botao
const btnCarregarManuais = document.getElementById('btnCarregarManuais');
btnCarregarManuais.addEventListener('click', ()=>{
    // Pegar os agrupamentos e anos escolares
    const agrupamentos = document.querySelectorAll('.checkbox_agrupamento:checked');
    const anos_escolares = document.querySelectorAll('.checkbox_ano_escolar:checked');

    let ids_agrupamentos = [];
    agrupamentos.forEach(agp => {
        ids_agrupamentos.push(agp.value);
    });

    let ids_anos_escolares = [];
    anos_escolares.forEach(ano => {
        ids_anos_escolares.push(ano.value);
    });

    if(ids_agrupamentos.length == 0){
        // TODO: MENSAGEM DE ERRO
        return;
    }
    if(ids_anos_escolares.length == 0){
        // TODO: MENSAGEM DE ERRO
        return;
    }

    // Iterar sobre a tabela e salvar os manuais num json
    const tabela = document.getElementById('tabela');
    let manuais = [];
    
    for(let i = 1; i < tabela.rows.length; i++){
        const isbn = tabela.rows[i].cells[0].textContent.trim();
        const nome_manual = tabela.rows[i].cells[1].textContent.trim();
        const codigo_manual = tabela.rows[i].cells[2].textContent.trim();
        const preco_manual = tabela.rows[i].cells[3].textContent.trim();
        
        // Verifica as checkboxes
        const editora = tabela.rows[i].cells[4].querySelector('select').value;
        const disciplina = tabela.rows[i].cells[5].querySelector('select').value;
        const tipo_manual = tabela.rows[i].cells[6].querySelector('select').value;


        if(!editora){
            // TODO: MENSAGEM DE ERRO
            return;
        }
        if(!disciplina){
            // TODO: MENSAGEM DE ERRO
            return;
        }
        if(!tipo_manual){
            // TODO: MENSAGEM DE ERRO
            return;
        }

        manuais[i-1] = {
            "isbn":isbn,
            "nome_manual":nome_manual,
            "codigo_manual":codigo_manual,
            "preco_manual":preco_manual,
            "editora":editora,
            "disciplina":disciplina,
            "tipo_manual":tipo_manual,
            "agrupamentos":ids_agrupamentos,
            "anos_escolares":ids_anos_escolares
        };
    }

    // Construir formData e mandar fetch
    const formData = new FormData();
    formData.append('acao', 'carregar_manuais');
    formData.append('manuais', JSON.stringify(manuais));

    fetch('carregar_manuais.php', {
        method:"post",
        body:formData
    }).then(response=>response.json())
    .then(data=>{
        alert(data['msg']);
    })
    
})