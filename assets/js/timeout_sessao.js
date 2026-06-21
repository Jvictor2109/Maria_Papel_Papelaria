let tempo_limite = 5 * 60 * 1000 // Minutos para milissegundos
let timer;

function iniciarContagem(){
    clearTimeout(timer);
    timer = setTimeout(()=>{
        alert("Sessão expirada por inatividade.")
        window.location.href = "logout.php";
    }, tempo_limite);   
}

// document.addEventListener("mousemove", iniciarContagem);
// document.addEventListener("keypress", iniciarContagem);
// document.addEventListener("click", iniciarContagem);

// iniciarContagem();