function exportarExcel(){
    let tabela = document.getElementById('tabela');
    let alteracoes = {}

    // Itera sobre cada linha e salva as informações de iva e de manter o item
    for(let i = 1; i < tabela.rows.length; i++){
        let row = tabela.rows[i];
        if(!row.classList.contains('linha-artigo')) continue; // Ignorar a linha de filtros, se existir

        let id = parseInt(row.cells[0].dataset.id);
        let manter = row.cells[8].querySelector('input').checked;
        
        // Se a linha estiver escondida pelo filtro, não vamos exportá-la
        if (row.style.display === 'none') {
            manter = false;
        }

        let iva = row.cells[7].querySelector('select').value;
        iva = parseFloat(iva);
        
        // Adicionar as alterações no objeto
        alteracoes[id] = {
            "manter":manter,
            "iva":iva
        }
        
    }
    
    // Envia request pro exportador (agora excel) enviando os dados
    fetch('exportarExcel.php', {
        method: "POST",
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(alteracoes)
    }).then(response => response.json())
      .then(data => {
            if(data['sucesso']==true){
                 // Criar link invisível para download (não navega fora da página)
                const link = document.createElement('a');
                link.href = 'exportarExcel.php';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                document.getElementById('modal-sucesso').style.display = "flex";
            }
        });
}

function alterarIva(select, preco){
    let iva = parseFloat(select.value);
    let preco_sIva = document.getElementById('pvp_sIva_'+preco);
    let preco_bruto = parseFloat(preco_sIva.dataset.pvpbruto);
    let preco_custo_com_iva = document.getElementById('preco_com_iva_'+preco);
    let preco_custo = parseFloat(preco_custo_com_iva.dataset.precocomiva);
    
    preco_custo_com_iva.innerText = (preco_custo/(1+iva)).toFixed(2)+"€";
    preco_sIva.innerText = (preco_bruto/(1+iva)).toFixed(2)+"€";    
}

function filtrarPorData() {
    let checkboxes = document.querySelectorAll('.filtro-data');
    let datasSelecionadas = [];
    
    checkboxes.forEach(cb => {
        if(cb.checked) {
            datasSelecionadas.push(cb.value);
        }
    });

    let linhas = document.querySelectorAll('.linha-artigo');
    linhas.forEach(linha => {
        let dataLinha = linha.dataset.data_distribuicao;
        if(dataLinha == '' || datasSelecionadas.includes(dataLinha)){
            linha.style.display = '';
        } else {
            linha.style.display = 'none';
        }
    });
}