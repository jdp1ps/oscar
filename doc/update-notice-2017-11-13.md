# Note mise à jour 13 Novembre 2017

Cette mise à jour inclue des modifications dans **UnicaenApp** afin de résoudre les soucis de configuration Ldap (Configuration des filtres).

## Préparer la mise à jour

Avant de faire un `git pull`, commencer par suivre les étapes suivantes.

### Petite sauvegarde de sécurité

Faire une copie de la base et de l'application afin de facilter un retour en arrière en cas de problème.

### Filtres LDAP

Modifier le fichier de configuration local de Unicaen app **config/autoload/unicaen-app.local.php**, la clef **ldap** propose maintenant 2 clefs pour inclure les différents filtres utilisés dans UnicaenApp (Oscar n'utilise qu'une partie des ces filtres, principalement pour l'authentification).

Vous pouvez vous référer aux modifications manuelles faites lors de l'installation dans le fichier *./vendor/unicaen/unicaen-app/src/UnicaenApp/Mapper/Ldap/People.php*

```php
<?php
// ./config/autoload/unicaen-app.local.php
<?php
/**
 * Dupliquer ce fichier en supprimant .dist
 */
$settings = array(
    /**
     * Informations concernant cette application
     */
    
    'ldap' => array(
        'connection' => array(
            // ne change pas
        ),
        
        // les DN générique
        'dn' => [
            'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=unicaen,dc=fr',
            
            // Non utilisé dans Oscar
            'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=unicaen,dc=fr',
            'GROUPS_BASE_DN'                        => 'ou=groups,dc=unicaen,dc=fr',
            'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=unicaen,dc=fr',
        ],
        'filters' => [
            // Utilisé pour l'authentification
            'LOGIN_FILTER'                          => '(supannAliasLogin=%s)',
            'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
            'CN_FILTER'                             => '(cn=%s)',
            'NAME_FILTER'                           => '(cn=%s*)',
            'UID_FILTER'                            => '(uid=%s)',
            'NO_INDIVIDU_FILTER'                    => '(supannEmpId=%08s)',
            'AFFECTATION_FILTER'                    => '(&(uid=*)(eduPersonOrgUnitDN=%s))',
            'AFFECTATION_CSTRUCT_FILTER'            => '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))',
            'LOGIN_OR_NAME_FILTER'                  => '(|(supannAliasLogin=%s)(cn=%s*))',
            'MEMBERSHIP_FILTER'                     => '(memberOf=%s)',
            'AFFECTATION_ORG_UNIT_FILTER'           => '(eduPersonOrgUnitDN=%s)',
            'AFFECTATION_ORG_UNIT_PRIMARY_FILTER'   => '(eduPersonPrimaryOrgUnitDN=%s)',
            'ROLE_FILTER'                           => '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)',
            'PROF_STRUCTURE'                        => '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))',
            'FILTER_STRUCTURE_DN'		            => '(%s)',
            'FILTER_STRUCTURE_CODE_ENTITE'	        => '(supannCodeEntite=%s)',
            'FILTER_STRUCTURE_CODE_ENTITE_PARENT'   => '(supannCodeEntiteParent=%s)',
        ],
    )
    // etc...
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-app' => $settings,
);
```

### Numérotation automatique des privilèges

A l'origine, les privilèges (fonctionnalités soumises au droits) étaient gérées en base de données *à la main*. Oscar propose maintenant un script qui automatise la création des privilèges. Pour fonctionner correctement, il faut mettre à jour la sequence qui gère les ID.

Connectez vous à la base Postgresql et executez cette requète : 

```sql
select setval('privilege_id_seq',(select max(id)+1 from privilege), false)
```

## Mise à jour

### Mettre oscar en maintenance

Vous pouvez mettre oscar en maintenance : 

```bash
touch MAINTENANCE
```

### Récupération de la dernière version

```bash
git pull
```

### Suppression des anciens Vendors et mise en place des nouveau

```bash
rm -Rf vendor
tar xvfz install/vendor.tar.gz
```

### Mise à jour du modèle

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --force
```

### Mise à jour des privilèges

(Une confirmation vous sera demandée)

```bash
php public/index.php oscar patch checkPrivilegesJSON
```

Vou pouvez remettre Oscar en service : 

```bash
rm MAINTENANCE
```




