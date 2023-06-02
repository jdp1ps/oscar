<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Oscar\Service\ActivityLogService;
use Oscar\Service\ContractDocumentService;

interface UseContractDocumentService
{
    /**
     * @param ContractDocumentService $em
     */
    public function setContractDocumentService( ContractDocumentService $em ) :void;

    /**
     * @return ContractDocumentService
     */
    public function getContractDocumentService() :ContractDocumentService ;
}