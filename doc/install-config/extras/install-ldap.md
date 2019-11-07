# Configurer LDAP

Le système d'authentification de Oscar est prévu pour fonctionner avec LDAP. Il permet au personne disposant d'un

## UnicaenAuth

La configuration de **UnicaenApp** et **UnicaenAuth** (surcouches utilisées dans
Oscar) ont leurs fichiers de configuration respectifs dans le dossier `/config/autoload` :

 - Pour UnicaenApp, `config/autoload/unicaen-app.local.php`
 - Pour UnicaenAuth, `config/autoload/unicaen-auth.local.php`

Des fichiers d'exemple sont disponibles avec l'extension `.dist`.

#### Configurer l'authentification LDAP

Copier le fichier d'exemple :

```bash
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php
vi !$
```

Puis compléter la configuration :

```php
<?php
//config/autoload/unicaen-app.local.php
$settings = array(
  // LDAP    
  'ldap' => array(
    'connection' => array(
      'default' => array(
        'params' => array(
          'host'                => 'ldap.domain.tdl',
          'port'                => 389,
          'username'            => 'uid=identifiant,ou=system,dc=domain,dc=fr',
          'password'            => 'P@$$W0rD',
          'baseDn'              => 'ou=people,dc=domain,dc=fr',
          'bindRequiresDn'      => true,
          'accountFilterFormat' => '(&(objectClass=posixAccount)(supannAliasLogin=%s))',
        )
      )
    )
  ),
  // etc ...
);
```

NOTE : Concernant le filtre `accountFilterFormat`, si votre LDAP est non supann, penser à consulter la partie suivante.

#### Authentification LDAP : Non-Supann

Pour les LDAP **non-spann**, il est possible que le champ utilisé pour l'autentification soit différent de **supannaliaslogin**, généralement le champ **uid**. Si c'est la cas, vous pouvez éditer le fichier **config/autoload/unicaen-auth.local.php** en renseignant la clef `ldap_username` : 

```php
<?php
//config/autoload/unicaen-auth.local.php
$settings = array(
    // ...
    // Champ utilisé pour l'autentification (côté LDAP)
    // exemple avec UID au lieu de supannaliaslogin
    'ldap_username' => 'uid',
);

return array(
    'unicaen-auth' => $settings,
);
```

Vous devrez également adapter les filtres LDAP en conséquence dans le fichier **config/autoload/unicaen-app.local.php** : 

```php
<?php
//config/autoload/unicaen-app.local.php
$settings = array(
    //
    'ldap' => array(
        'connection' => array(
            // ...
        ),
        'dn' => [
            'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=domain,dc=fr',
            'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=domain,dc=fr',
            'GROUPS_BASE_DN'                        => 'ou=groups,dc=domain,dc=fr',
        ],
        
        'filters' => [
            'LOGIN_FILTER'                          => '(uid=%s)',
            'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
            'CN_FILTER'                             => '(cn=%s)',
            'NAME_FILTER'                           => '(cn=%s*)',
            'UID_FILTER'                            => '(uid=%s)',
            // Les autres filtres sont optionnels
        ],
        /****/
    ),
    // ...
);

return array(
    'unicaen-app' => $settings,
);
```

Pensez également à corriger la clef `accountFilterFormat` dans la connexion LDAP renseignée dans le fichier `config/autoload/unicaen-app.local.php` : 

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
          'accountFilterFormat' => '(&(objectClass=posixAccount)(uid=%s))', // << ICI
        )
      )
    )
  ),
  // etc ...
);
```


#### Configurer l'authentification CAS

**UnicaenAuth** va permettre de configurer l'accès à Oscar en utilisant le *Cas*.

```php
<?php
//config/autoload/unicaen-auth.local.php
$settings = array(
    'cas' => array(
        'connection' => array(
            'default' => array(
                'params' => array(
                    'hostname' => 'cas.domain.fr',
                    'port' => 443,
                    'version' => "2.0",
                    'uri' => "",
                    'debug' => false,
                ),
            ),
        ),
    ),
    /**
     * Identifiants de connexion LDAP autorisés à faire de l'usurpation d'identité.
     * NB: à réserver exclusivement aux tests.
     */
    'usurpation_allowed_usernames' => array('brucebanner', 'dieu'),
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-auth' => $settings,
);
```

### Relation Person / Authentification

Une option a été ajouté pour force Oscar à ignorer la casse lorsque il établit la relation entre l'indentifiant de connexion et le login de la fiche personne. Par défaut cette option est ignorée, pour l'activier, éditer le fichier de configuration local : 

```php
<?php
// config/autoload.local.php
// ...
return array(
    // ...
    // Oscar
    'oscar' => [
        // ...
        'authPersonNormalize' => true,
    ]
);
```

### Usurpation

Pour les copies de développement/préprod, l'option `usurpation_allowed_usernames`
permet de s'identifier à la place d'un utilisateur.

On utilise l'identifiant `compte=compteusurpation` où `compte` correspond à
l'identifiant principale (qui doit figurer dans le tableau `usurpation_allowed_usernames`),
et `compteusurpation` correspond au compte usurpé. Le mot de passe est celui de `compte`.

Cette option n'est pas compatible avec l'identification CAS.

BUG CONNU : Cette option est utilisé pour les tests uniquement. Il peut arriver que UnicaenApp
ait des difficultés à detecter le rôle à charger lors d'une usurpation. Vérifiez toujours
lors d'une usurpation qu'un rôle est bien actif en cliquant sur le nom du compte
dans le menu principal.