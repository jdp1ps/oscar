<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 11/03/16
 * Time: 16:51
 */

namespace Oscar\Provider\Person;


use Monolog\Logger;
use Oscar\Provider\Person\ISyncPersonStrategy;
use Oscar\Entity\Person;
use Oscar\Provider\AbstractOracleProvider;

class SyncPersonHarpege extends AbstractOracleProvider implements ISyncPersonStrategy
{
    const LDAP_ID_FORMAT = 'p%08d';


    const QUERY_PERSONS = 'SELECT * FROM INDIVIDU';

    const QUERY_INM_CONT = <<<'EOD'
SELECT
  INDIVIDU.NO_INDIVIDU harpegeId,
  INDIVIDU.NOM_USUEL nom,
  INDIVIDU.PRENOM prenom,
  INDIVIDU.D_CREATION dateCreated,
  INDIVIDU.D_MODIFICATION dateUpdated,
  INDIVIDU.NO_E_MAIL email,
  indice_majore_d_jour(CONTRAT_AVENANT.INDICE_CONTRAT, CONTRAT_AVENANT.D_FIN_CONTRAT_TRAV, CONTRAT_AVENANT.D_FIN_EXECUTION) INM,
  TYPE_REMUN_CONTRAT.L_TYPE_REMUN_CONTRA
FROM
  INDIVIDU,
  CONTRAT_AVENANT,
  TYPE_REMUN_CONTRAT,
  CONTRAT_TRAVAIL,
  PERSONNEL
WHERE
  ( INDIVIDU.NO_INDIVIDU=PERSONNEL.NO_DOSSIER_PERS(+)  )
  AND  ( CONTRAT_AVENANT.C_TYPE_REMUN_CONTRAT=TYPE_REMUN_CONTRAT.C_TYPE_REMUN_CONTRAT(+)  )
  AND  ( CONTRAT_TRAVAIL.NO_CONTRAT_TRAVAIL=CONTRAT_AVENANT.NO_CONTRAT_TRAVAIL(+) and CONTRAT_TRAVAIL.NO_DOSSIER_PERS=CONTRAT_AVENANT.NO_DOSSIER_PERS(+)
and CONTRAT_AVENANT.TEM_ANNULATION = 'N'  )
  AND  ( PERSONNEL.NO_DOSSIER_PERS=CONTRAT_TRAVAIL.NO_DOSSIER_PERS(+)  )
  AND
  ( CONTRAT_AVENANT.D_DEB_CONTRAT_TRAV <= sysdate and nvl(nvl(CONTRAT_AVENANT.D_FIN_EXECUTION, CONTRAT_AVENANT.D_FIN_CONTRAT_TRAV), sysdate) + 1 >= sysdate and CONTRAT_AVENANT.TEM_ANNULATION = 'N'  )
EOD;
    const QUERY_INM = <<<'EOD'
            SELECT
              INDIVIDU.NO_INDIVIDU harpegeId,
                INDIVIDU.NOM_USUEL nom,
  INDIVIDU.PRENOM prenom,
  INDIVIDU.D_CREATION dateCreated,
  INDIVIDU.D_MODIFICATION dateUpdated,
  INDIVIDU.NO_E_MAIL email,
              recup_remuneration_majore(ELEMENT_CARRIERE.NO_DOSSIER_PERS, ELEMENT_CARRIERE.NO_SEQ_CARRIERE, ELEMENT_CARRIERE.NO_SEQ_ELEMENT, EVOLUTION_CHEVRON.C_CHEVRON) INM
            FROM
              INDIVIDU,
              ELEMENT_CARRIERE,
              EVOLUTION_CHEVRON,
              CARRIERE,
              PERSONNEL
            WHERE
            ( INDIVIDU.NO_INDIVIDU=PERSONNEL.NO_DOSSIER_PERS(+)  )
            AND  ( CARRIERE.NO_DOSSIER_PERS=ELEMENT_CARRIERE.NO_DOSSIER_PERS(+) and CARRIERE.NO_SEQ_CARRIERE=ELEMENT_CARRIERE.NO_SEQ_CARRIERE(+)
                and ELEMENT_CARRIERE.TEM_PROVISOIRE(+) = 'N'
                and ELEMENT_CARRIERE.D_ANNULATION(+) is null  )
              AND  ( ELEMENT_CARRIERE.NO_DOSSIER_PERS=EVOLUTION_CHEVRON.NO_DOSSIER_PERS(+) and ELEMENT_CARRIERE.NO_SEQ_CARRIERE=EVOLUTION_CHEVRON.NO_SEQ_CARRIERE(+) and ELEMENT_CARRIERE.NO_SEQ_ELEMENT=EVOLUTION_CHEVRON.NO_SEQ_ELEMENT(+)  )
                AND  ( PERSONNEL.NO_DOSSIER_PERS=CARRIERE.NO_DOSSIER_PERS(+)  )
                AND
                ( (ELEMENT_CARRIERE.D_EFFET_ELEMENT <= sysdate)  AND (ELEMENT_CARRIERE.D_FIN_ELEMENT + 1>= sysdate  OR ELEMENT_CARRIERE.D_FIN_ELEMENT IS NULL ) and ELEMENT_CARRIERE.D_ANNULATION is NULL  )
EOD;

