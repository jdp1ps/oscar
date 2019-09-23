<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\OrganizationService;

trait UseOrganizationServiceTrait
{
    /**
     * @var OrganizationService
     */
    private $organizationService;

    /**
     * @param OrganizationService $s
     */
    public function setOrganizationService( OrganizationService $organizationService ) :void
    {
        $this->organizationService = $organizationService;
    }

    /**
     * @return OrganizationService
     */
    public function getOrganizationService() :OrganizationService {
        return $this->organizationService;
    }
}