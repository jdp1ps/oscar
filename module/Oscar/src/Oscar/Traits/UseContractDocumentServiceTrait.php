<?php
namespace Oscar\Traits;

use Oscar\Service\ContractDocumentService;

trait UseContractDocumentServiceTrait
{
    /**
     * @var ContractDocumentService
     */
    private $contractDocumentServie;

    /**
     * @param ContractDocumentService $s
     */
    public function setContractDocumentService( ContractDocumentService $contractDocumentServie ) :void
    {
        $this->contractDocumentServie = $contractDocumentServie;
    }

    /**
     * @return ContractDocumentService
     */
    public function getContractDocumentService() :ContractDocumentService {
        return $this->contractDocumentServie;
    }
}