/* RESUMO WRAPPER - Colocar o Banco de Dados aqui*/
document.addEventListener('DOMContentLoaded', () => {

    const resumoScroll = document.getElementById('summaryScroll');

    if (resumoScroll) {

        let isDragging = false;
        let startX = 0;
        let scrollLeft = 0;

        resumoScroll.addEventListener('mousedown', (e) => {
            isDragging = true;
            resumoScroll.classList.add('grabbing');
            resumoScroll.style.scrollBehavior = 'auto';

            startX = e.pageX;
            scrollLeft = resumoScroll.scrollLeft;
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            e.preventDefault();

            const walk = e.pageX - startX;
            resumoScroll.scrollLeft = scrollLeft - walk;
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            resumoScroll.classList.remove('grabbing');
            resumoScroll.style.scrollBehavior = 'smooth';
        });
        resumoScroll.addEventListener('touchstart', (e) => {
            isDragging = true;
            startX = e.touches[0].pageX;
            scrollLeft = resumoScroll.scrollLeft;
        });

        // CELULAR - arrastando
        resumoScroll.addEventListener('touchmove', (e) => {
            if (!isDragging) return;

            const walk = e.touches[0].pageX - startX;
            resumoScroll.scrollLeft = scrollLeft - walk;
        });

        // CELULAR - terminou de tocar
        resumoScroll.addEventListener('touchend', () => {
            isDragging = false;
        });
    }
    });
