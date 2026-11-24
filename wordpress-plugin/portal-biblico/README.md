# Portal Bíblico Integrado

Plugin WordPress separado que integra o Portal Bíblico existente sem alterar o projeto PHP original.

## Instalação

1. Copie a pasta `portal-biblico` para `wp-content/plugins/` no WordPress.
2. Ative **Portal Bíblico Integrado** no painel.
3. Insira o shortcode em uma página:

```text
[portal_biblico url="https://seu-dominio.com/projeto-integrador/" altura="820px"]
```

O atributo `url` deve apontar para a instalação real do Portal Bíblico. O site original continua sendo servido independentemente pelo PHP/MySQL. Bootstrap é carregado apenas pela camada WordPress.
