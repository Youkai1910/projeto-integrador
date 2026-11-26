# Portal Bíblico

## Estrutura

- `public/` contém as páginas e os recursos acessíveis pelo navegador. Configure
  o document root do servidor web para apontar para esse diretório.
- `public/admin/` contém rotas administrativas protegidas por autenticação e
  pelo ID configurado em `ADMIN_USER_ID`.
- `src/` contém os serviços, modelos, helpers e bootstrap administrativo.
- `vendor/` e `composer.json` ficam na raiz do projeto, fora do document root.
- `logs/` e `biblia_catolica/` também ficam fora do document root.

## Desenvolvimento local

Na raiz do projeto, instale as dependências:

```powershell
composer install
```

Inicie o servidor PHP apontando para `public/`:

```powershell
php -S localhost:8000 -t public
```

Depois, acesse `http://localhost:8000`. Configure as variáveis de ambiente do
banco de dados (`DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`) antes de usar as
funcionalidades que precisam dele. Para habilitar as rotas administrativas,
crie a conta responsável normalmente, consulte seu `id` na tabela `usuarios`
e configure esse valor em `ADMIN_USER_ID`:

```text
ADMIN_USER_ID=1
```

Todas as contas têm o mesmo perfil de usuário. A conta cujo ID corresponde a
`ADMIN_USER_ID` pode gerenciar outras contas; nenhuma conta recebe perfil de
administrador ou editor no banco. Sem essa configuração, as rotas administrativas
ficam indisponíveis. Usuários podem excluir a própria conta em **Minha conta**,
confirmando a senha atual; isso também remove as anotações e favoritos vinculados.

## Anotações e favoritos bíblicos

Execute `biblia_catolica/tabelas_usuarios_anotacoes.sql` no banco configurado
em `DB_NAME` para criar as tabelas de anotações e favoritos bíblicos. Em uma
instalação existente, execute o script novamente para criar a tabela de
favoritos e converter perfis antigos em contas de usuário padrão. Usuários
autenticados podem favoritar versículos e capítulos na página de leitura; esses
itens aparecem em uma seção própria na página de anotações.
