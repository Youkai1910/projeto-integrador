# Resumo da branch `biblia_alexandre`

Atualizado em 24/11/2026.

## Estrutura atual

- `public/` é a raiz web e contém as páginas públicas, recursos CSS/imagens e
  rotas administrativas protegidas.
- `src/` contém conexão, modelos, serviços, helpers e o bootstrap administrativo.
- `vendor/`, `logs/`, `composer.json` e os arquivos SQL ficam fora da raiz web.
- O servidor local deve ser iniciado com document root em `public/`.

## Funcionalidades

- A página inicial consulta os livros da Bíblia e apresenta navegação e cards
  responsivos.
- Cadastro público, login, logout com CSRF e área administrativa com verificação
  de perfil.
- Anotações pessoais limitadas ao usuário autenticado.
- Leitura bíblica pública por livro e capítulo, com leitura em voz alta opcional
  via `SpeechSynthesis` do navegador. Há controles para ouvir, pausar, continuar
  e parar; o áudio não inicia automaticamente.
- Formulários públicos de contato e colaboração.
- O formulário de contato exibe `contato@portalbiblico.com`.
- `LogServico` direciona registros PHP para `logs/erros.log`.

## Alterações realizadas em 24/11/2026

- A branch `main` foi atualizada localmente; o pull não encontrou novos commits.
- As páginas, estilos e imagens do site foram organizados em `public/`; os
  caminhos do autoload foram atualizados. O bootstrap de administração foi
  mantido fora da raiz web em `src/Bootstrap/admin.php`.
- O overflow dos cards principais em telas estreitas foi corrigido limitando
  sua largura ao contêiner.
- A leitura bíblica passou a oferecer voz nativa do navegador, mediante ação
  explícita do usuário.
- Foram adicionados atalhos de teclado para o conteúdo, hierarquia de títulos
  na página inicial, foco mais visível, contraste reforçado nos cards e
  semântica de grupo nos controles de áudio.
- Na página de leitura, o rótulo foi simplificado para “BÍBLIA” e o rodapé
  passou a exibir “Portal Bíblico”.

## Comandos executados e verificações

Sincronização de `main`:

```powershell
git fetch origin
git switch main
git pull --ff-only origin main
```

Servidor local e verificações:

```powershell
php -S 127.0.0.1:8005 -t public
Get-ChildItem -Recurse -Path .\public -Filter *.php | ForEach-Object { php -l $_.FullName }
git diff --check
```

- Páginas públicas independentes do banco e recursos CSS/imagem responderam com
  HTTP 200 no servidor local.
- A página de leitura carregou um capítulo e apresentou os controles de áudio
  sem iniciar a fala automaticamente.
- O navegador confirmou o suporte à API de voz sem iniciar fala durante o teste.
- O link para pular ao conteúdo recebeu foco por teclado e moveu o foco para o
  elemento `<main>`.
- A sintaxe PHP e `git diff --check` passaram.
