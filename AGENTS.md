# Guia de desenvolvimento do projeto-integrador

## Criar as tabelas de usuários e anotações

No phpMyAdmin, selecione o banco `biblia_catolica`, abra a aba **Importar** e
importe `biblia_catolica/tabelas_usuarios_anotacoes.sql`. O script cria:

- `usuarios`: nome, senha, email e tipo. O email é único.
- `tipo` aceita somente `usuario`, `editor` ou `admin`; novas contas recebem
  `usuario` por padrão. O script não cria contas automaticamente.
- `anotacoes`: anotação, indicador de favorito e vínculo com o usuário; cada
  usuário pode ter várias anotações.

Armazene em `usuarios.senha` somente o hash gerado por `password_hash()` do PHP,
nunca a senha em texto puro. A aplicação também deve verificar o tipo do usuário
no servidor antes de permitir ações administrativas ou de edição; o valor no
banco, sozinho, não controla permissões.

`CREATE TABLE IF NOT EXISTS` não altera tabelas que já existam. Se essas tabelas
já foram criadas, a estrutura existente precisará de uma alteração específica
para restringir `tipo` aos três perfis.

## Banco bíblico e configuração local

Por padrão, os dados bíblicos, usuários e anotações usam o banco
`biblia_catolica`. Importe os arquivos nesta ordem: `biblia_tabelas.sql.sql`,
`biblia_testamento_livros.sql.sql` e `biblia_versiculos.sql.sql`, todos da pasta
`biblia_catolica/`. Os scripts não removem o banco inteiro; os dois últimos
substituem os dados da tabela que carregam. Se `BIBLIA_DB_NAME` apontar para
outro banco, importe os arquivos bíblicos nesse banco em vez de
`biblia_catolica`.

A conexão padrão do PHP é `localhost` (porta MySQL padrão). Configure
`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` (padrão `biblia_catolica`) e
`BIBLIA_DB_NAME` (padrão igual a `DB_NAME`, normalmente `biblia_catolica`) conforme o ambiente.
O ambiente atual utiliza o Portal Bíblico em `http://localhost:890/` e o
phpMyAdmin em `http://localhost:4000/`. As portas HTTP são específicas do
servidor local e não devem ser fixadas no código PHP ou nos links relativos.

O cadastro público cria somente contas do tipo `usuario`. O envio dos
formulários de contato e colaboração usa `MAIL_TO` e `MAIL_FROM` e requer que o
PHP esteja configurado para enviar e-mail; sem essas variáveis, o site informa
que o envio não está disponível e oferece o endereço de contato.

`login.php` autentica contas pelo hash de senha e abre a sessão usada pela área
administrativa. O primeiro administrador precisa ser provisionado pelo
responsável pelo banco; o cadastro público nunca concede esse perfil.

## Organização das funcionalidades

- `src/Database/ConexaoBD.php` fornece `getConexao()` para o banco de aplicação
  (`DB_NAME`, padrão `biblia_catolica`) e `getConexaoBiblia()` para o banco
  bíblico (`BIBLIA_DB_NAME`, padrão igual a `DB_NAME`, com fallback para
  `biblia_catolica`). As configurações de host e credenciais são compartilhadas.
- `src/Services/UsuarioServico.php` concentra consultas e regras de usuários;
  recebe uma conexão PDO no construtor. `Models/Usuario.php` representa os dados
  públicos da conta, sem expor o hash da senha, e `Enum/TipoUsuario.php` define
  os três perfis permitidos.
- `src/Helpers/Seguranca.php` deve ser usado para iniciar sessões, validar CSRF,
  escapar saída HTML e emitir mensagens temporárias. `Utils.php` reúne
  utilitários sem estado; `Validacoes.php` centraliza validação de dados.
- A autenticação pública está em `login.php`; `logout.php` aceita apenas POST
  com token CSRF. O cadastro em `cadastro.php` sempre cria perfil `usuario`.
- O painel `admin/usuarios.php` e os controladores
  `admin/usuario-insere.php`, `admin/usuario-atualiza.php` e
  `admin/usuario-exclui.php` incluem `admin/_bootstrap.php`. O bootstrap exige
  sessão com `$_SESSION['usuario']['id']` e confirma o perfil atual no banco.
  Controladores de alteração devem continuar aceitando somente POST e CSRF.
