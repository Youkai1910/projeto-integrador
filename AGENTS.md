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
`BIBLIA_DB_NAME` (padrão igual a `DB_NAME`, normalmente `biblia_catolica`) conforme o ambiente. O site não fixa portas
HTTP como 650, 4000 ou 890; a porta HTTP depende do servidor local usado para
executar o projeto.

O cadastro público cria somente contas do tipo `usuario`. O envio dos
formulários de contato e colaboração usa `MAIL_TO` e `MAIL_FROM` e requer que o
PHP esteja configurado para enviar e-mail; sem essas variáveis, o site informa
que o envio não está disponível e oferece o endereço de contato.

`login.php` autentica contas pelo hash de senha e abre a sessão usada pela área
administrativa. O primeiro administrador precisa ser provisionado pelo
responsável pelo banco; o cadastro público nunca concede esse perfil.