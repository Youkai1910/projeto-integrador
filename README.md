# projeto-integrador
projeto-integrador

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
