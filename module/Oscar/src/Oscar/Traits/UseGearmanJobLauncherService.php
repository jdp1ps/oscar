<?php

namespace Oscar\Traits;

use Oscar\Service\GearmanJobLauncherService;

interface UseGearmanJobLauncherService
{
    /**
     * @param GearmanJobLauncherService $gearmanJobLauncherService
     */
    public function setGearmanJobLauncherService(GearmanJobLauncherService $gearmanJobLauncherService): void;

    /**
     * @return GearmanJobLauncherService
     */
    public function getGearmanJobLauncherService(): GearmanJobLauncherService;
}