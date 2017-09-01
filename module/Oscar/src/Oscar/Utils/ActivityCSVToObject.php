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
                'project'       => $datas[0],
                'acronym'       => $datas[1],
                'projectlabel'  => $datas[2],
                'pfi'           => $datas[3],
                'datepfi'       => $datas[4],
                'amount'        => $datas[5],
                'type'          => $datas[6],
                'datestart'     => $datas[7],
                'dateend'       => $datas[8],
                'datesign'      => $datas[9],
                'amount'        => $datas[10],
                'organizations' => [],
                'persons' => []
            ];
            var_dump($datas);

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
        var_dump($out);
    }

    private function extractSeparatedOrNull( $key, $data, &$destination ){
        if( !$data ){
            return null;
        }
        $destination[$key] = explode('$', $data);
    }
}