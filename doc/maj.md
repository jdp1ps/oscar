# FAITES EN PROD

## Responsable > Responsable scientifique

Mise à jour du rôle : 

```sql
UPDATE projectmember SET role = 'Responsable scientifique' WHERE role = 'Responsable'
UPDATE activityperson SET role = 'Responsable scientifique' WHERE role = 'Responsable'

-- Suppression des doublons personnes <> activités avec le role 'Responsable scientifique'
DELETE FROM activityperson 
WHERE id IN (
	SELECT ID FROM (SELECT MIN(id) id, person_id || '-' || activity_id as TAG, COUNT(person_id) num
			FROM activityperson
			WHERE role = 'Responsable scientifique'
			GROUP BY tag)AS roled 
	WHERE NUM > 1
	) 
```

Suppression des doublons consécutif à ce changement

# A FAIRE

## Membres et partenaires 

Mise à jour des partenaires & membres du projet vers les activités du projet : 

```bash
php public/index.php centaure sync personToActivity
```

## Composante > Tutelle de gestion

Mise à jour du rôle : 

```sql
UPDATE activityorganization SET role = 'Tutelle de gestion' WHERE role = 'Composante de gestion';
UPDATE projectpartner SET role = 'Tutelle de gestion' WHERE role = 'Composante de gestion';
```



## Recalcule des statuts


```bash
php public/index.php oscar activity:status
```