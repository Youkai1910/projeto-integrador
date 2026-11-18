# Resumo da branch `biblia_alexandre`

Este resumo inclui somente o trabalho exclusivo desta branch em relação à
`main`. Nenhuma outra branch foi alterada.

## Página inicial

- Foi criada a página inicial do Portal Diário em `index.php`, com navegação,
  pesquisa, cards de conteúdo, lista de livros e rodapé.
- A página busca os nomes dos livros na tabela existente `books` e escapa o
  conteúdo exibido com `htmlspecialchars()`.
- Foi criado `css/style.css` com o estilo da página e ajustes responsivos para
  telas menores.

## Imagens

- Foram adicionadas imagens para o logotipo, leitura diária, Novo Testamento,
  Antigo Testamento, Salmos, versículos, Evangelho, autoajuda e uma imagem hero.
- O logotipo e as imagens dos cards foram ligados à página usando os arquivos
  em `biblia_catolica/imagens/`.
- Os cards usam uma camada escura sobre as imagens para manter o texto legível.

## Prompt

- Foi adicionado `prompts.md` para guardar o prompt de desenvolvimento da Bíblia.

## Verificações

- A sintaxe PHP de `index.php` foi verificada.
- Os arquivos de imagem referenciados na página foram confirmados como
  existentes.
- A conexão real com o banco e a aparência no navegador não foram verificadas
  nesta etapa.
