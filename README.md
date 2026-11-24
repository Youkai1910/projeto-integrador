# Portal Bíblico

## Estrutura

- `public/` contém as páginas e os recursos acessíveis pelo navegador. Configure
  o document root do servidor web para apontar para esse diretório.
- `public/admin/` contém as rotas administrativas, protegidas por autenticação
  e verificação de perfil.
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
funcionalidades que precisam dele.
