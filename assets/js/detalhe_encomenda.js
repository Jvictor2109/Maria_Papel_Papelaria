// Adicionar observação
const btnObs = document.getElementById('btnObs');
if(btnObs){
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
    });
}


// Botao de entregar
const btnEntregar = document.getElementById('btnEntregar');
if(btnEntregar){
    btnEntregar.addEventListener('click', ()=>{
        document.getElementById('modalEntregar').style.display = "flex";
    });
}

// Fechar modal de entregar
const btnFecharConfirmar = document.getElementById('fecharConfirmar');
if(btnFecharConfirmar){
    btnFecharConfirmar.addEventListener('click', ()=>{
        document.getElementById('modalEntregar').style.display = "none";
    });
}

// Confirmar entrega
const btnConfirmarEntregar = document.getElementById('confirmarEntregar');
if(btnConfirmarEntregar){
    btnConfirmarEntregar.addEventListener('click', async function (){
        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({
                "acao":"entregar_encomenda",
                "id_encomenda":btnConfirmarEntregar.dataset.id_encomenda
            })
        })

        const data = await response.json();

        if(data["resultado"] == "sucesso"){
            alert(data["msg"]);
            window.location.href = "index.php";
        }
        else{
            alert(data["msg"]);
            location.reload();
        }
    });
}
 
 
 const msg = document.getElementById('msgErro');
 function mostrarMsg(cor, conteudo) {
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}