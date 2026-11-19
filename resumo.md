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

## Consulta da Bíblia

- `src/Services/BibliaServico.php` consulta testamentos, livros, capítulos e
  versículos; os modelos correspondentes ficam em `src/Models/`.
- A página inicial e `admin/biblia.php` usam `Validacoes` para os parâmetros de
  busca e `Utils::escaparHtml()` para o conteúdo exibido.

## Anotações

- `src/Models/Anotacao.php` representa os campos da tabela `anotacoes`.
- `src/Services/AnotacaoServico.php` implementa listagem, criação, edição,
  favoritos e remoção, sempre limitando as operações ao usuário proprietário.
- `admin/anotacoes.php` requer `$_SESSION['usuario_id']`, protege os formulários
  com token CSRF e escapa o conteúdo apresentado.
- O login deve preencher `$_SESSION['usuario_id']` com o ID positivo do usuário.
- A validação do texto exige conteúdo não vazio, UTF-8 válido e limite de
  65.535 bytes.

## Banco de dados e funcionalidades pendentes

- O script de criação das tabelas de usuários e anotações está em
  `biblia_catolica/tabelas_usuarios_anotacoes.sql`.
- `admin/usuarios.php` permanece sem implementação; os arquivos relacionados
  aos usuários não foram alterados nesta branch.

## Verificações

- `php -l` passou nos arquivos PHP alterados para as anotações.
- Foram verificadas a validação do texto, o autoload PSR-4 e a hidratação do
  modelo `Anotacao`.
- `composer validate` confirmou a validade da configuração, com aviso opcional
  de licença não informada.
- Não foi feito teste integrado com o banco de dados nem verificação visual no
  navegador.
