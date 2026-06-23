# Backend — Desafio Systock

Laravel 13 + PHP 8.4, servido via FrankenPHP.

## Estrutura principal

```
app/
  Http/
    Controllers/Api/   AuthController, UserController, CategoryController,
                       ProductController, StockMovementController
    Middleware/        EnsureIdempotency, RestrictSwaggerToNonProduction
    Requests/          Form Requests de validação por recurso
  Models/              User, Category, Product, StockMovement, IdempotencyKey
  Policies/            UserPolicy (IDOR — API1)
  Support/             Cpf (geração/formatação de CPF)
database/
  migrations/          Schema completo (users, categories, products,
                       stock_movements, idempotency_keys)
  seeders/             DatabaseSeeder → UserSeeder, CategorySeeder,
                       ProductSeeder, StockMovementSeeder
routes/
  api.php              Rotas públicas (login) + grupo auth:sanctum
```

## Como rodar localmente

```bash
# a partir da raiz do projeto
docker-compose up --build

# reset completo do banco (dentro do container)
php artisan migrate:fresh --seed --force
```

## Testes

```bash
php artisan test          # PHPUnit (suite base do Laravel)
vendor/bin/pint --test    # lint PHP (Laravel Pint)
```

## Documentação OpenAPI

Disponível em `http://localhost:8000/api/documentation` (apenas em ambiente não-produtivo).
