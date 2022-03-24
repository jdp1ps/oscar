<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\PCRUService;

trait UsePCRUServiceTrait
{
    /**
     * @var PCRUService
     */
    private $pcruService;

    /**
     * @return PCRUService
     */
    public function getPCRUService(): PCRUService
    {
        return $this->pcruService;
    }

    /**
     * @param PCRUService $pcruService
     */
    public function setPCRUService(PCRUService $pcruService): void
    {
        $this->pcruService = $pcruService;
    }
}