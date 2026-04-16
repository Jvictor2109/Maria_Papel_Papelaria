// Lida somente com mostrar/esconder o modal de edição
const modalGestao = document.getElementById('modal-gestao');
const closeModal = document.getElementById('close-modal');

if (closeModal) {
    closeModal.addEventListener('click', function () {
        modalGestao.style.display = 'none';
    });
}

window.addEventListener('click', function (e) {
    if (e.target === modalGestao) {
        modalGestao.style.display = 'none';
    }
});
