# Sistema de Gestão de Produtos

Projeto acadêmico desenvolvido para a disciplina de **Programação para Internet**, como parte da Atividade Prática Orientada (APO).

## Objetivo

Desenvolver uma aplicação web para gerenciamento de produtos e fornecedores, com autenticação de usuários e cesta de compras.

## Tecnologias

- PHP com orientação a objetos
- MySQL
- PDO para acesso ao banco de dados
- HTML5, CSS3 e JavaScript
- Bootstrap 5
- AJAX
- Git e GitHub

## Funcionalidades previstas

- Cadastro e autenticação de usuários com hash SHA-256.
- Cadastro, consulta, edição e exclusão de fornecedores.
- Cadastro, consulta, edição e exclusão de produtos.
- Relacionamento entre produtos e fornecedores.
- Atualização de dados utilizando AJAX.
- Seleção de produtos por checkboxes.
- Cesta de compras com uma unidade de cada produto.
- Exibição da quantidade de produtos e do valor total da cesta.

## Estrutura inicial

```text
app/
config/
database/
docs/
public/
    css/
    js/
    assets/
    index.php
```

## Requisitos de execução

- XAMPP com Apache e MySQL
- PHP com suporte a PDO MySQL
- Navegador web

## Executando o projeto

1. Coloque o projeto na pasta `C:\xampp\htdocs\`.
2. Inicie o Apache e o MySQL pelo XAMPP.
3. Acesse no navegador:

    `http://localhost/apo-sistema-gestao-produtos/public/`

**Estado atual:** ambiente PHP inicial validado. A conexão PDO, a criação automática do banco, a autenticação e as demais funcionalidades ainda serão implementadas.

## Documentação acadêmica

O projeto seguirá as três etapas da APO:

1. Análise e prototipação das telas no Figma.
2. Modelagem do banco de dados e exportação do DER em PDF.
3. Implementação e entrega do código funcional em ZIP.

## Observação

Projeto desenvolvido para fins acadêmicos. O uso de SHA-256 puro para senhas atende à orientação específica da atividade, mas não é recomendado para sistemas reais em produção.
