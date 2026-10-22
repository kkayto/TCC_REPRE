
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


   

/* CALENDÁRIO */
// CALENDÁRIO
    const tituloMes = document.getElementById('month');
    const diasContainer = document.getElementById('calendarDays');
    const informacoesEvento = document.getElementById('eventInfo');
    const botaoAnterior = document.getElementById('prev');
    const botaoProximo = document.getElementById('next');

    if (
        !tituloMes ||
        !diasContainer ||
        !informacoesEvento ||
        !botaoAnterior ||
        !botaoProximo
    ) {
        console.warn('Elementos do calendário não encontrados.');
        return;
    }

    const eventos = Array.isArray(window.repreEventos)
        ? window.repreEventos
        : [];

    const eventosPorData = {};

    function extrairData(valor) {
        if (!valor) return '';

        const resultado = String(valor).match(
            /^(\d{4})-(\d{2})-(\d{2})/
        );

        return resultado ? resultado[0] : '';
    }

    function dataBrasileira(data) {
        const partes = data.split('-');

        if (partes.length !== 3) return data;

        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    eventos.forEach((evento) => {
        const data = extrairData(evento.inicio);

        if (!data) return;

        if (!eventosPorData[data]) {
            eventosPorData[data] = [];
        }

        eventosPorData[data].push(evento);
    });

    const nomesMeses = [
        'Janeiro',
        'Fevereiro',
        'Março',
        'Abril',
        'Maio',
        'Junho',
        'Julho',
        'Agosto',
        'Setembro',
        'Outubro',
        'Novembro',
        'Dezembro'
    ];

    const hoje = new Date();

    let dataAtual = new Date(
        hoje.getFullYear(),
        hoje.getMonth(),
        1
    );

    function mostrarEventos(data) {
        informacoesEvento.replaceChildren();

        const lista = eventosPorData[data] || [];

        if (lista.length === 0) {
            informacoesEvento.textContent =
                `Nenhum evento em ${dataBrasileira(data)}.`;
            return;
        }

        const dataTitulo = document.createElement('strong');
        dataTitulo.textContent = dataBrasileira(data);
        informacoesEvento.appendChild(dataTitulo);

        lista.forEach((evento) => {
            const item = document.createElement('div');
            item.className = 'calendar-event-info';

            const titulo = document.createElement('strong');
            titulo.textContent = evento.titulo || 'Evento';
            item.appendChild(titulo);

            if (evento.descricao) {
                const descricao = document.createElement('p');
                descricao.textContent = evento.descricao;
                item.appendChild(descricao);
            }

            if (
                evento.inicio &&
                String(evento.inicio).length >= 16
            ) {
                const horario = document.createElement('p');
                horario.textContent =
                    `Início: ${String(evento.inicio).substring(11, 16)}`;
                item.appendChild(horario);
            }

            informacoesEvento.appendChild(item);
        });
    }

    function renderizarCalendario() {
        diasContainer.replaceChildren();

        const ano = dataAtual.getFullYear();
        const mes = dataAtual.getMonth();

        const primeiroDia = new Date(ano, mes, 1).getDay();
        const totalDias = new Date(ano, mes + 1, 0).getDate();

        tituloMes.textContent = `${nomesMeses[mes]} ${ano}`;

        // Espaços antes do primeiro dia do mês.
        for (let i = 0; i < primeiroDia; i++) {
            const vazio = document.createElement('div');
            vazio.className = 'day-empty';
            vazio.setAttribute('aria-hidden', 'true');
            diasContainer.appendChild(vazio);
        }

        for (let dia = 1; dia <= totalDias; dia++) {
            const data = [
                ano,
                String(mes + 1).padStart(2, '0'),
                String(dia).padStart(2, '0')
            ].join('-');

            const botaoDia = document.createElement('button');
            botaoDia.type = 'button';
            botaoDia.className = 'day';
            botaoDia.textContent = String(dia);
            botaoDia.setAttribute(
                'aria-label',
                dataBrasileira(data)
            );

            const dataHoje = [
                hoje.getFullYear(),
                String(hoje.getMonth() + 1).padStart(2, '0'),
                String(hoje.getDate()).padStart(2, '0')
            ].join('-');

            if (data === dataHoje) {
                botaoDia.classList.add('today');
            }

            const eventosDia = eventosPorData[data] || [];

            if (eventosDia.length > 0) {
                botaoDia.classList.add('has-event');
                botaoDia.title = eventosDia
                    .map((evento) => evento.titulo || 'Evento')
                    .join(', ');

                const indicador = document.createElement('span');
                indicador.className = 'event-dot';
                indicador.setAttribute('aria-hidden', 'true');
                botaoDia.appendChild(indicador);
            }

            botaoDia.addEventListener('click', () => {
                diasContainer
                    .querySelectorAll('.day.selected')
                    .forEach((elemento) => {
                        elemento.classList.remove('selected');
                    });

                botaoDia.classList.add('selected');
                mostrarEventos(data);
            });

            diasContainer.appendChild(botaoDia);
        }
    }

    botaoProximo.addEventListener('click', () => {
        dataAtual = new Date(
            dataAtual.getFullYear(),
            dataAtual.getMonth() + 1,
            1
        );

        renderizarCalendario();
    });

    botaoAnterior.addEventListener('click', () => {
        dataAtual = new Date(
            dataAtual.getFullYear(),
            dataAtual.getMonth() - 1,
            1
        );

        renderizarCalendario();
    });

    renderizarCalendario();
});