- `src/Services/LeituraBiblicaServico.php` consulta `books`, `testament` e
  `verses`; `leitura.php` apresenta livros, capítulos e versículos. Use consultas
  preparadas para parâmetros de usuário e escape toda saída dinâmica.
- `contato.php` e `colaborador.php` encaminham mensagens por
  `src/Services/ContatoServico.php`. Configure `MAIL_TO` e `MAIL_FROM` e o
  transporte de e-mail do PHP; as páginas precisam continuar oferecendo o
  endereço `contato@portalbiblico.com` quando o envio não estiver configurado.

## Navegação e validação

Os links da página inicial devem apontar para rotas existentes; evite deixar
`href="#"` ou formulários sem destino funcional. Os livros exibidos na home
devem abrir `leitura.php?livro=<id>`; a pesquisa usa `q`, e o leitor usa
`livro`, `capitulo`, `testamento` ou `aleatoria=1`.

Não fixe portas HTTP no código ou na documentação da aplicação: elas pertencem
ao servidor de desenvolvimento. No ambiente local podem existir servidores em
650 e 4000 para o site e em 890 para o phpMyAdmin, mas isso não é configuração
portável do projeto.

Antes de importar SQL, confira o banco selecionado. Os dumps bíblicos não devem
conter comandos que apaguem o banco inteiro; execute-os na ordem descrita neste
guia e saiba que os arquivos de carga substituem os dados das tabelas
correspondentes.

Depois de alterar PHP, valide com `php -l` nos arquivos afetados e verifique o
autoload PSR-4. Testes de rotas e consultas locais são úteis, mas não substituem
testes funcionais com banco de desenvolvimento e formulários reais controlados.

## Integração e perfis confirmados no branch atual

- A interface principal combina o layout da branch bíblica com autenticação,
  cadastro, logout, anotações e área administrativa da implementação anterior.
- Links relativos devem continuar apontando para as rotas existentes do Portal
  Bíblico. Não fixe `localhost:890` ou `localhost:4000` em código PHP, CSS,
  JavaScript ou formulários.
- `TipoUsuario` aceita `usuario`, `editor` e `admin`.
- Contas públicas recebem somente `usuario`. Perfis privilegiados devem ser
  provisados por administrador autorizado ou pelo responsável pelo banco.
- `admin/_bootstrap.php` deve aceitar somente administradores e editores como
  sessões para área administrativa, mas deve diferenciar permissões completas e
  parciais.
- A criação de contas editor/admin, a alteração de perfil e toda exclusão
  permanente devem ser reservadas ao administrador no servidor, não somente na
  interface.
- Editores possuem acesso administrativo parcial e às anotações. Usuários comuns
  possuem apenas acesso público e às próprias anotações; não podem excluir a
  própria conta pela aplicação.
- `login.php` deve conferir o perfil atual no banco ao iniciar sessão e
  redirecionar conforme o nível permitido.
- `admin/anotacoes.php` usa `$_SESSION['usuario']['id']` e filtra todas as
  operações pelo ID do usuário autenticado.
- As ações de alterar/excluir usuários continuam exigindo POST e CSRF.

## Camada WordPress separada

- Não copie diretamente os arquivos PHP do Portal para `wp-content/plugins` sem
  revisar conexão e sessão.
- A integração isolada está em `wordpress-plugin/portal-biblico/` e utiliza um
  shortcode Bootstrap responsivo para incorporar o Portal Bíblico por iframe.
- Essa integração não altera o projeto PHP original nem compartilha sessões e
  bancos com o WordPress.

## Configuração e auditoria

- Não reutilize ou publique a senha de banco usada em ambiente local.
- Prefira as variáveis `DB_HOST`, `DB_NAME`, `BIBLIA_DB_NAME`, `DB_USER` e
  `DB_PASSWORD`. Os fallbacks locais podem existir apenas para compatibilidade
  do servidor de desenvolvimento atual.
- Inicie sessões por `Seguranca::iniciarSessao()`. Mantenha HTTP-only,
  SameSite Lax, Secure em HTTPS e modo estrito habilitado.
- Antes de importar SQL, selecione o banco correto no cliente usado. Não importe
  dumps que recriem bancos inteiros ou executem `DROP DATABASE`.
- Execute `php -l` nos arquivos alterados e `composer dump-autoload` após
  modificar namespaces ou classes.