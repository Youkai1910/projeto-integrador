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

## E-mail de contato

Os formulários usam SMTP do Outlook.com (`smtp-mail.outlook.com`, porta 587,
STARTTLS) e OAuth2/XOAUTH2 para enviar para `contato.portalbiblico@outlook.com`.
Não é usada senha de e-mail nem senha de aplicativo. Configure no ambiente:

```text
MAIL_TO=contato.portalbiblico@outlook.com
OUTLOOK_SMTP_USERNAME=contato.portalbiblico@outlook.com
OUTLOOK_CLIENT_ID=ID_DO_APLICATIVO_MICROSOFT
```

Registre um aplicativo Microsoft Entra que aceite contas pessoais Microsoft,
habilite o fluxo de cliente público e conceda a permissão delegada `SMTP.Send`
do Exchange Online. `OUTLOOK_CLIENT_ID` é o ID de aplicativo exibido no
registro; não é uma senha. Com essa variável configurada e o Composer instalado,
execute na raiz do projeto:

```powershell
php .\scripts\autorizar-outlook.php
```

O script mostrará as instruções e o código de dispositivo da Microsoft para
entrar na conta Outlook e consentir o acesso. Ele grava o token de atualização
em `.secrets/outlook-refresh-token.txt`, fora do document root e ignorado pelo
Git. Não compartilhe nem versione esse arquivo. O PHP precisa das extensões
`curl` e `openssl`, e o servidor deve permitir conexões HTTPS e SMTP de saída
na porta 587. As respostas ao formulário serão direcionadas ao e-mail informado
pelo visitante.

No Windows, se o PHP informar que não encontrou o emissor do certificado SSL,
baixe um pacote CA atualizado de `https://curl.se/ca/cacert.pem` e configure
`curl.cainfo` e `openssl.cafile` com o caminho desse arquivo no `php.ini` que
aparece em `php --ini`. Mantenha a verificação de certificados ativada.

## Anotações e favoritos bíblicos

Execute `biblia_catolica/tabelas_usuarios_anotacoes.sql` no banco configurado
em `DB_NAME` para criar as tabelas de anotações e favoritos bíblicos. Em uma
instalação existente, execute o script novamente para criar a tabela de
favoritos e converter perfis antigos em contas de usuário padrão. Usuários
autenticados podem favoritar versículos e capítulos na página de leitura; esses
itens aparecem em uma seção própria na página de anotações.
