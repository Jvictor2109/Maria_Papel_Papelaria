// Adicionar observação
const btnObs = document.getElementById('btnObs');
btnObs.addEventListener('click', async function () {
    const obs = document.getElementById('obs');
    const id_encomenda = obs.dataset.id_encomenda;

    if(!obs.value){
        mostrarMsg("red", "Introduza uma observação");
        return;
    }

    const response = await fetch('detalhe_encomenda.php', {
        method:"post",
        headers:{ 'Content-Type': 'application/json' },
        body:JSON.stringify({
            acao:"add_obs",
            id_encomenda:id_encomenda,
            obs:obs.value
        })
    });

    const data = await response.json();

    alert(data["resultado"]);
    location.reload();
}) 
 
 
 const msg = document.getElementById('msgErro');
 function mostrarMsg(cor, conteudo) {
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}