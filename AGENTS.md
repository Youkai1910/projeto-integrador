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

## Anotações

- A página `admin/anotacoes.php` exige uma sessão autenticada com o identificador
  positivo do usuário em `$_SESSION['usuario_id']`.
- A listagem e as operações de anotação são limitadas ao usuário autenticado.

## Página inicial e imagens

- A página inicial fica em `index.php` e seus estilos ficam em `css/style.css`.
- As imagens do site ficam em `images/`.
- Caminhos de imagens definidos no CSS são relativos a `css/style.css`, por
  exemplo `../images/logo.png`.
- Ao renomear ou mover uma imagem, atualize também o caminho correspondente na
  página ou no CSS.