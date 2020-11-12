<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/10/15 10:30
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Oscar\Service\ActivityLogService;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class ActivityLogController
 * @package Oscar\Controller
 * @deprecated
 */
class ActivityLogController extends AbstractOscarController
{
    /**
     * Activité dans l'application (ADMIN)
     */
    public function indexAction()
    {
        $level = $this->params()->fromQuery('level', 100);
        $debug = (integer) $this->params()->fromQuery('debug', 0);
        return [
            'level' => $level,
            'debug' => $debug,
            'entities' => new UnicaenDoctrinePaginator(
                $this->getActivityLogService()->listAdmin($level, $debug),
                $this->params()->fromQuery('page', 1)
            )
        ];
    }
}
