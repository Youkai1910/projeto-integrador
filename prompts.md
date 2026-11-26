# Prompt para o desenvolvimento da biblia

# 1. Arquivos
crie para mim arquivos de php vazios que pertece as pasta models, services e admin baseado no banco de dados biblia_catolica

# 2. Montando os arquivos php e seus codigos e conectalos
então vamos corrigir e produzir os arquivos php  e os codigos usando as Validacoes Utils os use self escap e tente não usar muito o PDO e não mexer em arquivos de usuarios.php tudo relacionado a usuarios pois esta sendo feito por outra pessoa 

# 3. Anotações
crie para mim o codigo da anotacoes para todos anotacoes usando como base os codigos ja feitos ate agora e não mexer nos usuarios ok
# Prompt para utilizar no desenvolvimento do projeto da bíblia

# 1. Adicionando imagens
Eu quero que coloque as imagens no html respeitando os códigos do agents.md e o código já está no index, isto e só um teste para eu ver como vai ficar.

# 2. Hamburgue pelo bootstrap
Utilizando o collapse do bootstrap para criar um menu hamburgue nos livros, para deixar o controle mais interativo ao usuário pelo celular e manter-lo responsivo.

# Registro de solicitações e comandos — 24/11/2026

## Solicitações atendidas

- Copiar para `biblia_alexandre` as alterações selecionadas de `biblia_anderson`,
  sem substituir `AGENTS.md`, `prompts.md` e `resumo.md`.
- Analisar a conexão `ConexaoBD`, atualizar o e-mail de contato e configurar
  registro centralizado de erros.
- Atualizar localmente a branch `main`.
- Organizar páginas e recursos públicos sob `public/`, mantendo código interno
  fora da raiz web e testando a configuração correta do servidor PHP.
- Corrigir o alinhamento dos cards em telas de celular.
- Adicionar leitura em voz alta opcional usando a voz nativa do navegador.
- Aplicar melhorias de acessibilidade sem remover as alterações existentes.
- Ajustar os textos da página de leitura para “BÍBLIA” e “Portal Bíblico”.
- Atualizar a documentação com o trabalho e os comandos executados.

## Comandos de terminal executados

```powershell
git fetch origin
git switch main
git pull --ff-only origin main
php -S 127.0.0.1:8005 -t public
Get-ChildItem -Recurse -Path .\public -Filter *.php | ForEach-Object { php -l $_.FullName }
git diff --check
```

O pull confirmou que `main` já estava sincronizada com `origin/main`. O servidor
PHP foi usado para validar as páginas com `public/` como raiz web e encerrado
após os testes. A cópia seletiva de arquivos foi feita com
`git restore --source=biblia_anderson`, preservando os três documentos listados
acima.

## Verificações realizadas

- Sintaxe dos arquivos PHP validada com `php -l`.
- `git diff --check` sem erros.
- Páginas e recursos locais verificados por HTTP.
- Navegação por teclado, link para o conteúdo principal e suporte à API de voz
  conferidos no navegador; a fala não foi iniciada automaticamente.

# Registro de solicitações e comandos — 26/11/2026

## Solicitações atendidas

- Reorganizar os scripts SQL e alinhar o esquema do banco às tabelas usadas
  pela aplicação.
- Configurar os formulários de contato e colaboração com Formspree e corrigir
  a validação do certificado TLS no PHP.
- Corrigir a configuração local do banco e criar a tabela
  `favoritos_biblicos` ausente.
- Atualizar `resumo.md` com as alterações do dia.

## Comandos executados

```powershell
composer install
composer update --no-interaction
Get-ChildItem -Recurse -Path .\public -Filter *.php | ForEach-Object { php -l $_.FullName }
php -l .\src\Services\ContatoServico.php
php -l .\src\Helpers\LogServico.php
php -S 127.0.0.1:8005 -t public
git diff --check
```

O PHP conectou ao banco `biblia_catolica`, as páginas inicial e de contato
responderam com HTTP 200, e o envio pelo Formspree foi confirmado. Credenciais
locais ficaram em `.env`, ignorado pelo Git, e não foram registradas aqui.
