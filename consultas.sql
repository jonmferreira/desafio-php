-- 1. Capital em estoque por cliente (user)
--    Soma do valor (valor_unitario × itens_por_fardo × quantidade_fardos) de todos os
--    lote_items dos lotes pertencentes a cada usuário.
SELECT
    u.id,
    u.name,
    u.email,
    COALESCE(SUM(li.valor_unitario * li.itens_por_fardo * li.quantidade_fardos), 0) AS capital_em_estoque
FROM users u
LEFT JOIN lotes l ON l.user_id = u.id
LEFT JOIN lote_items li ON li.lote_id = l.id
GROUP BY u.id, u.name, u.email
ORDER BY capital_em_estoque DESC;

-- 2. Produtos com estoque crítico (total de fardos abaixo do mínimo)
--    Considera todos os lote_items de todos os lotes, independente do cliente.
SELECT
    p.sku,
    p.name,
    p.min_fardos,
    COALESCE(SUM(li.quantidade_fardos), 0) AS total_fardos_em_estoque
FROM products p
LEFT JOIN lote_items li ON li.product_id = p.id
GROUP BY p.id, p.sku, p.name, p.min_fardos
HAVING COALESCE(SUM(li.quantidade_fardos), 0) < p.min_fardos
ORDER BY total_fardos_em_estoque ASC;

-- 3. Maior estoque de cada produto por cliente
--    Para cada (usuário, produto), soma os fardos em todos os lotes daquele usuário.
SELECT
    u.id   AS user_id,
    u.name AS cliente,
    p.id   AS product_id,
    p.sku,
    p.name AS produto,
    COALESCE(SUM(li.quantidade_fardos), 0) AS total_fardos
FROM users u
JOIN lotes l ON l.user_id = u.id
JOIN lote_items li ON li.lote_id = l.id
JOIN products p ON p.id = li.product_id
GROUP BY u.id, u.name, p.id, p.sku, p.name
ORDER BY u.id, total_fardos DESC;
