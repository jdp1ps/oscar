<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-01-09 14:46
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Oscar\Entity\Activity;
use Oscar\Entity\SpentTypeGroup;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\SpentService;
use Oscar\Traits\UseActivityService;
use Oscar\Traits\UseActivityServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;

class DepenseController extends AbstractOscarController implements UseServiceContainer
{
    use UseServiceContainerTrait;

    /**
     * @return SpentService
     */
    public function getSpentService(){
        return $this->getServiceContainer()->get(SpentService::class);
    }

    /**
     * @return ProjectGrantService
     */
    public function getProjectGrantService(){
        return $this->getServiceContainer()->get(ProjectGrantService::class);
    }



    /**
     * @return OscarUserContext
     */
    public function getOscarUserContextService(): OscarUserContext
    {
        return $this->getServiceContainer()->get(OscarUserContext::class);
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContext(){
        return $this->getOscarUserContextService();
    }

    /**
     * @return OscarConfigurationService
     */
    public function getOscarConfigurationService(): OscarConfigurationService
    {
        return $this->getServiceContainer()->get(OscarConfigurationService::class);
    }

    /**
     * @return Logger
     */
    public function getLogger(){
        return $this->getServiceContainer()->get('Logger');
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager() :EntityManager {
        return $this->getServiceContainer()->get(EntityManager::class);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function manageSpendTypeGroupAction()
    {

        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE);
        $format = $this->params()->fromQuery('format', 'html');

        $method = $this->getHttpXMethod();

        $this->getLogger()->debug("SPENTTYPE - $method");

        switch ($method) {
            case Request::METHOD_PUT:
                try {
                    $this->getLogger()->debug(print_r($_POST, true));
                    $result = $this->getSpentService()->createSpentTypeGroup($this->params()->fromPost());
                    return $this->getResponseOk();
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }

            case Request::METHOD_POST:
                try {
                    $this->getLogger()->debug(print_r($_POST, true));
                    if( $this->params()->fromPost("moved") ){
                        $result = $this->getSpentService()->moved($this->params()->fromPost('moved'), $this->params()->fromPost('to'));
                    }
                    elseif ($this->params()->fromPost("admin") == 'reset') {
                        $this->getSpentService()->loadPCG();
                        return $this->getResponseOk();
                    }
                    elseif ($this->params()->fromPost("admin") == 'sort') {
                        $this->getSpentService()->orderSpentsByCode();
                        return $this->getResponseOk();
                    }
                    elseif ($this->params()->fromPost("action") == 'blind') {
                        $spent = $this->getSpentService()->getSpentTypeById($this->params()->fromPost('id'));
                        $spent->setBlind(!$spent->getBlind());
                        $this->getEntityManager()->flush($spent);
                        return $this->getResponseOk();
                    }

                    elseif ($this->params()->fromPost("action") == 'annexe') {
                        /** @var SpentTypeGroup $spent */
                        $spent = $this->getSpentService()->getSpentTypeById($this->params()->fromPost('id'));
                        $annexe = $spent->getAnnexe();
                        $newAnnexe = $this->params()->fromPost('annexe');

                        // @todo Contrôler la validitée de l'annexe
                        $spent->setAnnexe( $annexe == $newAnnexe ? '' : $newAnnexe);
                        $this->getEntityManager()->flush($spent);
                        return $this->getResponseOk();
                    }

                    else {
                        $result = $this->getSpentService()->updateSpentTypeGroup($this->params()->fromPost());
                    }
                    return $this->jsonOutput([
                        'spenttypegroups' => $this->getSpentService()->getAllArray()
                    ]);
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }

            case Request::METHOD_DELETE:
                try {
                    $this->getLogger()->debug(print_r($_GET, true));
                    $deleteId = $this->params()->fromQuery('id');
                    $result = $this->getSpentService()->deleteNode(
                        $this->getSpentService()->getSpentGroupNodeData($deleteId));
                    return $this->jsonOutput([
                        'spenttypegroups' => $this->getSpentService()->getAllArray()
                    ]);
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }


            case Request::METHOD_GET:
                break;

            default:
                return $this->getResponseBadRequest();
        }


        if( $format == 'json' || $this->isAjax() ){
            return $this->jsonOutput([
                'spenttypegroups' => $this->getSpentService()->getAllArray(),
                'masses' => $this->getOscarConfigurationService()->getConfiguration('spenttypeannexes')
            ]);
        } else {
            return [];
        }
    }

    public function activityApiAction(){
        try {
            $idactivity = $this->params()->fromRoute('id');
            $activity = $this->getProjectGrantService()->getActivityById($idactivity);
        } catch (\Exception $e){
            return $this->getResponseInternalError("Impossible de charger l'activité : " . $e->getMessage());
        }
        $this->getOscarUserContextService()->check(Privileges::DEPENSE_SHOW, $activity);

        try {
            if( !$activity->getCodeEOTP() ){
                throw new OscarException(sprintf(_("Cette activité n'a pas de PFI")));
            }
            $spents = $this->getSpentService()->getGroupedSpentsDatas($activity->getCodeEOTP());
            $datas = $this->baseJsonResponse();
            $datas['spents'] = $spents;
            return $this->jsonOutput($datas);
        } catch (\Exception $e){
            return $this->getResponseInternalError("Impossible de charger l'activité : " . $e->getMessage());
        }
    }
}