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
            'default' => array(
                'params' => array(
                    'host'                => 'xxxxxxxxxxxxxx',
                    'port'                => 389,
                    'username'            => "xxxxxxxxxxxxxxxxxxx",
                    'password'            => "xxxxxxxxxxxxxxx",
                    'baseDn'              => "dc=unilim,dc=fr",
                    'bindRequiresDn'      => true,
                    'accountFilterFormat' => "(&(objectclass=eduPerson)(uid=%s))",
                )
            )
        ),
        'dn' => [
            'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=unilim,dc=fr',
            'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=unilim,dc=fr',
            'GROUPS_BASE_DN'                        => 'ou=groups,dc=unilim,dc=fr',
            'STRUCTURES_BASE_DN'                    => 'ou=structures,dc=unilim,dc=fr',
        ],
        
        'filters' => [
            'LOGIN_FILTER'                          => '(&(objectclass=eduPerson)(uid=%s))',
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
            'FILTER_PERSON_AFFILIATION'             => '(&(objectClass=inetOrgPerson)(eduPersonAffiliation=member)(%s))',
        ],
        /****/
    ),
    /**
     * Options concernant l'envoi de mail par l'application
     */
    'mail' => array(
       // transport des mails
       'transport_options' => array(
           'host' => 'smtp.unilim.fr',
           'port' => 25,
       ),
       // adresses à substituer à celles des destinataires originaux ('CURRENT_USER' équivaut à l'utilisateur connecté)
       'redirect_to' => array('damien.rieu@unilim.fr', /*'CURRENT_USER'*/),
       // désactivation totale de l'envoi de mail par l'application
       'do_not_send' => false,
    ),
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-app' => $settings,
);
