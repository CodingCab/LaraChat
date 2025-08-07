SELECT
--     select just date
    DATE(inventory_movements.created_at) as created_at,
    inventory_movements.id,

FROM inventory_movements
WHERE created_at BETWEEN '2023-10-01 00:00:00' AND '2023-10-31 23:59:59'
