# TODO — v2 Implementação (Lotes + Fardos)

Modelo aprovado: users → lotes → lote_items (produto + fardos) + avarias + saidas + audits

---

## FASE 1 — Backend: Migrations

- [x] B01 — Migration: adicionar `peso` (decimal) e `min_fardos` (int) em `products`; remover `min_quantity`
- [x] B02 — Migration: criar tabela `lotes` (id, user_id, numero incremental por cliente, frete, timestamps)
- [x] B03 — Migration: criar tabela `lote_items` (PK composta: lote_id + product_id, quantidade_fardos, itens_por_fardo, valor_unitario)
- [x] B04 — Migration: criar tabela `avarias` (id, lote_id, descricao, valor, timestamps)
- [x] B05 — Migration: criar tabela `saidas` (id, lote_id, product_id, user_id, quantidade_fardos, motivo, timestamps)
- [x] B06 — Migration: criar tabela `audits` (id, user_id, event, auditable_type, auditable_id, old_values JSON, new_values JSON, ip_address, timestamps)

## FASE 2 — Backend: Models

- [x] B07 — Atualizar `Product`: campo peso, min_fardos, relations hasMany LoteItem
- [x] B08 — Criar `Lote`: belongsTo User, hasMany LoteItem, hasMany Avaria, hasMany Saida; accessor numero auto-incremental por cliente
- [x] B09 — Criar `LoteItem`: PK composta (lote_id + product_id), belongsTo Lote, belongsTo Product; accessors subtotal, peso_item
- [x] B10 — Criar `Avaria`: belongsTo Lote
- [x] B11 — Criar `Saida`: belongsTo Lote, belongsTo Product, belongsTo User
- [x] B12 — Criar `Audit` model + trait `Auditable` (boot: created/updated/deleted → insere em audits com old/new values)

## FASE 3 — Backend: Form Requests

- [x] B13 — Atualizar `StoreProductRequest` / `UpdateProductRequest`: trocar min_quantity por min_fardos, adicionar peso
- [x] B14 — Criar `StoreLoteRequest` (user_id, frete)
- [x] B15 — Criar `StoreLoteItemRequest` (product_id, quantidade_fardos, itens_por_fardo, valor_unitario)
- [x] B16 — Criar `StoreAvariaRequest` (descricao, valor)
- [x] B17 — Criar `StoreSaidaRequest` (lote_id, product_id, quantidade_fardos, motivo)

## FASE 4 — Backend: Controllers & Routes

- [x] B18 — Atualizar `ProductController`: garantir middleware admin; adaptar campos peso/min_fardos
- [x] B19 — Criar `LoteController`: index, store, show (com itens + avarias + totais calculados), destroy; GET volume/total por lote
- [x] B20 — Criar `LoteItemController`: store (adiciona item ao lote), update (altera qtd/itens/valor), destroy
- [x] B21 — Criar `AvariaController`: store (adiciona avaria ao lote), destroy
- [x] B22 — Criar `SaidaController`: store (registra saída, decrementa lote_item.quantidade_fardos)
- [x] B23 — Atualizar `ReportController`: adicionar endpoints capital por cliente, estoque por produto/lote, ranking clientes
- [x] B24 — Atualizar `routes/api.php`: novas rotas com middlewares corretos (admin/operator)
- [x] B25 — Atualizar `consultas.sql`: 3 queries v2 (capital por cliente, estoque crítico por min_fardos, maior estoque de produto por cliente)

## FASE 5 — Backend: Seeders

- [x] B26 — Criar `LoteSeeder` + dados de lotes/itens/avarias de demo
- [x] B27 — Atualizar `DatabaseSeeder`: incluir novos seeders na ordem correta
- [x] B28 — Atualizar `DemoMovementsSeeder`: mantido para alimentar relatório Giro (stock_movements preservado)

## FASE 6 — Backend: Validação local

- [x] B29 — Rodar `php artisan migrate` no container e confirmar schema
- [x] B30 — Testar todas as rotas novas/alteradas: CRUD lotes, itens, avarias, saídas, relatórios

---

## FASE 7 — Frontend: Types & Integrations

- [x] F01 — Adicionar tipos em `src/types/index.ts`: Lote, LoteItem, Avaria, Saida, LoteDetalhe, CapitalCliente, EstoqueProduto
- [x] F02 — Criar `src/views/LotesView/integrations/lotes.ts`: fetch lotes, create, show, delete, avarias, saidas
- [x] F03 — Integrations para avarias e saidas incluídas no mesmo arquivo

## FASE 8 — Frontend: Atualizar views existentes

- [x] F04 — Atualizar `ProductFormView`: trocar min_quantity por min_fardos, adicionar campo peso
- [x] F05 — Atualizar `DashboardView`: texto de estoque crítico; `ReportsView`: colunas da aba ruptura

## FASE 9 — Frontend: Novas views

- [x] F06 — Criar `LotesView` (listagem de lotes com totais)
- [x] F07 — Criar `LoteFormView` (criar lote: campos header + adicionar itens inline com subtotal calculado)
- [x] F08 — Criar `LoteDetailView` (detalhe do lote: itens, avarias, saídas, peso total, total financeiro)
- [x] F09 — Criar `SaidaFormView` (registrar saída de fardos de um lote específico)

## FASE 10 — Frontend: Router & Nav

- [x] F10 — Atualizar `router/index.ts`: novas rotas /lotes, /lotes/:id, /lotes/:id/saida com props
- [x] F11 — Atualizar nav drawer: adicionar link Lotes (operator/admin)

---

## FASE 11 — Testes (últimos)

- [x] T01 — Atualizar `consultas.sql` com queries v2 cobrindo os requisitos do enunciado
- [x] T02 — Rotas backend testadas manualmente via curl (lotes, itens, avarias, saídas, relatórios)
- [x] T03 — Criar `lotes.spec.ts`; adaptar `dashboard.spec.ts`, `reports.spec.ts`, `global-setup.ts`
- [x] T04 — Rodar 34 testes E2E — 34/34 passando ✅

---

## Calculados (nunca armazenados)

| Campo | Fórmula |
|---|---|
| subtotal item | valor_unitario × itens_por_fardo × quantidade_fardos |
| peso item | product.peso × itens_por_fardo × quantidade_fardos |
| peso total lote | SUM(peso item) |
| total avarias | SUM(avarias.valor) |
| total lote | SUM(subtotal item) + frete + total avarias |
| estoque crítico | SUM(lote_items.quantidade_fardos) < product.min_fardos |
| capital por cliente | SUM(valor_unitario × itens_por_fardo × quantidade_fardos) por user via lotes |
