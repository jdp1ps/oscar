# Authentification

*Oscar* s'appuie sur la librairie  [UnicaenAuth](https://git.unicaen.fr/lib/unicaen/auth) pour gérer l'authentification des utilisateurs. 

Les systèmes pris en charge sont : 

 - **LDAP**
 - **CAS**
 - **Local** via la BDD
 - **Shibboleth**

## Informations générales

Les fichiers impliqués dans la configuration sont : 

 - `config/autoload/unicaen-app.local.php`
 - `config/autoload/unicaen-auth.local.php`

> *UnicaenAuth* étant un module de [UnicaenApp](https://git.unicaen.fr/lib/unicaen/app), la configuration de l'authentification est basée sur 2 fichiers.

Le dépôt initiale contient des fichiers d'exemple.

- `config/autoload/unicaen-app.local.php`
- `config/autoload/unicaen-auth.local.php`

## Fichiers de configuration

```bash
# Copiez les fichiers de base
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php
cp config/autoload/unicaen-auth.local.php.dist config/autoload/unicaen-auth.local.php
```

## LDAP

L'authentification LDAP est renseignée dans le fichier **config/autoload/unicaen-app.local.php** :

```php
<?php
/**
 * Dupliquer ce fichier en supprimant .dist
 */
$settings = array(
    /**
     * Informations concernant cette application
     */
    
    'ldap' => array(
        /////////////////////////////////////// ACCES au SERVEUR LDAP
        'connection' => array(
            'default' => array(
                'params' => array(
                    'host'                => 'XXX.XXXXX.XX',
                    'port'                => 389,
                    'username'            => "PERSEVAL",
                    'password'            => "CPAFO",
                    'baseDn'              => "XXXXXXXXXXX",
                    'bindRequiresDn'      => true,
                    'accountFilterFormat' => "XXXXXXXXXXX",
                )
            )
        ),

        /////////////////////////////////////// DOMAINES
        'dn' => [
            'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=unicaen,dc=fr',
            'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=unicaen,dc=fr',
            'GROUPS_BASE_DN'                        => 'ou=groups,dc=unicaen,dc=fr',
            'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=unicaen,dc=fr',
        ],

        /////////////////////////////////////// FILTRES
        'filters' => [
            // --- AUTHENTIFICATION
            // Login (identifiant de l'utilisateur)
            'LOGIN_FILTER'                          => '(supannAliasLogin=%s)',
            
            'LOGIN_OR_NAME_FILTER'                  => '(|(supannAliasLogin=%s)(cn=%s*))',
            'MEMBERSHIP_FILTER'                     => '(memberOf=%s)',

            // Les filtres suivant ne sont pas utilisés dans Oscar
            'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
            'CN_FILTER'                             => '(cn=%s)',
            'NAME_FILTER'                           => '(cn=%s*)',
            'UID_FILTER'                            => '(uid=%s)',
            'NO_INDIVIDU_FILTER'                    => '(supannEmpId=%08s)',
            'AFFECTATION_FILTER'                    => '(&(uid=*)(eduPersonOrgUnitDN=%s))',
            'AFFECTATION_CSTRUCT_FILTER'            => '(&(uid=*)(|(ucbnSousStructure=%s;*)(supannAffectation=%s;*)))',
            'AFFECTATION_ORG_UNIT_FILTER'           => '(eduPersonOrgUnitDN=%s)',
            'AFFECTATION_ORG_UNIT_PRIMARY_FILTER'   => '(eduPersonPrimaryOrgUnitDN=%s)',
            'ROLE_FILTER'                           => '(supannRoleEntite=[role={SUPANN}%s][type={SUPANN}%s][code=%s]*)',
            'PROF_STRUCTURE'                        => '(&(eduPersonAffiliation=teacher)(eduPersonOrgUnitDN=%s))',
            'FILTER_STRUCTURE_DN'		            => '(%s)',
            'FILTER_STRUCTURE_CODE_ENTITE'	        => '(supannCodeEntite=%s)',
            'FILTER_STRUCTURE_CODE_ENTITE_PARENT'   => '(supannCodeEntiteParent=%s)',
        ],
        /****/
    ),
    
    // ...
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-app' => $settings,
);
```

### LDAP Non-Supann

Pour les LDAP non-spann, il est possible que le champ utilisé pour l'autentification soit différent de **supannaliaslogin**, généralement le champ uid. Si c'est la cas, il faudra modifier le fichier **unicaen-app.local.php** : 

```php
<?php
//config/autoload/unicaen-app.local.php
$settings = array(
  // LDAP    
  'ldap' => array(
    'connection' => array(
      'default' => array(
        'params' => array(
          // ...
          'accountFilterFormat' => '(&(objectClass=posixAccount)(uid=%s))',
        )
      )
    )
  ),
  // ...
    'filters' => [
        'LOGIN_FILTER'                          => '(uid=%s)',
        'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
        // ...
    ],
);
```

vous pouvez éditer le fichier config/autoload/unicaen-auth.local.php en renseignant la clef ldap_username :