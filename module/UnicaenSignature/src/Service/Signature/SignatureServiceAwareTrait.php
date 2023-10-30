<?php

namespace UnicaenSignature\Service\Signature;

trait SignatureServiceAwareTrait {

    private SignatureService $signatureService;

    /**
     * @return SignatureService
     */
    public function getSignatureService(): SignatureService
    {
        return $this->signatureService;
    }

    /**
     * @param SignatureService $signatureService
     * @return void
     */
    public function setSignatureService(SignatureService $signatureService): void
    {
        $this->signatureService = $signatureService;
    }

}