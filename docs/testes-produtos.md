# Relatório de Testes — CRUD de Produtos

## Projeto

APO — Sistema de Gestão de Produtos

## Ambiente

- PHP 8 / XAMPP
- MySQL
- HTML, Bootstrap e JavaScript
- Testes pelo navegador, DevTools e phpMyAdmin

## Testes funcionais

| ID      | Teste                | Resultado esperado | Resultado obtido                      |
| ------- | -------------------- | ------------------ | ------------------------------------- |
| PROD-01 | Cadastrar produto    | Produto criado     | Mensagem de sucesso e registro criado |
| PROD-02 | Listar produtos      | Produtos exibidos  | Dados e fornecedor exibidos           |
| PROD-03 | Editar com AJAX      | Dados atualizados  | Alteração sem recarregamento          |
| PROD-04 | Excluir com AJAX     | Produto removido   | Confirmação e exclusão efetiva        |
| PROD-05 | Cancelar exclusão    | Produto preservado | Produto permanece                     |
| PROD-06 | Persistência após F5 | Dados mantidos     | Dados persistidos                     |
| PROD-07 | Campos obrigatórios  | Envio bloqueado    | Formulário impede envio               |
| PROD-08 | Preço inválido       | Rejeitado          | Operação rejeitada                    |
| PROD-09 | Acesso sem login     | Redirecionamento   | Redirecionamento ao login             |

## Testes HTTP

| ID                              | Teste                   | HTTP esperado | Resultado obtido                                  |
| ------------------------------- | ----------------------- | ------------- | ------------------------------------------------- |
| HTTP-01                         | Método GET na exclusão  | 405           | 405                                               |
| {erro: "Método não permitido."} |
| HTTP-02                         | Token CSRF inválido     | 403           | 403                                               |
| {erro: "Token CSRF inválido."}  |
| HTTP-03                         | ID inválido             | 400           | HTTP 400 — ID do produto inválido.                |
| HTTP-04                         | Produto inexistente     | 404           | HTTP 404 — Produto não encontrado.                |
| HTTP-05                         | Usuário não autenticado | 401           | HTTP 401 — Usuário não autenticado.               |
| HTTP-06                         | Fornecedor inexistente  | 400           | HTTP 400, informando que o fornecedor é inválido. |

## Revisão técnica

- [x] Sintaxe PHP validada.
- [x] Relacionamento com fornecedores validado.
- [x] Operações AJAX verificadas.
- [x] Nenhum erro inesperado no Console.
- [x] Nenhuma alteração fora do escopo.

## Observações

Registrar eventuais problemas encontrados, correções
realizadas e resultados dos novos testes.
