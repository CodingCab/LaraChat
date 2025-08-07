DROP TEMPORARY TABLE IF EXISTS temp_itemMovementsStatistics_3982179371;

# SELECT COUNT(*) FROM modules_inventory_movements_statistics_last28days_sale_movements
#     WHERE is_within_7days IS NULL
#       AND sold_at > date_sub(now(), interval 7 day);

CREATE TEMPORARY TABLE temp_itemMovementsStatistics_3982179371 AS (
    SELECT inventory_id,
           sum(quantity_sold) as quantity_sold,
           max(sold_at) as max_sold_at
    FROM modules_inventory_movements_statistics_last28days_sale_movements
    WHERE is_within_7days IS NULL
      AND sold_at > date_sub(now(), interval 7 day)
    GROUP BY inventory_id
);

# SELECT COUNT(DISTINCT inventory_id) FROM modules_inventory_movements_statistics_last28days_sale_movements
# WHERE is_within_7days IS NULL
#   AND sold_at > date_sub(now(), interval 7 day);
# SELECT COUNT(*) FROM temp_itemMovementsStatistics_3982179371;

INSERT INTO inventory_movements_statistics (inventory_id, product_id, warehouse_code, warehouse_id, created_at, updated_at)
    SELECT inventory.id, inventory.product_id, inventory.warehouse_code, inventory.warehouse_id, now(), now()
    FROM temp_itemMovementsStatistics_3982179371 as tempTable
    LEFT JOIN inventory_movements_statistics
        ON inventory_movements_statistics.inventory_id = tempTable.inventory_id
    LEFT JOIN inventory
        ON inventory.id = tempTable.inventory_id
    WHERE inventory_movements_statistics.inventory_id is null;

# SELECT * FROM inventory_movements_statistics
#     RIGHT JOIN temp_itemMovementsStatistics_3982179371 as tempTable
#     ON inventory_movements_statistics.inventory_id = tempTable.inventory_id;

UPDATE inventory_movements_statistics
    RIGHT JOIN temp_itemMovementsStatistics_3982179371 as tempTable
    ON inventory_movements_statistics.inventory_id = tempTable.inventory_id
SET
    quantity_sold_last_7_days = IFNULL(quantity_sold_last_7_days, 0) + quantity_sold,
    quantity_sold_last_14_days = IFNULL(quantity_sold_last_14_days, 0) + quantity_sold,
    quantity_sold_last_14_days = IFNULL(quantity_sold_last_28_days, 0) + quantity_sold;


# SELECT tempTable.max_sold_at, modules_inventory_movements_statistics_last28days_sale_movements.sold_at, date_sub(now(), interval 7 day)
# FROM modules_inventory_movements_statistics_last28days_sale_movements
# LEFT JOIN temp_itemMovementsStatistics_3982179371 as tempTable
#     ON tempTable.inventory_id = modules_inventory_movements_statistics_last28days_sale_movements.inventory_id
# WHERE is_within_7days IS NULL
#   AND modules_inventory_movements_statistics_last28days_sale_movements.sold_at <= tempTable.max_sold_at;

UPDATE modules_inventory_movements_statistics_last28days_sale_movements
LEFT JOIN temp_itemMovementsStatistics_3982179371 as tempTable
    ON tempTable.inventory_id = modules_inventory_movements_statistics_last28days_sale_movements.inventory_id
SET is_within_7days = 1,
    is_within_14days = 1,
    is_within_28days = 1
WHERE is_within_7days IS NULL
  AND modules_inventory_movements_statistics_last28days_sale_movements.sold_at <= tempTable.max_sold_at;

DROP TEMPORARY TABLE IF EXISTS temp_itemMovementsStatistics_3982179371;
