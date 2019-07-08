
-- ---------------------------------------------------------------------------
-- BUG : Mauvaise affectation des ORGANISATIONS/PERSONNES aux ACTIVITÉS
-- ---------------------------------------------------------------------------


-- ---------------------------------------------------------------------------
-- PERSONNES <> ACTIVITÉS

-- Affichage des roles problématiques
SELECT * FROM activityperson
  WHERE roleobj_id IS NULL AND role != '';

-- MAJ des Rôles
UPDATE activityperson a SET roleobj_id = (SELECT r.id FROM user_role r WHERE r.role_id = a.role)
  WHERE roleobj_id IS NULL AND a.role != '';

-- ---------------------------------------------------------------------------
-- ORGANIZATIONS <> ACTIVITÉS

-- Afficher les affectations problématiques
SELECT * FROM activityorganization
  WHERE roleobj_id IS NULL AND role != '';

-- Mise à jour si possible
UPDATE activityorganization a SET roleobj_id = (SELECT r.id FROM organizationrole r WHERE r.label = a.role)
  WHERE roleobj_id IS NULL AND a.role != '';
------------------------------------------------------------------------------------------------------------------------

