SELECT
       warehouses.code,
       day(inventory_movements.created_at),
#        hour(inventory_movements.created_at),
       count(*)

FROM `inventory_movements`

LEFT JOIN warehouses ON warehouses.id = inventory_movements.warehouse_id

WHERE
      `description` = 'stocktake'
  AND datediff(inventory_movements.created_at, now()) > -7

GROUP BY
         warehouses.code,
         day(inventory_movements.created_at)
#          , hour(inventory_movements.created_at)

ORDER BY warehouses.code, datediff(inventory_movements.created_at, now()) ASC

LIMIT 500
