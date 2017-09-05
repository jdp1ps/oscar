<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 01/09/17
 * Time: 09:59
 */

namespace Oscar\Utils;


class ActivityCSVToObject
{
    private $enabledOrganisationRole;
    private $enabledPersonRole;
    private $enabledType;
    private $correspondanceRolesActivites;
    private $correspondanceRolesOrga;

    public function __construct($correspondanceRolesActivites, $correspondanceRolesOrga)
    {
        $this->correspondanceRolesActivites = $correspondanceRolesActivites;
        $this->correspondanceRolesOrga = $correspondanceRolesOrga;
    }

    public function convert( $filepath ){
        $handler = fopen($filepath, 'r');

        $headers = fgetcsv($handler);
        $out = [];
        while( ($datas = fgetcsv($handler)) !== FALSE ){
            $data = [
                'uid'           => $datas[0],
                'acronym'       => $datas[1],
                'projectlabel'  => $datas[2],
                'label'         => $datas[3],
                'pfi'           => $datas[4],
                'datepfi'       => $datas[5],
                'amount'        => $datas[6],
                'type'          => $datas[7],
                'datestart'     => $datas[8],
                'dateend'       => $datas[9],
                'datesigned'      => $datas[10],
                'organizations' => [],
                'persons' => []
            ];

            // Traitement des organisations
            foreach ($this->correspondanceRolesActivites as $role=>$index){
                if( $index !== false)
                    $this->extractSeparatedOrNull($role, $datas[$index], $data['persons']);
            }

            // Traitement des organisations
            foreach ($this->correspondanceRolesOrga as $role=>$index){
                if( $index !== false)
                    $this->extractSeparatedOrNull($role, $datas[$index], $data['organizations']);
            }

            $out[] = (object)$data;
        }
        return $out;
    }

    private function extractSeparatedOrNull( $key, $data, &$destination ){
        if( !$data ){
            return null;
        }
        $destination[$key] = explode('$', $data);
    }
}