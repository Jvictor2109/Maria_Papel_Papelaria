let manuais = [];
// Botão de filtrar
const btnFiltrar = document.getElementById('btnFiltrar');
btnFiltrar.addEventListener('click', async ()=>{
    // Verifica se pelo menos uma combobox está preenchida
    const agrupamento = document.getElementById('filtroAgrupamento').value;
    const ano_escolar = document.getElementById('filtroAnoEscolar').value;
    const tipo_manual = document.getElementById('filtroTipoManual').value;

    manuais = await filtrarManuais(agrupamento, ano_escolar, tipo_manual);
    renderTabela(manuais);
})

// Selecionadores da tabela (campos selecionar e voucher)
const selecionarAll = document.getElementById('selecionarAll');
selecionarAll.addEventListener('change', function (){
    checkboxes = document.querySelectorAll('.checkbox-selecionar');
    checkboxes.forEach(checkbox=>{
        checkbox.checked = this.checked;
    });

    atualizarTotal();
});

const voucherAll = document.getElementById('voucherAll');
voucherAll.addEventListener('change', function (){
    checkboxes = document.querySelectorAll('.checkbox-voucher');
    checkboxes.forEach(checkbox=>{
        checkbox.checked = this.checked;
    });

    atualizarTotal();
});


// Modal de confirmar encomenda
const modalConfirmar = document.getElementById('modal-confirmar');
const closeModalConfirmar = document.getElementById('close-modal-confirmar');
closeModalConfirmar.addEventListener('click', ()=>{
    modalConfirmar.style.display = "none";
});

// Fecha o modal ao clicar fora da caixa do modal
window.addEventListener('click', function (e) {
    if (e.target === modalConfirmar) {
        modalConfirmar.style.display = 'none';
    }
});


// Botão de encomendar
const btnEncomendar = document.getElementById('btnEncomendar');
btnEncomendar.addEventListener('click', ()=>{
    // Verificar se tem algum manual selecionado
    const manuais_selecionados = document.querySelectorAll('.checkbox-selecionar:checked');

    if(manuais_selecionados.length == 0){
        mostrarMsg("red", "Selecione pelo menos 1 manual");
        return;
    }

    // Verificar se tem nome do aluno e do EE
    const nomeAluno = document.getElementById('nomeAluno').value;
    const nomeEnc = document.getElementById('nomeEnc').value;

    if(!nomeAluno){
        mostrarMsg("red", "Introduza o nome do aluno");
        return;
    }

    if(!nomeEnc){
        mostrarMsg("red", "Introduza o nome do encarregado de educação");
        return;
    }
    
    // Verificar se tem email ou telefone
    // Verificar se email tá certo
    const email = document.getElementById('email').value;
    const telemovel = document.getElementById('telemovel').value;

    if(!email && !telemovel){
        mostrarMsg("red", "Introduza pelo menos um email ou telemóvel");
        return;
    }

    if(email){
        if(/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(email.trim()) == false){
            mostrarMsg("red", "Introduza um email válido");
            return;
        }
    }

    // Verificar se existe etiquetas: tem que ter observações
    const checkEtiquetas = document.getElementById('checkEtiquetas').checked;

    if(checkEtiquetas){
        const obs_etiquetas = document.getElementById('etiquetas').value;
        if(!obs_etiquetas){
            mostrarMsg("red", "Introduza o nome para a etiqueta");
            return;
        }
    }

    // Popular o modal com as informações
    const agrupamentos = document.getElementById('filtroAgrupamento');
    const agrupamento = agrupamentos.selectedOptions[0].text;
    document.getElementById('confirmar_agrupamento').innerText = agrupamento;
    
    const anos = document.getElementById('filtroAnoEscolar');
    const ano = anos.selectedOptions[0].text;
    document.getElementById('confirmar_ano').innerText = ano;

    renderTabelaConfirmar();

    document.getElementById('confirmarTotalEncomenda').innerText = document.getElementById('totalEncomenda').innerText + "€";

    document.getElementById('confirmarCaucaoPaga').innerText = document.getElementById('caucaoPaga').value + "€";
    
    const plastManuais = document.getElementById('plastificarManuais');
    if(plastManuais.checked){
        document.getElementById('confirmarPlastManuais').innerText = "Sim";
    }
    else{
        document.getElementById('confirmarPlastManuais').innerText = "Não";
    }

    const plastLivroFichas = document.getElementById('plastificarLivroDeFichas');
    if(plastLivroFichas.checked){
        document.getElementById('confirmarPlastLivroFichas').innerText = "Sim";
    }
    else{
        document.getElementById('confirmarPlastLivroFichas').innerText = "Não";
    }

    if(checkEtiquetas){
        document.getElementById('confirmarEtiquetas').innerText = "Sim - " + document.getElementById('etiquetas').value;
    }
    else{
        document.getElementById('confirmarEtiquetas').innerText = "Não";
    }

    document.getElementById('confirmarObs').innerText = document.getElementById('observacoes').value;

    document.getElementById('confirmarAluno').innerText = document.getElementById('nomeAluno').value;
    document.getElementById('confirmarNIF').innerText = document.getElementById('nif').value;
    document.getElementById('confirmarEnc').innerText = document.getElementById('nomeEnc').value;
    document.getElementById('confirmarEmail').innerText = document.getElementById('email').value;
    document.getElementById('confirmarTelemovel').innerText = document.getElementById('telemovel').value;


    modalConfirmar.style.display = "flex";
});


const btnConfirmarEncomenda = document.getElementById('btnConfirmarEncomenda');
btnConfirmarEncomenda.addEventListener('click', ()=>{
    // FAZER DEPOIS
    alert("Daqui irá para 'confirmar_encomenda.php' e a encomenda é adicionada a base de dados");
});

