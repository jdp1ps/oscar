<?php

namespace Oscar\Traits;

use Oscar\Service\GearmanJobLauncherService;

trait UseGearmanJobLauncherServiceTrait
{
    /**
     * @var GearmanJobLauncherService $gearmanJobLauncherService
     */
    private $gearmanJobLauncherService;

    /**
     * @param Logger $s
     */
    public function setGearmanJobLauncherService(GearmanJobLauncherService $gearmanJobLauncherService): void
    {
        $this->gearmanJobLauncherService = $gearmanJobLauncherService;
    }

    /**
     * @return GearmanJobLauncherService
     */
    public function getGearmanJobLauncherService(): GearmanJobLauncherService
    {
        return $this->gearmanJobLauncherService;
    }
}