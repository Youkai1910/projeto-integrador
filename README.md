# Portal Bíblico

## Estrutura

- `public/` contém as páginas e os recursos acessíveis pelo navegador. Configure
  o document root do servidor web para apontar para esse diretório.
- `public/admin/` contém rotas administrativas protegidas por autenticação e
  pelo ID configurado em `ADMIN_USER_ID`.
- `src/` contém os serviços, modelos, helpers e bootstrap administrativo.
- `vendor/` e `composer.json` ficam na raiz do projeto, fora do document root.
- `logs/` e `database/` também ficam fora do document root.

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
funcionalidades que precisam dele. Para uma configuração local simples, copie
`.env.example` para `.env` e preencha os valores; o arquivo `.env` não é versionado.
Para habilitar as rotas administrativas,
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

## E-mail de contato

Os formulários de contato e colaboração enviam as mensagens pelo Formspree.
Crie um formulário no painel do Formspree, confirme o endereço de destino e
configure o ID do formulário no arquivo `.env`:

```text
FORMSPREE_FORM_ID=xoedbywo
```

Esse é o ID do endpoint já criado para este projeto. Se trocar de formulário,
substitua pelo ID correspondente, sem incluir o URL completo. As mensagens são
encaminhadas ao endereço confirmado no Formspree, e o e-mail do visitante é
usado para responder.
O servidor precisa da extensão PHP `curl` e de acesso HTTPS ao Formspree.
No Windows, se o log mostrar erro de certificado CA, baixe o bundle oficial em
`https://curl.se/ca/cacert.pem` e defina `CURL_CA_BUNDLE` no `.env` com o
caminho completo do arquivo. Também é possível definir `curl.cainfo` e
`openssl.cafile` no `php.ini` carregado (`php --ini`). Não desative a
verificação TLS.

## Anotações e favoritos bíblicos

Execute `database/dumps/tabelas_usuarios_anotacoes.sql` no banco configurado
em `DB_NAME` para criar as tabelas de anotações e favoritos bíblicos. Em uma
instalação existente, execute o script novamente para criar a tabela de
favoritos e converter perfis antigos em contas de usuário padrão. Usuários
autenticados podem favoritar versículos e capítulos na página de leitura; esses
itens aparecem em uma seção própria na página de anotações.
