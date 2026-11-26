# Guia de desenvolvimento do projeto-integrador

## Composer e autoload

- Execute `composer install` na raiz do projeto para instalar as dependências e
  gerar `vendor/autoload.php`.
- O autoload PSR-4 mapeia `ProjetoIntegrador\` para `src/`, conforme
  `composer.json`. Mantenha nomes de namespace, classes e arquivos com a mesma
  capitalização; por exemplo, `ProjetoIntegrador\Database\ConexaoBD` fica em
  `src/Database/ConexaoBD.php`.
- Depois de alterar o mapeamento de autoload, execute `composer dump-autoload`.
- `vendor/` é gerado pelo Composer e não deve ser versionado.

## Configuração local

- `Env` carrega variáveis do `.env` na raiz; variáveis já definidas no ambiente
  têm precedência. Use `.env.example` como modelo.
- Nunca versione `.env`, senhas ou tokens. Mantenha-os fora do Git.
- Configure `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` para a instalação
  MySQL local. O Docker Compose configura o host interno do banco para a aplicação.

## Banco de dados

No phpMyAdmin, selecione `biblia_catolica` e importe os arquivos de
`database/dumps/` nesta ordem:

1. `biblia_tabelas.sql.sql`
2. `biblia_testamento_livros.sql.sql`
3. `biblia_versiculos.sql.sql`
4. `tabelas_usuarios_anotacoes.sql`

O último script cria `usuarios`, `anotacoes` e `favoritos_biblicos`, além de
ajustar `usuarios.tipo` para `usuario`, `editor` ou `admin`. Ele não cria contas.
Os arquivos em `database/init/` são cópias usadas na inicialização do MySQL pelo
Docker; os scripts dessa pasta devem permanecer versionados.

Armazene em `usuarios.senha` somente o hash gerado por `password_hash()` do PHP,
nunca a senha em texto puro. Permissões administrativas também são verificadas
no servidor; não confie somente no valor do banco ou na interface.

## Cadastro e administração de usuários

- `public/cadastro.php` sempre cria contas com perfil `usuario`.
- `public/login.php` define `$_SESSION['usuario']['id']` e `$_SESSION['usuario_id']`.
  `public/logout.php` aceita somente POST com token CSRF.
- As rotas administrativas exigem sessão autenticada e autorizam o usuário
  conforme `usuarios.tipo = 'admin'` ou o identificador `ADMIN_USER_ID`.
- O perfil `editor` existe no esquema do banco, mas não concede acesso
  administrativo por si só.
- O bootstrap administrativo fica em `src/Bootstrap/admin.php`, fora da raiz web.

## Anotações

- A página `public/admin/anotacoes.php` exige uma sessão autenticada com o
  identificador positivo do usuário em `$_SESSION['usuario_id']`; o fluxo de
  autenticação deve definir esse valor após o login.
- `AnotacaoServico` limita listagem, edição, favoritos e exclusão ao dono da
  anotação e usa consultas preparadas.
- A página usa token CSRF nos formulários e `Validacoes::textoAnotacao()` para
  exigir texto válido de até 65.535 bytes.

## Página inicial e imagens

- A página inicial fica em `public/index.php` e seus estilos ficam em `public/css/style.css`.
- As imagens do site ficam em `public/images/`.
- Configure a raiz web do servidor para o diretório `public/`.
- Caminhos de imagens definidos no CSS são relativos a `public/css/style.css`, por
  exemplo `../images/logo.png`.
- Ao renomear ou mover uma imagem, atualize também o caminho correspondente na
  página ou no CSS.

## Interface da página inicial

- Mantenha o PHP da consulta e da conexão no início de `public/index.php`.
- Renderize dados vindos do banco com `htmlspecialchars()` ou outra proteção
  equivalente.
- Coloque os estilos da interface em `public/css/style.css`; não misture CSS extenso
  dentro de `public/index.php`.
- Preserve os arquivos existentes em `public/images/` e use caminhos relativos
  a partir da raiz web `public/`.
- Utilize `alt` em todas as imagens informativas e preserve `aria-label` em
  controles importantes de navegação e pesquisa.
- Mantenha links para pular ao conteúdo principal, hierarquia de títulos,
  rótulos de formulários e foco visível para navegação por teclado.
- Garanta contraste legível para textos sobre imagens e fundos coloridos.
- A leitura em voz alta usa `SpeechSynthesis` e só deve iniciar após ação do
  usuário; mantenha controles de pausar, continuar e parar, além do status acessível.
- Preserve o layout responsivo em larguras menores com media queries.
- Valide a sintaxe PHP e confirme que a página e os recursos CSS e de imagem
  respondem corretamente em servidor local.

## Comandos locais

Execute na raiz do repositório. Copie `.env.example` para `.env` e preencha as
configurações locais antes de iniciar recursos que dependem do banco.

```powershell
composer install
php -S 127.0.0.1:8000 -t public
Get-ChildItem -Recurse -Path .\public -Filter *.php | ForEach-Object { php -l $_.FullName }
git diff --check
```

## Comandos realizados em 24/11/2026

- `git fetch origin`
- `git switch main`
- `git pull --ff-only origin main` — `main` já estava sincronizada com `origin/main`.
- `php -S 127.0.0.1:8005 -t public` — teste local das páginas e recursos com `public/`
  como raiz web; o servidor de teste foi encerrado ao final.
- `php -l` nos arquivos PHP da aplicação e `git diff --check`.

## Alterações e verificações em 26/11/2026

- Os dumps SQL foram movidos para `database/dumps/`; o esquema passou a incluir
  `favoritos_biblicos`.
- Os formulários de contato e colaboração usam Formspree via cURL. Configure
  `FORMSPREE_FORM_ID`; se necessário, informe `CURL_CA_BUNDLE` com o caminho
  local do bundle de certificados CA. Nunca desative a validação TLS.
- A conexão MySQL, as tabelas necessárias, as páginas inicial/contato e o envio
  Formspree foram verificados; o segredo do banco permanece somente no `.env`.
# Portal Bíblico

Aplicação PHP integrada a MySQL para leitura bíblica, cadastro/login, anotações e área administrativa.

## Requisitos

- PHP 8.1 ou superior recomendado (compatibilidade mínima PHP 7.4)
- Extensões PHP PDO, pdo_mysql e mbstring/intl conforme disponibilidade
- Composer
- MySQL 8
- Servidor web Apache, Nginx ou servidor PHP embutido

## Instalação

```bash
composer install
composer dump-autoload
```

## Variáveis de ambiente

Configure antes de iniciar o servidor:

```text
DB_HOST=localhost
DB_NAME=biblia_catolica
BIBLIA_DB_NAME=biblia_catolica
DB_USER=usuario_do_banco
DB_PASSWORD=senha_segura
FORMSPREE_FORM_ID=seu_id_de_formulario
CURL_CA_BUNDLE=
```

Os formulários de contato e colaboração enviam pelo Formspree através do serviço
PHP, preservando a validação e o token CSRF dos formulários. Crie o formulário,
confirme o endereço de destino e configure seu ID em `FORMSPREE_FORM_ID`. O PHP
precisa da extensão `curl` e de acesso HTTPS ao Formspree. Se a validação do
certificado falhar, configure `CURL_CA_BUNDLE` ou os caminhos `curl.cainfo` e
`openssl.cafile` no `php.ini`; nunca desative a verificação de certificados.

## Banco de dados

Selecione o banco no phpMyAdmin ou cliente MySQL antes de importar os arquivos da pasta `database/dumps/` nesta ordem:

1. `biblia_tabelas.sql.sql`
2. `biblia_testamento_livros.sql.sql`
3. `biblia_versiculos.sql.sql`
4. `tabelas_usuarios_anotacoes.sql`

Os dumps bíblicos não removem o banco inteiro. Confirme o banco selecionado antes da importação, pois os scripts de dados substituem os dados das suas respectivas tabelas.

## Perfis

- `usuario`: navegação pública e anotações próprias.
- `editor`: perfil definido no banco; não recebe acesso administrativo somente
  por esse valor.
- `admin`: autorizado pelas rotas administrativas.

O cadastro público cria somente usuários comuns. O acesso administrativo exige
perfil `admin` ou correspondência com `ADMIN_USER_ID`, verificada no servidor.
