# Configuration du module "SIGNATURE"

## Technique

### Fichiers de configuration
```
cp config/autoload/unicaen-signature.local.php.dist config/autoload/unicaen-signature.local.php
```

### Activer les éléments d'UI (Interface d'administration)
```bash
# Création d'un lien symbolique pour les éléments d'interface
cd public/unicaen
ln -s ../../vendor/unicaen/signature/public/dist signature
```

### Modèle de données

> Les mises à jour du modèle de données sont automatiquement intégrées à la procédure de mise à jour standard.

### Accès/Privilèges

Via l'interface d'administration

