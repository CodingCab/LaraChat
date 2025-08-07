UPDATE `modules_inventory_movements_statistics_last28days_sale_movements`

SET is_within_7days = 0

WHERE `is_within_7days` = 1 AND `sold_at` < DATE_SUB(now(), INTERVAL 7 DAY);

UPDATE `modules_inventory_movements_statistics_last28days_sale_movements`

SET is_within_14days = 0

WHERE `is_within_14days` = 1 AND `sold_at` < DATE_SUB(now(), INTERVAL 14 DAY);

DELETE FROM `modules_inventory_movements_statistics_last28days_sale_movements`

WHERE `sold_at` < DATE_SUB(now(), INTERVAL 28 DAY);
