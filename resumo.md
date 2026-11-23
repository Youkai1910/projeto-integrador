# Resumo da branch biblia_marcelo

## Trabalho realizado

- Integração da interface do Portal Diário no `index.php`.
- Separação dos estilos em `css/style.css`.
- Adição de layout responsivo para desktop, tablet e celular.
- Uso de Font Awesome para os ícones da navegação e do rodapé.
- Inclusão das imagens da Bíblia em `biblia_catolica/imagens/`.
- Associação das imagens aos cartões de leitura diária, Novo Testamento,
  Antigo Testamento, Salmos, Versículos, Evangelho e Auto Ajuda.
- Inclusão de atributos de acessibilidade nas imagens e na navegação.
- Uso de carregamento otimizado nas imagens da página.

## Estrutura da página

- Cabeçalho com logo e menu de navegação.
- Barra de pesquisa no cabeçalho.
- Três cartões principais para leitura diária e Testamentos.
- Quatro cartões inferiores para Salmos, Versículos, Evangelho e Auto Ajuda.
- Lista dos livros da Bíblia consultados no banco de dados.
- Rodapé com descrição, links rápidos e contatos.

## Consulta ao banco

O `index.php` mantém a consulta existente à tabela `books` e preserva o escape
de saída com `htmlspecialchars()`.

## Imagens adicionadas

- `logo.png`
- `leitura-diaria.jpg`
- `novo-testamento.jpg`
- `antigo-testamento.jpg`
- `salmos.jpg`
- `versiculos.jpg`
- `evangelho.jpg`
- `auto-ajuda.jpg`
- `hero.jpg`

## Validações realizadas

- Sintaxe PHP do `index.php` validada.
- Arquivos de imagem referenciados confirmados no diretório do projeto.
- Página e arquivo CSS testados em servidor PHP local com respostas HTTP 200.

## Arquivos principais desta branch

- `index.php`
- `css/style.css`
- `biblia_catolica/imagens/`
- `prompts.md`
- `resumo.md`
