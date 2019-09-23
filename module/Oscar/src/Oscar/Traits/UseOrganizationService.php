<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Oscar\Service\OrganizationService;

interface UseOrganizationService
{
    /**
     * @param OrganizationService $s
     */
    public function setOrganizationService( OrganizationService $s ) :void;

    /**
     * @return OrganizationService
     */
    public function getOrganizationService() :OrganizationService ;
}