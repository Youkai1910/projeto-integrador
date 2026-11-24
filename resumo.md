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

## Atualização posterior do branch — 2026-11-24

> Esta seção registra as mudanças realizadas após a atualização anterior e
> complementa os estados históricos sem reescrever o conteúdo que a precede.

### Integração entre as branches

- Foi realizada uma cópia seletiva de conteúdos da branch
  `biblia_alexandre` para `biblia_anderson`, mantendo os arquivos anteriores
  relacionados a usuários, perfis, login, cadastro e administração.
- A interface principal passou a combinar o layout da branch copiada com os
  links e sessões da implementação anterior.
- A home recebeu links reais para cadastro, login, contato, administração,
  anotações e leitura bíblica. A navegação ficou integrada a sessão atual.
- `ConexaoBD.php` passou a oferecer `getConexao()` para o banco da aplicação e
  `getConexaoBiblia()` para o banco bíblico, com nomes configuráveis por
  `DB_NAME` e `BIBLIA_DB_NAME`.
- Foram reconciliados os helpers `Utils.php` e `Validacoes.php`, que hoje
  oferecem escape HTML, tokenização, validação de perfil, pesquisa, ID,
  texto de anotação e dados públicos de cadastro.

### Identidade visual e páginas públicas

- Login, cadastro, contato e colaboração passaram a usar o tema azul da página
  inicial do Portal Bíblico.
- Os nomes visíveis foram padronizados como **Portal Bíblico**.
- O leitor recebeu o mesmo tema azul, com cabeçalho, cards, capítulos,
  versículos, navegação e rodapé consistentes com o restante do portal.
- Os menus de capítulos mobile foram testados, removidos e restaurados conforme
  os comandos sequenciais; no estado final, a grade original de capítulos foi
  preservada como solução desejada. O header da página de leitura possui menu
  recolhível no mobile e navegação original no desktop.

### Perfis e autorização

- O enum mantém os perfis `usuario`, `editor` e `admin`.
- O cadastro público cria somente contas `usuario`.
- Usuários comuns podem navegar pelo portal e gerenciar suas próprias
  anotações, mas não possuem acesso administrativo nem podem excluir a própria
  conta permanentemente.
- Editores possuem acesso administrativo parcial e acesso às anotações. Não
  podem criar outras contas, alterar perfis ou realizar exclusões permanentes.
- Administradores possuem acesso integral à área administrativa, contas,
  perfis e exclusões permanentes.
- Login, bootstrap administrativo e endpoints de criação, alteração e exclusão
  foram ajustados para conferir o perfil no servidor em vez de confiar apenas
  no conteúdo da sessão.
- `admin/biblia.php` passou a ser protegido pelo bootstrap administrativo.

### Integração Bootstrap e WordPress

- Foi criada uma camada separada em `wordpress-plugin/portal-biblico/`, sem
  alterar os arquivos do Portal Bíblico original.
- O plugin oferece o shortcode
  `[portal_biblico url="https://dominio/projeto-integrador/" altura="820px"]`
  e carrega Bootstrap responsivo.
- A integração foi criada como iframe separado, por isso não mescla sessão,
  banco, PHP ou CSS do plugin com o Portal original.

### Auditoria realizada

- Foram revistos PHP, CSS, SQL, Composer, rotas, sessões, CSRF, banco,
  anotações, escaping, autorização e estrutura responsiva.
- A sessão recebeu configuração estrita de cookie: HTTP-only, SameSite Lax,
  Secure em HTTPS e modo estrito do identificador de sessão.
- O fallback de senha de banco foi restaurado aos padrões locais anteriores,
  conforme a necessidade de funcionamento do ambiente atual, mantendo
  `DB_HOST`, `DB_NAME`, `BIBLIA_DB_NAME`, `DB_USER` e `DB_PASSWORD` como
  variáveis opcionais/sobreponíveis.
- Os dumps de livros/testamentos foram ajustados para não depender de banco
  fixo ou recriar o banco inteiro. O dump de versículos ainda precisa ser
  revisado antes da importação, pois mantém comandos de criação/destruição em
  seu cabeçalho legado.
- A documentação foi ampliada com requisitos, configuração de banco, ordem dos
  dumps e política de perfis.
- Foram validados lint PHP, autoload Composer e diagnósticos do workspace.

### Portas do ambiente atual

- Portal Bíblico: `http://localhost:890/`.
- phpMyAdmin: `http://localhost:4000/`.
- O projeto PHP não fixa portas HTTP em código ou links relativos; as portas
  são específicas do servidor local. O MySQL continua usando a conexão local
  configurada separadamente das portas HTTP.