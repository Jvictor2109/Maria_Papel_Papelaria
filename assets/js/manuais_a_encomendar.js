// Primeiro botão de confirmar, embaixo da tabela
const btnConfirmar = document.getElementById('btnConfirmarEncomenda');
btnConfirmar.addEventListener('click', () => {
    const modal = document.getElementById('modal-gestao');
    modal.style.display = "flex";
});


// Botão do modal de confirmar
const btnModal = document.getElementById('btnConfirmarSenha');
btnModal.addEventListener('click', async function () {
    const pass = document.getElementById('pass_confirmar').value;

    if(!pass){
        mostrarMsg("red","Introduza uma palavra passe");
        return;
    }

   const response = await fetch('manuais_a_encomendar.php', {
        method:"post",
        headers: { 'Content-Type': 'application/json' },
        body:JSON.stringify({
            "acao":"pedir_manuais",
            "pass":pass
        })
   });

   const data = await response.json();
   const msg = data["msg"];

   if(data["resultado"] == "sucesso"){
        alert(msg);
        // window.location.href = "index.php";
   }
   else{
        mostrarMsg("red", data["msg"]);
        return;
   }
});


const msg = document.getElementById('msgErro');
function mostrarMsg(cor, conteudo) {
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}