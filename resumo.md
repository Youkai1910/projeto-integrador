# Resumo da branch `biblia_alexandre`

Este resumo inclui somente o trabalho exclusivo desta branch em relação à
`main`. Nenhuma outra branch foi alterada.

## Página inicial

- Foi criada a página inicial do Portal Diário em `index.php`, com navegação,
  pesquisa, cards de conteúdo, lista de livros e rodapé.
- `index.php` carrega `vendor/autoload.php` e usa a classe de conexão
  `ProjetoIntegrador\Database\ConexaoBD`, em `src/Database/ConexaoBD.php`.
- A página busca os nomes dos livros na tabela existente `books` e escapa o
  conteúdo exibido com `htmlspecialchars()`.
- Foi criado `css/style.css` com o estilo da página e ajustes responsivos para
  telas menores.

## Composer

- `composer.json` exige PHP 7.4 ou superior e configura o autoload PSR-4 de
  `ProjetoIntegrador\` para `src/`.
- `composer.lock` registra o estado das dependências; atualmente não há pacotes
  externos declarados.
- Use `composer install` na raiz para preparar o projeto. `vendor/` é gerado
  localmente e não é versionado.

## Imagens

- Foram adicionadas imagens para o logotipo, leitura diária, Novo Testamento,
  Antigo Testamento, Salmos, versículos, Evangelho, autoajuda e uma imagem hero.
- O logotipo e as imagens dos cards foram ligados à página usando os arquivos
  em `images/`.
- Os cards usam uma camada escura sobre as imagens para manter o texto legível.

## Prompt

- Foi adicionado `prompts.md` para guardar o prompt de desenvolvimento da Bíblia.

## Banco de dados e funcionalidades pendentes

- O script de criação das tabelas de usuários e anotações está em
  `biblia_catolica/tabelas_usuarios_anotacoes.sql`.
- `admin/usuarios.php` está vazio; a funcionalidade administrativa ainda não foi
  implementada.

## Verificações

- A sintaxe PHP de `index.php` e da classe de conexão foi verificada.
- O autoload do Composer foi gerado e a classe de conexão foi carregada com
  sucesso pelo mapeamento PSR-4.
- `composer validate` confirmou a validade da configuração, com aviso opcional
  de licença não informada.
- Os arquivos de imagem referenciados na página foram confirmados como
  existentes.
- A conexão real com o banco e a aparência no navegador não foram verificadas
  nesta etapa.
