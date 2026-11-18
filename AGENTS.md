# Guia de desenvolvimento do projeto-integrador

## Criar as tabelas de usuários e anotações

No phpMyAdmin, selecione o banco `biblia_catolica`, abra a aba **Importar** e
importe `database/tabelas_usuarios_anotacoes.sql`. O script cria:

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