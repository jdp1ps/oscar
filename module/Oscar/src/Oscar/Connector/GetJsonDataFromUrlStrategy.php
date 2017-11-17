<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-16 17:44
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;


class GetJsonDataFromUrlStrategy extends GetJsonDataStrategy
{

    private $urlOne;
    private $urlAll;

    /**
     * GetJsonDataFromUrl constructor.
     * @param $urlOne
     * @param $urlAll
     */
    public function __construct($urlOne, $urlAll)
    {
        $this->urlOne = $urlOne;
        $this->urlAll = $urlAll;
    }


    /**
     * @return mixed
     */
    public function getUrlOne()
    {
        return $this->urlOne;
    }

    /**
     * @return mixed
     */
    public function getUrlAll()
    {
        return $this->urlAll;
    }

    /**
     * @return mixed un tableau de stdObject
     */
    public function getAll()
    {
        return $this->getDataFromUrl($this->getUrlAll());
    }

    /**
     * @param $id
     * @return mixed stdObject
     */
    public function getOne($id)
    {
        return $this->getDataFromUrl(sprintf($this->getUrlOne(), $id));
    }

    protected function getDataFromUrl($url)
    {
        static $curl;

        if ($curl === null) {
            $curl = curl_init();
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);

        $return = curl_exec($curl);

        if (false === $return) {
            throw new ConnectorException("L'URL n'a pas fournis les données attendues");
        }

        curl_close($curl);

        return $this->stringToJson($return);
    }
}