-- 1. Produtos em ruptura de estoque (saldo atual abaixo do mínimo)
--    Retorna SKU, nome, saldo calculado e quantidade mínima configurada.
SELECT
    p.sku,
    p.name,
    p.min_quantity,
    COALESCE(SUM(CASE WHEN sm.type = 'in' THEN sm.quantity ELSE -sm.quantity END), 0) AS saldo_atual
FROM products p
LEFT JOIN stock_movements sm ON sm.product_id = p.id
GROUP BY p.id, p.sku, p.name, p.min_quantity
HAVING saldo_atual < p.min_quantity
ORDER BY saldo_atual ASC;

-- 2. Movimentações dos últimos 30 dias agrupadas por dia
--    Usado para alimentar o gráfico de giro de estoque no dashboard.
SELECT
    DATE(created_at)           AS data,
    SUM(quantity)              AS total_movimentado,
    COUNT(*)                   AS total_operacoes,
    SUM(CASE WHEN type = 'in'  THEN quantity ELSE 0 END) AS total_entradas,
    SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) AS total_saidas
FROM stock_movements
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY DATE(created_at)
ORDER BY data;

-- 3. Top 10 usuários com mais movimentações no sistema
--    Útil para auditoria e rastreabilidade de operações por operador.
SELECT
    u.id,
    u.name,
    u.email,
    u.role,
    COUNT(sm.id)               AS total_movimentacoes,
    SUM(sm.quantity)           AS total_itens_movimentados
FROM users u
LEFT JOIN stock_movements sm ON sm.user_id = u.id
GROUP BY u.id, u.name, u.email, u.role
ORDER BY total_movimentacoes DESC
LIMIT 10;
