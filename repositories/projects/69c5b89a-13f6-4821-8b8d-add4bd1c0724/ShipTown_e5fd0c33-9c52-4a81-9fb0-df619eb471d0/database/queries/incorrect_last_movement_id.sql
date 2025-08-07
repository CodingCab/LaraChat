#SELECT count(*) FROM inventory
#SELECT (SELECT MAX(ID) FROM inventory_movements WHERE inventory_id = inventory.id), inventory.* FROM `inventory`
update inventory set inventory.last_movement_id = (SELECT MAX(ID) FROM inventory_movements WHERE inventory_id = inventory.id)
WHERE inventory.last_movement_id != (SELECT MAX(ID) FROM inventory_movements WHERE inventory_id = inventory.id)
LIMIT 20;

SELECT count(*) FROM inventory
WHERE inventory.last_movement_id != (SELECT MAX(ID) FROM inventory_movements WHERE inventory_id = inventory.id)
LIMIT 10
