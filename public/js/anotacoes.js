(() => {
    const filtros = document.getElementById('filtros-anotacoes');
    const busca = document.getElementById('busca-anotacoes');
    const filtroFavoritos = document.getElementById('filtro-anotacoes');
    const contagem = document.getElementById('contagem-anotacoes');
    const semResultados = document.getElementById('nenhuma-anotacao-encontrada');
    const anotacoes = Array.from(document.querySelectorAll('.anotacao-card'));

    if (!filtros || !busca || !filtroFavoritos || !contagem || !semResultados) {
        return;
    }

    const normalizar = (texto) => texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('pt-BR');

    const atualizarLista = () => {
        const termo = normalizar(busca.value.trim());
        const somenteFavoritos = filtroFavoritos.value === 'favoritos';
        let visiveis = 0;

        anotacoes.forEach((anotacao) => {
            const texto = anotacao.querySelector('.anotacao-texto').textContent;
            const correspondeAoTexto = normalizar(texto).includes(termo);
            const correspondeAoFavorito = !somenteFavoritos || anotacao.dataset.favorito === '1';
            const corresponde = correspondeAoTexto && correspondeAoFavorito;

            anotacao.hidden = !corresponde;
            if (corresponde) {
                visiveis += 1;
            }
        });

        const rotulo = anotacoes.length === 1 ? 'anotação' : 'anotações';
        contagem.textContent = `Exibindo ${visiveis} de ${anotacoes.length} ${rotulo}.`;
        semResultados.hidden = visiveis > 0;
    };

    busca.addEventListener('input', atualizarLista);
    filtroFavoritos.addEventListener('change', atualizarLista);
    filtros.hidden = false;
    atualizarLista();
})();
