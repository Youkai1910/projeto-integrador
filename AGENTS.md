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
- `admin/usuarios.php` e suas ações exigem uma sessão autenticada e confirmam o
  perfil de administrador no banco antes de permitir operações.

## Anotações

- A página `admin/anotacoes.php` exige uma sessão autenticada com o identificador
  positivo do usuário em `$_SESSION['usuario_id']`; o fluxo de autenticação deve
  definir esse valor após o login.
- `AnotacaoServico` limita listagem, edição, favoritos e exclusão ao dono da
  anotação e usa consultas preparadas.
- A página usa token CSRF nos formulários e `Validacoes::textoAnotacao()` para
  exigir texto válido de até 65.535 bytes.

## Página inicial e imagens

- A página inicial fica em `index.php` e seus estilos ficam em `css/style.css`.
- As imagens do site ficam em `images/`.
- Caminhos de imagens definidos no CSS são relativos a `css/style.css`, por
  exemplo `../images/logo.png`.
- Ao renomear ou mover uma imagem, atualize também o caminho correspondente na
  página ou no CSS.
## Interface da página inicial

- Mantenha o PHP da consulta e da conexão no início de `index.php`.
- Renderize dados vindos do banco com `htmlspecialchars()` ou outra proteção
  equivalente.
- Coloque os estilos da interface em `css/style.css`; não misture CSS extenso
  dentro de `index.php`.
- Preserve arquivos no diretório `biblia_catolica/imagens/` e use caminhos
  relativos a partir da raiz da aplicação.
- Utilize `alt` em todas as imagens informativas e preserve `aria-label` em
  controles importantes de navegação e pesquisa.
- Preserve o layout responsivo em larguras menores com media queries.
- Valide a sintaxe PHP e confirme que a página e os recursos CSS e de imagem
  respondem corretamente em servidor local.