    private $config;

    public function configure( $config )
    {
        $this->config = $config;
    }

    /**
     * Retourne l'identifiant Ldap calculé à partir du N° Harpège.
     *
     * @param $hargepeId
     * @return string
     */
    public static function getLdapIdFromHarpegeId( $hargepeId ){
        return sprintf(self::LDAP_ID_FORMAT, $hargepeId);
    }

    public static function getHarpegeIdFromLdapId( $ldapId ){
        return preg_replace('/^p0*/', '', $ldapId);
    }


    public function queryPersons(){
        return $this->query(<<<'EOT'
 SELECT * FROM  (
 SELECT
              INDIVIDU.NO_INDIVIDU harpegeId,
                INDIVIDU.NOM_USUEL nom,
  INDIVIDU.PRENOM prenom,
  INDIVIDU.D_CREATION dateCreated,
  INDIVIDU.D_MODIFICATION dateUpdated,
  INDIVIDU.NO_E_MAIL email,
              recup_remuneration_majore(ELEMENT_CARRIERE.NO_DOSSIER_PERS, ELEMENT_CARRIERE.NO_SEQ_CARRIERE, ELEMENT_CARRIERE.NO_SEQ_ELEMENT, EVOLUTION_CHEVRON.C_CHEVRON) INM,
              'Contractuel' typeRemuneration
            FROM
              INDIVIDU,
              ELEMENT_CARRIERE,
              EVOLUTION_CHEVRON,
              CARRIERE,
              PERSONNEL
            WHERE
            ( INDIVIDU.NO_INDIVIDU=PERSONNEL.NO_DOSSIER_PERS(+)  )
            AND  ( CARRIERE.NO_DOSSIER_PERS=ELEMENT_CARRIERE.NO_DOSSIER_PERS(+) and CARRIERE.NO_SEQ_CARRIERE=ELEMENT_CARRIERE.NO_SEQ_CARRIERE(+)
                and ELEMENT_CARRIERE.TEM_PROVISOIRE(+) = 'N'
                and ELEMENT_CARRIERE.D_ANNULATION(+) is null  )
              AND  ( ELEMENT_CARRIERE.NO_DOSSIER_PERS=EVOLUTION_CHEVRON.NO_DOSSIER_PERS(+) and ELEMENT_CARRIERE.NO_SEQ_CARRIERE=EVOLUTION_CHEVRON.NO_SEQ_CARRIERE(+) and ELEMENT_CARRIERE.NO_SEQ_ELEMENT=EVOLUTION_CHEVRON.NO_SEQ_ELEMENT(+)  )
                AND  ( PERSONNEL.NO_DOSSIER_PERS=CARRIERE.NO_DOSSIER_PERS(+)  )
                AND
                ( (ELEMENT_CARRIERE.D_EFFET_ELEMENT <= sysdate)  AND (ELEMENT_CARRIERE.D_FIN_ELEMENT + 1>= sysdate  OR ELEMENT_CARRIERE.D_FIN_ELEMENT IS NULL ) and ELEMENT_CARRIERE.D_ANNULATION is NULL  )
UNION
SELECT
  INDIVIDU.NO_INDIVIDU harpegeId,
  INDIVIDU.NOM_USUEL nom,
  INDIVIDU.PRENOM prenom,
  INDIVIDU.D_CREATION dateCreated,
  INDIVIDU.D_MODIFICATION dateUpdated,
  INDIVIDU.NO_E_MAIL email,
  indice_majore_d_jour(CONTRAT_AVENANT.INDICE_CONTRAT, CONTRAT_AVENANT.D_FIN_CONTRAT_TRAV, CONTRAT_AVENANT.D_FIN_EXECUTION) INM,
  TYPE_REMUN_CONTRAT.L_TYPE_REMUN_CONTRA typeRemuneration
FROM
  INDIVIDU,
  CONTRAT_AVENANT,
  TYPE_REMUN_CONTRAT,
  CONTRAT_TRAVAIL,
  PERSONNEL
WHERE
  ( INDIVIDU.NO_INDIVIDU=PERSONNEL.NO_DOSSIER_PERS(+)  )
  AND  ( CONTRAT_AVENANT.C_TYPE_REMUN_CONTRAT=TYPE_REMUN_CONTRAT.C_TYPE_REMUN_CONTRAT(+)  )
  AND  ( CONTRAT_TRAVAIL.NO_CONTRAT_TRAVAIL=CONTRAT_AVENANT.NO_CONTRAT_TRAVAIL(+) and CONTRAT_TRAVAIL.NO_DOSSIER_PERS=CONTRAT_AVENANT.NO_DOSSIER_PERS(+)
and CONTRAT_AVENANT.TEM_ANNULATION = 'N'  )
  AND  ( PERSONNEL.NO_DOSSIER_PERS=CONTRAT_TRAVAIL.NO_DOSSIER_PERS(+)  )
  AND
  ( CONTRAT_AVENANT.D_DEB_CONTRAT_TRAV <= sysdate and nvl(nvl(CONTRAT_AVENANT.D_FIN_EXECUTION, CONTRAT_AVENANT.D_FIN_CONTRAT_TRAV), sysdate) + 1 >= sysdate and CONTRAT_AVENANT.TEM_ANNULATION = 'N'  )


)
ORDER BY HARPEGEID
EOT
);
    }


