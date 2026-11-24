# Registro de erros

O arquivo `erros.log` é protegido pelo `.htaccess` e não deve ser publicado publicamente.

Cada linha usa JSON com:

- data/hora UTC;
- severidade;
- mensagem sanitizada;
- arquivo e linha;
- método e caminho da requisição;
- solução sugerida;
- stack trace de exceções críticas.

Senhas, tokens e dados de conexão não são registrados. Para análise, use somente
a rota protegida da área administrativa ou acesse o arquivo no servidor.
