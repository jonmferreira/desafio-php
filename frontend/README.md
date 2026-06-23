# Frontend — Desafio Systock

Vue 3 + Vuetify 4 + TypeScript + Vite.

## Stack

- Framework: Vue 3 (Options API)
- UI: Vuetify 4
- Estado: Pinia
- Roteamento: Vue Router 5
- Linguagem: TypeScript
- Build: Vite / Node 22

## Estrutura

```
src/
  App.vue              Raiz: app-bar, nav-drawer (só em rotas autenticadas), snackbar
  main.ts              Entry point
  router/index.ts      Rotas + guarda de autenticação
  stores/
    auth.ts            Sessão do usuário (token, user, login/logout)
    notifications.ts   Snackbar global
  services/
    api.ts             Axios autenticado (injeta Bearer token)
    publicApi.ts       Axios sem autenticação (login)
  helpers/
    cpf.ts             Formatação de CPF
    pagination.ts      Conversão DataTableOptions → ApiPaginationParams
    permissions.ts     canManageUser() — espelha UserPolicy do backend
    select.ts          Utilidades para campos select
  views/
    LoginView/         Tela de login
    users/
      UsersView/       Listagem paginada de usuários (com store + integrations)
      UserFormView/    Formulário de criação/edição de usuário
    products/
      ProductsView/    Listagem paginada de produtos (com store + integrations)
      ProductFormView/ Formulário de criação/edição de produto
  components/
    products/
      MovementForm/    Formulário de movimentação de estoque
      StockBadge/      Badge de quantidade em estoque
    shared/
      ConfirmDialog/   Diálogo de confirmação reutilizável
  plugins/
    vuetify.ts         Tema Systock (primary: #0D47A1, accent: #FF6F00)
```

## Scripts

```bash
npm run dev          # dev server (porta 3000)
npm run build        # build de produção
npm run type-check   # vue-tsc
npm run lint         # ESLint
npm run lint:fix     # ESLint com correção automática
npm run e2e          # testes E2E (Playwright, headless)
SLOWMO=1500 npx playwright test --headed  # E2E em slow-motion para inspeção visual
```

## Ambiente Docker

O frontend roda dentro de um container Docker. Para executar comandos:

```bash
docker compose exec frontend npm run lint
docker compose exec frontend npm run type-check
```

## Testes E2E — Playwright

Suite com **28 testes** cobrindo fluxos funcionais e pontos de segurança.

### Pré-requisitos

Backend e frontend em execução (`docker compose up -d`).

### Executar

```bash
npm run e2e                   # headless, relatório em lista
npx playwright test --headed  # com browser visível
npx playwright show-report    # relatório HTML após execução
```

### Estrutura dos testes

| Arquivo | Cobertura |
|---|---|
| `e2e/auth.spec.ts` | Login válido/inválido, redirecionamento sem sessão, logout |
| `e2e/dashboard.spec.ts` | Cards de estatística, nav drawer, link para relatórios |
| `e2e/products.spec.ts` | Listagem e formulário para admin; acesso leitura para operador |
| `e2e/rbac.spec.ts` | Visibilidade de menus e proteção de rotas por role |
| `e2e/reports.spec.ts` | Abas de relatório, tabelas e acesso por role |
| `e2e/security.spec.ts` | Vetores de segurança frontend (ver abaixo) |

### Segurança frontend — pontos mitigados

| Vetor | Mecanismo de defesa | Teste |
|---|---|---|
| **XSS stored** | Vue 3 escapa `{{ }}` por padrão; ausência de `v-html` | Injeta `<img onerror=...>` via mock da API e verifica que não executa |
| **Token no cliente após logout** | `clearSession()` remove `token` e `user` do `localStorage` | Verifica `localStorage.getItem('token') === null` após logout |
| **Escalada de role via localStorage** | Backend valida role pelo token Sanctum, não pelo localStorage | Adultera `user.role` no localStorage e verifica que a API retorna erro |
| **Open redirect** | Router ignora parâmetro `?redirect=` externo; sempre vai para `/dashboard` | Acessa `/login?redirect=https://evil.com`, confirma que URL final é `/dashboard` |
| **IDOR por URL direto** | API retorna 404 para IDs inexistentes; frontend exibe alerta de erro | Acessa `/products/99999/edit`, verifica alerta e campos vazios |
