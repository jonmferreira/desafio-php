# Coleção Postman — Desafio Systock

Arquivos:

- `Desafio-Systock.postman_collection.json` — requests organizados por recurso (Auth, Users,
  Categories, Products, Movements), incluindo variações usadas nos testes de segurança.
- `Desafio-Systock.postman_environment.json` — variáveis de ambiente (`base_url`, `token`,
  `other_user_id`, `other_resource_id`).

## Como importar

1. Abra o Postman → **Import** → arraste os dois arquivos `.json` desta pasta.
2. Selecione o ambiente **Desafio Systock - Local** no seletor de ambiente (canto superior direito).
3. Garanta que o backend está no ar (`docker-compose up --build`, API em `http://localhost:8000/api`).

## Como rodar

1. Rode **Auth → Login - sucesso** primeiro. O script de teste salva o token retornado na
   variável de ambiente `token`, usada automaticamente (Bearer) nos demais requests.
2. Os requests com sufixo entre parênteses citando uma categoria do **OWASP API Security Top 10**
   (ex.: `(API1)`, `(API3)`...) são os testes de segurança — ver a tabela completa no
   `README.md` da raiz do projeto (seção "Análise de segurança").
3. Para o teste de **IDOR** (`Buscar usuario de outro contexto`), preencha a variável de ambiente
   `other_user_id` com o ID de um usuário diferente do autenticado.
4. Para o teste de **idempotência** (`POST duplicado com mesma Idempotency-Key`), execute o
   request **duas vezes seguidas** (Send → Send novamente) sem alterar nada — a segunda resposta
   deve vir com o header `X-Idempotent-Replay` e não deve criar uma nova movimentação no banco.
5. Para o teste de **Race Condition**, use o **Collection Runner** (botão "Run") apenas na pasta
   `Movements`, request "Race Condition - saida concorrente do mesmo lote", configurando
   múltiplas iterações e delay 0ms — ou dispare o mesmo request em várias abas simultaneamente.

## Status dos testes

Resultados de cada vetor estão na tabela "Análise de segurança" do `README.md` da raiz,
com evidência de cada teste (🛡️ Blindado / ⚠️ Parcial / N/A).