    /**
     * Retourne l'INM sur la forme d'un tableau HARPEGEID => INM.
     *
     * @return array
     */
    public function loadINM()
    {
        $INM = [];
        $stid = $this->query(self::QUERY_INM);
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $INM[$row['HARPEGEID']] = $row['INM'];
        }
        $stid = $this->query(self::QUERY_INM_CONT);
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $harpegeId = $row['HARPEGEID'];
            $harpegeINM = $row['INM'];
            if( $harpegeINM ) {
                // Test de conflict
                if( isset($INM[$harpegeId]) && $INM[$harpegeId] != $harpegeINM ){
                    echo sprintf(" /!\\ Un conflit a été détecté pour %s. %s != %s\n", $harpegeId, $INM[$harpegeId], $harpegeINM);
                    continue;
                }
            }
            $INM[$row['HARPEGEID']] = $row['INM'];
        }
        return $INM;
    }

    public function sync(Person $person)
    {
        if( $person->getCodeHarpege() ){
            $id = $person->getCodeHarpege();
        } elseif( $person->getCodeLdap() ) {
            $id = self::getHarpegeIdFromLdapId($person->getCodeLdap());
        } else {
            return false;
        }
        $query = self::QUERY_INM . ' WHERE INDIVIDU.NO_INDIVIDU = ' . $id;
        $r = $this->query($query);
        die();
    }

    public function config()
    {
        return $this->config;
    }


    ////////////////////////////////////////////////////////////////////////////
    public function syncINM( $persons )
    {


    }
}