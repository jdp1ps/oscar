<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 10:46
 */

namespace Oscar\Formatter;


use Oscar\Connector\ConnectorRepport;

class ConnectorRepportToPlainText
{
    public function format(ConnectorRepport $connectorRepport){

        foreach ($connectorRepport->getRepportStates() as $state=>$datas) {
            echo "# " . strtoupper($state) ."\n";
            foreach ($datas as $data ){
                echo date('Y-m-d H:i:s', $data['time'])
                    ."\t"
                    . $data['message']
                    . "\n";
            }
        }
    }
}