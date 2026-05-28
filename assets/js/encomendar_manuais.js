// Botão de filtrar
const btnFiltrar = document.getElementById('btnFiltrar');
btnFiltrar.addEventListener('click', async ()=>{
    // Verifica se pelo menos uma combobox está preenchida
    const agrupamento = document.getElementById('filtroAgrupamento').value;
    const ano_escolar = document.getElementById('filtroAnoEscolar').value;
    const tipo_manual = document.getElementById('filtroTipoManual').value;

    const manuais = await filtrarManuais(agrupamento, ano_escolar, tipo_manual);
    renderTabela(manuais);
})

// Selecionadores da tabela (campos selecionar e voucher)
const selecionarAll = document.getElementById('selecionarAll');
selecionarAll.addEventListener('change', function (){
    checkboxes = document.querySelectorAll('.checkbox-selecionar');
    checkboxes.forEach(checkbox=>{
        checkbox.checked = this.checked;
    })
});

const voucherAll = document.getElementById('voucherAll');
voucherAll.addEventListener('change', function (){
    checkboxes = document.querySelectorAll('.checkbox-voucher');
    checkboxes.forEach(checkbox=>{
        checkbox.checked = this.checked;
    })
});


// Função que calcula o valor total da encomenda
function calcularTotal(){
    // Pega os valores de todos os campos "selecionar" marcados
    const checkboxSelecionar = document.querySelectorAll('.checkbox-selecionar:checked');

    let totalSelecionar = 0;
    checkboxSelecionar.forEach(manual => {
        totalSelecionar += parseFloat(manual.dataset.preco);
    });
    
    // Pega os valores de todos os campos "voucher" marcados
    const checkboxVoucher = document.querySelectorAll('.checkbox-voucher:checked');

    let totalVoucher = 0;
    checkboxVoucher.forEach(manual => {
        totalVoucher += parseFloat(manual.dataset.preco);
    });

    let total = totalSelecionar - totalVoucher;
}

// Função que constroi a tabela
function renderTabela(dados) {
    const tbody = document.querySelector('tbody');
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
        checkboxSelecionar.classList.add('checkbox-selecionar');
        checkboxSelecionar.addEventListener('change', calcularTotal);

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
        checkboxVoucher.addEventListener('change', calcularTotal);

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
    const msg = document.getElementById('ErroFiltrar');
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}