const btnCancelarEncomenda = document.getElementById('btnCancelarEncomenda');
btnCancelarEncomenda.addEventListener('click', ()=>{
    modalConfirmar.style.display = "none";
})



// Tabela confirmar
function renderTabelaConfirmar(){
    const tbody = document.getElementById('tabela-confirmar');
    tbody.innerHTML = '';

    // Verifica cada linha da tabela de filtros e extrai somente os selecionados

    const tabelaFiltros = document.querySelectorAll('#tabela-filtro tr');

    tabelaFiltros.forEach(tr=>{
        const selecionado = tr.querySelector('.checkbox-selecionar');
        if(!selecionado.checked){
            return;
        }

        const linha = document.createElement('tr');
        const celulas = tr.querySelectorAll('td');

        let isbn = document.createElement('td');
        isbn.innerText = celulas[0].innerText;        
        linha.appendChild(isbn);

        let nome = document.createElement('td');
        nome.innerText = celulas[1].innerText;        
        linha.appendChild(nome);

        let preco = document.createElement('td');
        preco.innerText = celulas[2].innerText;        
        linha.appendChild(preco);

        let voucher = document.createElement('td');
        let checkvoucher = celulas[6].querySelector('input[type="checkbox"]');
        if(checkvoucher.checked == true){
            voucher.innerText = "Sim";
        }    
        else{
            voucher.innerText = "Não";
        }
        linha.appendChild(voucher);

        tbody.appendChild(linha);
    })

}


// Função que calcula o valor total da encomenda
function atualizarTotal(){
    // Pega os valores de todos os campos "selecionar" marcados
    const checkboxSelecionar = document.querySelectorAll('.checkbox-selecionar:checked');
    let caucao = 0;

    let totalSelecionar = 0;
    checkboxSelecionar.forEach(manual => {
        totalSelecionar += parseFloat(manual.dataset.preco);
        caucao += 5;
    });
    
    // Pega os valores de todos os campos "voucher" marcados
    const checkboxVoucher = document.querySelectorAll('.checkbox-voucher:checked');

    let totalVoucher = 0;
    checkboxVoucher.forEach(manual => {
        totalVoucher += parseFloat(manual.dataset.preco);
        caucao -= 5;
    });

    const total = totalSelecionar - totalVoucher;

    document.getElementById('totalEncomenda').innerText = total;
    document.getElementById('valorCaucao').innerText = caucao;
}

// Função que constroi a tabela
function renderTabela(dados) {
    const tbody = document.getElementById('tabela-filtro');
    tbody.innerHTML = '';

    // Itera sobre cada linha e adiciona à tabela
    dados.forEach(element => {
        let linha = document.createElement('tr');

        let isbn_manual = document.createElement('td');
        isbn_manual.innerText = element.isbn_manual;
        linha.appendChild(isbn_manual);

        let nome_manual = document.createElement('td');
        nome_manual.innerText = element.nome_manual;
        linha.appendChild(nome_manual);

        let preco_manual = document.createElement('td');
        preco_manual.innerText = element.preco_manual;
        linha.appendChild(preco_manual);

        let nome_disciplina = document.createElement('td');
        nome_disciplina.innerText = element.nome_disciplina;
        linha.appendChild(nome_disciplina);

        let tipo_manual = document.createElement('td');
        tipo_manual.innerText = element.tipo_manual;
        linha.appendChild(tipo_manual);

        // Campo selecionar
        let selecionar = document.createElement('td');

        let checkboxSelecionar = document.createElement('input');
        checkboxSelecionar.type = "checkbox";
        checkboxSelecionar.dataset.preco = element.preco_manual;
        checkboxSelecionar.id = 'checkSelecionar-'+element.id_manual;
        checkboxSelecionar.dataset.id_manual = element.id_manual;
        checkboxSelecionar.classList.add('checkbox-selecionar');
        checkboxSelecionar.addEventListener('change', atualizarTotal);

        let labelSelecionar = document.createElement('label');
        labelSelecionar.htmlFor = 'checkSelecionar-'+element.id_manual;

        selecionar.appendChild(checkboxSelecionar);
        selecionar.appendChild(labelSelecionar);
        linha.appendChild(selecionar);

        // Campo voucher
        let voucher = document.createElement('td');

        let checkboxVoucher = document.createElement('input');
        checkboxVoucher.type = "checkbox";
        checkboxVoucher.dataset.preco = element.preco_manual;
        checkboxVoucher.id = 'checkVoucher-'+element.id_manual;
        checkboxVoucher.classList.add('checkbox-voucher');
        checkboxVoucher.addEventListener('change', atualizarTotal);

        let labelVoucher = document.createElement('label');
        labelVoucher.htmlFor = 'checkVoucher-'+element.id_manual;

        voucher.appendChild(checkboxVoucher);
        voucher.appendChild(labelVoucher);
        linha.appendChild(voucher);


        tbody.appendChild(linha);
    })
}


// Vai buscar os manuais que correspondem aos filtros à base de dados 
async function filtrarManuais(agrupamento, ano_escolar, tipo_manual){
    let response = await fetch('encomendar_manuais.php', {
        method:"post",
        headers:{ 'Content-Type': 'application/json' },
        body: JSON.stringify({
            'acao':'filtrar_manuais',    
            'agrupamento':agrupamento,
            'ano_escolar':ano_escolar,
            'tipo_manual':tipo_manual

        })
    });

    const manuais  = await response.json();
    return manuais;    
}


function mostrarMsg(cor, conteudo) {
    const msg = document.getElementById('errorMsg');
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}