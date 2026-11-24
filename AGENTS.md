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

## Cadastro e administração de usuários

- `cadastro.php` sempre cria contas com perfil `usuario`; `TipoUsuario` centraliza
  os perfis válidos e seus rótulos.
- `login.php` define `$_SESSION['usuario']['id']` e `$_SESSION['usuario_id']`.
  `logout.php` aceita somente POST com token CSRF.
- `public/admin/usuarios.php` e suas ações exigem uma sessão autenticada e confirmam o
  perfil de administrador no banco antes de permitir operações.

## Anotações

- A página `public/admin/anotacoes.php` exige uma sessão autenticada com o identificador
  positivo do usuário em `$_SESSION['usuario_id']`; o fluxo de autenticação deve
  definir esse valor após o login.
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
- Preserve arquivos no diretório `biblia_catolica/imagens/` e use caminhos
  relativos a partir da raiz da aplicação.
- Utilize `alt` em todas as imagens informativas e preserve `aria-label` em
  controles importantes de navegação e pesquisa.
- Preserve o layout responsivo em larguras menores com media queries.
- Valide a sintaxe PHP e confirme que a página e os recursos CSS e de imagem
  respondem corretamente em servidor local.
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
MAIL_TO=contato@portaldiario.com
MAIL_FROM=nao-responda@seu-dominio.com
```

Nunca coloque a senha real diretamente nos arquivos versionados.

## Banco de dados

Selecione o banco no phpMyAdmin ou cliente MySQL antes de importar os arquivos da pasta `biblia_catolica/` nesta ordem:

1. `biblia_tabelas.sql.sql`
2. `biblia_testamento_livros.sql.sql`
3. `biblia_versiculos.sql.sql`
4. `tabelas_usuarios_anotacoes.sql`

Os dumps bíblicos não removem o banco inteiro. Confirme o banco selecionado antes da importação, pois os scripts de dados substituem os dados das suas respectivas tabelas.

## Perfis

- `usuario`: navegação pública e anotações próprias.
- `editor`: área administrativa parcial e anotações.
- `admin`: administração integral, perfis, usuários e exclusões permanentes.

O cadastro público cria somente usuários comuns. Perfis editor/admin devem ser atribuídos pelo banco ou por administrador autorizado.