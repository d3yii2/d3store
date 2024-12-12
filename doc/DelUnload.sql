SELECT
tl.quantity,  
  tl.`remain_quantity` + tu.quantity
FROM
  `store_transactions` tu
  INNER JOIN `store_woff` w
    ON tu.`id` = w.`unload_tran_id`
    INNER JOIN `store_transactions` tl
    ON w.`load_tran_id` = tl.id
WHERE tu.`id` = '236621';

UPDATE
 `store_transactions` tu
  INNER JOIN `store_woff` w
    ON tu.`id` = w.`unload_tran_id`
    INNER JOIN `store_transactions` tl
    ON w.`load_tran_id` = tl.id
    SET  tl.`remain_quantity` = tl.`remain_quantity` + tu.quantity
WHERE tu.`id` = '236621';

DELETE FROM `store_woff` WHERE `unload_tran_id` = 236621;
DELETE FROM `store_transactions` WHERE `id` = 236621;