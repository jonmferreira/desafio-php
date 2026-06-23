# Components

Componentes Vue reutilizáveis do projeto, organizados por domínio.

## Estrutura

```
components/
  products/
    MovementForm/    Formulário de registro de entrada/saída de estoque
    StockBadge/      Badge colorido de quantidade em estoque (abaixo/acima do mínimo)
  shared/
    ConfirmDialog/   Diálogo de confirmação genérico (usado em exclusões)
```

## Importação

Os componentes **não** são auto-importados — cada uso requer `import` explícito.
Apenas componentes do Vuetify (`v-btn`, `v-card`, etc.) são auto-importados via
`vite-plugin-vuetify` (`autoImport: true` em `vite.config.ts`).
