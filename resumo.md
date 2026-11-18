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