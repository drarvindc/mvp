-- Make sure both pets belong to the same family (owner_id = 2)
UPDATE pets SET owner_id = 2 WHERE unique_id IN ('250001','250002');

-- Give them names (so they won’t show as "Unnamed Pet")
UPDATE pets SET pet_name = 'Bruno' WHERE unique_id = '250001';
UPDATE pets SET pet_name = 'Misty' WHERE unique_id = '250002';
