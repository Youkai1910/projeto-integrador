# Resumo do projeto até agora

## Arquivos e organização

- Os arquivos de conexão e criação das tabelas estão na pasta `database/`.
- O caminho de importação do script SQL foi atualizado no `README.md`.

## Banco de dados

O arquivo `database/tabelas_usuarios_anotacoes.sql` seleciona o banco
`biblia_catolica` e cria:

- `usuarios`, com nome, senha (que deve guardar apenas o hash), email único e
  perfil (`usuario`, `editor` ou `admin`). O perfil padrão é `usuario`.
- `anotacoes`, com texto, favorito e vínculo obrigatório com um usuário. Um
  usuário pode ter várias anotações; ao excluir o usuário, suas anotações também
  são excluídas.

O script não cria contas automaticamente. Ele também não adiciona um vínculo
entre uma anotação e um livro da Bíblia (`livro_id`).

## Conexão e página inicial

- `database/conexao.php` contém a classe final `ConexaoBD`, que cria e reutiliza
  uma conexão PDO por meio de `getConexao()`.
- `index.php` usa essa classe e lista os livros consultando a tabela `books`,
  ordenada pela coluna `id`. O texto exibido é escapado com
  `htmlspecialchars()`.
- A senha do banco ainda tem um valor padrão no arquivo de conexão. Antes de
  publicar o projeto, configure `DB_PASSWORD` no ambiente e retire esse padrão.

## Estado atual

A sintaxe PHP de `index.php` e `database/conexao.php` foi verificada. O script
SQL foi preparado para importar pelo phpMyAdmin; a conexão real com o banco não
foi testada nesta etapa.

Ainda não foram implementados cadastro/login, autorização dos perfis ou uma
tela para criar e consultar anotações. A coluna `tipo` registra o perfil, mas a
aplicação deverá verificar esse perfil no servidor antes de permitir ações
administrativas ou de edição.

## Atualização do branch `biblia_anderson` — 2026-11-19

> Esta seção complementa o registro inicial acima e prevalece sobre as
> observações antigas que dizem que a conexão está em `database/` ou que
> cadastro e login ainda não existem.

### Organização atual

- `index.php`: página inicial, pesquisa de livros e atalhos para leitura,
  cadastro, login, contato e colaboração.
- `src/Database/ConexaoBD.php`: conexões PDO; `getConexao()` usa `DB_NAME` (padrão
  `biblia_catolica`) e `getConexaoBiblia()` usa `BIBLIA_DB_NAME`, com fallback
  para `DB_NAME` e depois `biblia_catolica`.
- `src/Services/UsuarioServico.php`: cadastro, autenticação por hash, busca e
  operações administrativas de contas.
- `src/Models/Usuario.php` e `src/Enum/TipoUsuario.php`: entidade de leitura e
  perfis válidos (`usuario`, `editor` e `admin`).
- `src/Helpers/Seguranca.php`, `Utils.php` e `Validacoes.php`: sessão/CSRF,
  escaping e funções comuns de validação/normalização.
- `src/Services/LeituraBiblicaServico.php`: consultas de livros, capítulos,
  versículos e versículo aleatório.
- `admin/_bootstrap.php` valida a sessão e o perfil no banco. `admin/usuarios.php`
  lista e pesquisa; `admin/usuario-insere.php`, `usuario-atualiza.php` e
  `usuario-exclui.php` executam as operações administrativas via POST e CSRF.
- `css/style.css`, `css/admin-usuarios.css` e `css/leitura.css` contêm estilos
  da página inicial, administração e leitura; `css/publico.css` atende os
  formulários públicos.

### Fluxos disponíveis

- `cadastro.php` cria conta pública somente com perfil `usuario`; `login.php`
  verifica `password_hash()` com `password_verify()`, regenera a sessão e
  direciona administradores à área de gestão. `logout.php` encerra a sessão via
  POST protegido por CSRF.
- `contato.php` e `colaborador.php` aceitam mensagens com validação e token
  CSRF. O envio depende de `MAIL_TO`, `MAIL_FROM` e do serviço de e-mail do PHP;
  se indisponível, mostram o endereço de contato alternativo.
- `leitura.php` permite pesquisar e abrir livros, escolher capítulos, ler seus
  versículos, navegar entre capítulos/livros e abrir um versículo aleatório.
  A página inicial também possui links clicáveis para os livros e atalhos de
  leitura.
- O banco local foi conferido: `biblia_catolica` contém `usuarios`, `anotacoes`,
  `books`, `testament` e `verses`; a tabela `books` tem 73 livros e `verses`
  tem 33.276 registros no ambiente verificado.

### Notas de ambiente e limites

- Os dumps bíblicos estão em `biblia_catolica/`. A ordem recomendada é
  `biblia_tabelas.sql.sql`, `biblia_testamento_livros.sql.sql` e
  `biblia_versiculos.sql.sql`. Eles não devem apagar o banco inteiro; os dumps
  de dados substituem o conteúdo da tabela que carregam.
- O código não fixa as portas HTTP 650, 4000 ou 890. No ambiente verificado,
  650 e 4000 serviram o Portal Diário e 890 serviu o phpMyAdmin.
- A aplicação possui fallback local de credenciais na conexão. Para outros
  ambientes, configure `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` e,
  opcionalmente, `BIBLIA_DB_NAME`; evite publicar credenciais de desenvolvimento.
- O projeto não cria automaticamente a primeira conta administradora. Ela deve
  ser provisionada pelo responsável pelo banco; o cadastro público não concede
  privilégios administrativos.
- Os testes executados nesta etapa incluíram lint PHP, carregamento PSR-4,
  consultas locais de leitura e requisições GET às páginas. Não foi enviado
  formulário de cadastro, contato ou colaboração durante a validação.