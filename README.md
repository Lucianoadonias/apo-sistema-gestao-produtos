# Sistema de Gestão de Produtos

Projeto acadêmico desenvolvido para a disciplina de
Programação para Internet, como parte da Atividade
Prática Orientada (APO).

## Objetivo

Desenvolver uma aplicação web para gerenciamento
de fornecedores e produtos, com autenticação de
usuários e cesta de produtos.

## Tecnologias utilizadas

- PHP com programação orientada a objetos
- MySQL
- PDO para acesso ao banco de dados
- HTML5, CSS3 e JavaScript
- Bootstrap 5
- AJAX utilizando Fetch API
- Sessões PHP
- Git e GitHub

## Funcionalidades implementadas

- Cadastro e autenticação de usuários
- Senhas armazenadas com hash SHA-256
- CRUD de fornecedores
- CRUD de produtos
- Relacionamento entre produtos e fornecedores
- Edição e exclusão utilizando AJAX
- Seleção de produtos por checkboxes
- Cesta com uma unidade de cada produto
- Cálculo da quantidade e do valor total
- Navegação entre as funcionalidades
- Proteção das páginas por autenticação
- Proteção CSRF nas operações implementadas
- Criação automática do banco e das tabelas

## Requisitos para execução

- Windows com XAMPP instalado
- Apache
- MySQL ou MariaDB compatível
- PHP com extensão PDO MySQL habilitada
- Navegador web

Os exemplos abaixo consideram o XAMPP instalado
em `C:\xampp`.

## Instalação do projeto

### 1. Preparar os arquivos

Extraia ou clone o projeto para:

`C:\xampp\htdocs\apo-sistema-gestao-produtos`

### 2. Iniciar os serviços

Abra o painel de controle do XAMPP.

Inicie os serviços:

- Apache
- MySQL

### 3. Configurar a conexão

Na pasta `config`, existe o arquivo:

`database.example.php`

Faça uma cópia dele e renomeie para:

`database.php`

O arquivo deve apresentar uma estrutura semelhante a:

```php
<?php

declare(strict_types=1);

return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'gestao_produtos',
    'username' => 'root',
    'password' => '',
];
```

Ajuste o usuário, a senha, a porta e os demais
parâmetros conforme o ambiente local.

O arquivo `config/database.php` é ignorado pelo Git
para evitar o versionamento de credenciais.

### 4. Instalar o banco de dados

Abra o PowerShell na pasta raiz do projeto:

```powershell
cd C:\xampp\htdocs\apo-sistema-gestao-produtos
```

Execute:

```powershell
C:\xampp\php\php.exe database\install.php
```

O instalador deve criar automaticamente:

- Banco `gestao_produtos`
- Tabela `usuarios`
- Tabela `fornecedores`
- Tabela `produtos`
- Chave estrangeira de produtos para fornecedores

Mensagem esperada:

```text
Banco de dados e tabelas criados com sucesso!
```

O instalador utiliza `CREATE DATABASE IF NOT EXISTS`
e `CREATE TABLE IF NOT EXISTS`.

Ele não apaga registros de tabelas existentes nem
atualiza automaticamente estruturas antigas.

Por segurança, o instalador somente pode ser
executado pelo terminal.

### 5. Acessar o sistema

Abra o navegador e acesse:

http://localhost/apo-sistema-gestao-produtos/public/

Cadastre um usuário pela interface e faça login.

Após a autenticação, o painel permite acessar:

- Gerenciamento de fornecedores
- Gerenciamento de produtos
- Seleção de produtos
- Cesta de produtos

## Estrutura do projeto

```text
app/
  Auth/
  Database/
  Repositories/
  Services/

config/
  database.example.php

database/
  install.php

docs/

public/
  index.php
  cadastro.php
  painel.php
  fornecedores.php
  produtos.php
  atualizar-fornecedor.php
  excluir-fornecedor.php
  atualizar-produto.php
  excluir-produto.php
  selecao-produtos.php
  cesta.php
  logout.php

README.md
.gitignore
```

A estrutura acima apresenta os arquivos principais.
Outros arquivos de apoio também podem estar presentes.

## Banco de dados

O sistema utiliza três tabelas:

- `usuarios`: dados de autenticação
- `fornecedores`: cadastro de fornecedores
- `produtos`: cadastro e relacionamento com fornecedores

O campo `produtos.fornecedor_id` referencia
`fornecedores.id`.

O relacionamento utiliza:

- `ON DELETE RESTRICT`
- `ON UPDATE CASCADE`

## Cesta de produtos

A cesta utiliza sessões PHP.

Cada produto selecionado corresponde a uma unidade.

Os preços são consultados no banco de dados
para calcular o valor total.

A cesta não representa um pedido persistido
e não possui processo de pagamento.

## Testes

O projeto passou por testes funcionais das
operações de cadastro, consulta, edição e exclusão.

Foram utilizados:

- Navegador web
- DevTools
- phpMyAdmin
- Verificação de sintaxe PHP

A criação automática do banco e das tabelas
também foi validada em um banco de teste isolado.

Os resultados detalhados dos testes de produtos
estão documentados em `docs/testes-produtos.md`.

## Observação de segurança

Este projeto foi desenvolvido para fins acadêmicos.

O uso de SHA-256 puro para senhas segue a orientação
específica da atividade, mas não é recomendado
para sistemas reais em produção.

Em sistemas profissionais, recomenda-se utilizar
`password_hash()` e `password_verify()`.

## Documentação acadêmica

A entrega da APO contempla:

1. Protótipo das telas elaborado no Figma.
2. Diagrama entidade-relacionamento do MySQL Workbench.
3. Código-fonte funcional do projeto em arquivo ZIP.

```

```
