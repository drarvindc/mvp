-- 002_breeds.sql
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'Labrador Retriever' FROM species s WHERE s.name='Canine';
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'German Shepherd' FROM species s WHERE s.name='Canine';
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'Persian' FROM species s WHERE s.name='Feline';
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'Siamese' FROM species s WHERE s.name='Feline';
