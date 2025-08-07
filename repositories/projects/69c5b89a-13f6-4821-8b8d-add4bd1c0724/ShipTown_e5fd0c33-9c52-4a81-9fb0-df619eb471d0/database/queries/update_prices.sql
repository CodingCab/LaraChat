UPDATE products_prices AS pp_target
    JOIN products_prices AS pp_source
    ON pp_target.product_id = pp_source.product_id
        AND pp_source.warehouse_code = '99'
        AND pp_source.cost != 0
SET pp_target.cost = pp_source.cost
WHERE pp_target.cost = 0

LIMIT 500000;

UPDATE products_prices AS pp_target
JOIN products_prices AS pp_source
    ON pp_target.product_id = pp_source.product_id
    AND pp_source.warehouse_code = '99'
    AND pp_source.price != 0
SET pp_target.price = pp_source.price
WHERE pp_target.price = 0

LIMIT 100000;

UPDATE inventory_movements

JOIN inventory_movements_daily_statistics
ON inventory_movements_daily_statistics.last_inventory_movement_id = inventory_movements.id
    AND inventory_movements.unit_cost = 0

LEFT JOIN products_prices
ON products_prices.inventory_id = inventory_movements_daily_statistics.inventory_id

SET unit_cost = products_prices.cost

WHERE inventory_movements_daily_statistics.date = '2025-01-31'

LIMIT 5000
