UPDATE external_products
    JOIN products ON products.sku = external_products.external_sku
    SET external_products.product_id = products.id
WHERE external_products.product_id IS NULL;

INSERT INTO products (sku, name, supplier)
SELECT external_products.external_sku, external_products.external_name, external_products_view.producent
FROM external_products

LEFT JOIN external_products_view ON external_products.id = external_products_view.id
WHERE external_products.product_id IS NULL

ON DUPLICATE KEY UPDATE
     name = VALUES(name),
     supplier = VALUES(supplier);

UPDATE external_products
    JOIN products ON products.sku = external_products.external_sku
    SET external_products.product_id = products.id
WHERE external_products.product_id IS NULL;

INSERT INTO models_tags(model_type, model_id, tag_type, tag_name)
SELECT 'App\\Models\\Product', product_id, 'rozmiar_paczki_labera', CONCAT('L: ', count_items_labera)
FROM external_products_view
WHERE external_products_view.product_id IS NOT NULL
    AND count_items_labera != 'null'
ON DUPLICATE KEY UPDATE
    tag_name = VALUES(tag_name);

INSERT INTO models_tags(model_type, model_id, tag_type, tag_name)
SELECT 'App\\Models\\Product', product_id, 'rozmiar_paczki_korallo', CONCAT('K: ', count_items_korallo)
FROM external_products_view
WHERE external_products_view.product_id IS NOT NULL
  AND count_items_korallo != 'null'
ON DUPLICATE KEY UPDATE
    tag_name = VALUES(tag_name);

UPDATE tempTable_Locations
JOIN products ON products.sku = tempTable_Locations.sku
SET tempTable_Locations.product_id = products.id
WHERE tempTable_Locations.product_id IS NULL;

UPDATE inventory
JOIN tempTable_Locations ON inventory.product_id = tempTable_Locations.product_id
    AND inventory.warehouse_code = 'KOR'
SET inventory.shelve_location = tempTable_Locations.lokalizacja_korallo;

UPDATE inventory
JOIN tempTable_Locations ON inventory.product_id = tempTable_Locations.product_id
    AND inventory.warehouse_code = 'LAB'
SET inventory.shelve_location = tempTable_Locations.lokalizacja_labera;
