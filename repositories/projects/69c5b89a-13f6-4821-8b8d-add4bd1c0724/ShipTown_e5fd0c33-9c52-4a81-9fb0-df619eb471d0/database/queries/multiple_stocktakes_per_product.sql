SELECT products.sku, products.name, warehouses.code, count(*) as times_counted, floor(inventory.quantity) as current_quantity, max(inventory_movements.created_at) as last_counted

FROM `inventory_movements`

         LEFT JOIN warehouses on warehouses.id = warehouse_id
         LEFT JOIN products on products.id = product_id
         LEFT JOIN inventory on inventory.id = inventory_id

WHERE `description` = 'stocktake'
  AND products.sku != '45'
  AND warehouses.code not in ('TRA','100')
  AND warehouses.code in ('NES')

  AND inventory_movements.created_at > '05 Dec 2022'

GROUP BY inventory_id

HAVING count(*) > 3

ORDER BY count(*) DESC

LIMIT 1000
