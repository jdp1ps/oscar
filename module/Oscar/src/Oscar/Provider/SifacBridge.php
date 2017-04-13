<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-03-15 11:51
 * @copyright Certic (c) 2016
 */

namespace Oscar\Provider;


class SifacBridge extends AbstractOracleProvider
{

    private $_params;

    /**
     * Configuration pour la connexion à SIFAC.
     *
     * @param array $param
     */
    public function configure( $param )
    {
        $this->_params = $param;
    }

    public function config()
    {
        return $this->_params;
    }

    public function getOrganizations()
    {
        return $this->query('select
          lifnr "ID",
          land1 "country",
          name1 "name",
          name2 "name2",
          ort01 "city",
          pfach "bp",
          pstl2 "bp",
          pstlz "zipcode",
          stras "rue1",
          mcod1 "nom ",
          mcod2 "nom2",
          mcod3 city2 ,
          anred "type",
          konzs "groupid",
          ktokk "group",
          kunnr "client",
          stcd1 "siret",
          stkzu "TVACA",
          telbx "email",
          telf1 "tel",
          telfx "telecopie",
          telx1 "telex",
          stceg "numtvaca"
          from sapsr3.lfa1
          where loevm  = \' \' and sperr = \' \' and sperm = \' \' and ktokk in (\'Z001\',\'Z002\',\'Z003\',\'Z004\',\'Z006\') order by lifnr');
    }

    public function getClients()
    {
        return $this->query('select
            kunnr "ID",
            land1 "country",
            name1 "name",
            name2,
            ort01 "city",
            pstlz "zipcode",
            stras "rue1",
            telf1 "tel",
            telfx "telecopie",
            anred "type",
            konzs "groupid",
            ktokd "accountgroup",
            lifnr "fourn",
            pfach "bp",
            pstl2 "bpp",
            stcd1 "siret"
            from sapsr3.kna1
            where
              aufsd = \' \'
            and faksd = \' \'
            and lifsd = \' \'
            and loevm = \' \'
            and sperr= \' \'
            and sperz = \' \'
            and ktokd in (\'Z001\',\'Z002\',\'Z003\',\'Z004\',\'Z005\',\'Z006\')');
    }

}