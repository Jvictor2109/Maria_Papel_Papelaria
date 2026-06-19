const btnSalvar = document.getElementById('salvar_alteracoes');
const id_encomenda = btnSalvar.dataset.id_encomenda;

btnSalvar.addEventListener('click', async function(){
    const manuais = document.querySelectorAll('.manualSeparado:checked');

    if(manuais.length == 0){
        mostrarMsg("red", "Selecione pelo menos 1 manual");
        return;
    }

    let dados = [];
    manuais.forEach(manual=>{
        dados.push({
            id_manual: manual.id,
            separado: true
        });
    });

    const response = await fetch('editar_encomenda.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            dados: dados,
            id_encomenda: id_encomenda
        })
    });

    const data = await response.json();
    alert(data["msg"]);
    location.href = 'tratar_encomendas.php';
});

const msg = document.getElementById('msgErro');
function mostrarMsg(cor, conteudo) {
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}