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
use Oscar\Formatter\Spent\SpentActivityDetailsExcelFormater;
use Oscar\Formatter\Spent\SpentActivityExcelFormater;
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

        try {
            $masses = $this->getOscarConfigurationService()->getConfiguration('spenttypeannexes');
        } catch (\Exception $e) {
            throw new OscarException("La configuration des masses budgetaire (spenttypeannexes) est necessaire");
        }

        $method = $this->getHttpXMethod();

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
                        try {
                            $id = $this->params()->fromPost('id');

                            /** @var SpentTypeGroup $spent */
                            $spent = $this->getSpentService()->getSpentTypeById($id);
                            if( !$spent ){
                                throw new OscarException("Impossible de charger le compte $id");
                            }
                            $annexe = $spent->getAnnexe();
                            $newAnnexe = $this->params()->fromPost('annexe');

                            $savedAnnexe = $annexe == $newAnnexe ? '' : $newAnnexe;

                            $this->getLogger()->info("Modification du compte $id vers $newAnnexe");
                            // @todo Contrôler la validitée de l'annexe
                            if( $savedAnnexe == '0' ){
                                $spent->setBlind(true);
                            } else {
                                $spent->setBlind(false);
                            }
                            $spent->setAnnexe($savedAnnexe);
                            $this->getEntityManager()->flush($spent);
                            return $this->getResponseOk();
                        } catch (\Exception $e){
                            return $this->getResponseInternalError($e->getMessage());
                        }
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
                'masses' => $masses,
            ]);
        } else {
            return [];
        }
    }

    public function compteAffectationAction(){
        $method = $this->getHttpXMethod();

        if( $method == 'POST' ){

            // Vérifiaction des droits d'accès
            $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE);

            // Récupération des affectations
            $postedAffectations = $this->params()->fromPost('affectation');

            if( !$postedAffectations ){
                return $this->getResponseBadRequest("Erreur de transmission : " . print_r($_POST, true));
            }

            try {
                $this->getSpentService()->updateAffectation($postedAffectations);

            } catch (\Exception $e) {
                return $this->getResponseInternalError($e->getMessage());
            }
            return $this->getResponseOk("Affectation des comptes terminée");
        }

        return $this->getResponseBadRequest();
    }

    /**
     * Données des dépenses pour une activité.
     *
     * @return \Zend\Http\Response|JsonModel
     */
    public function activityApiAction(){

        try {
            $idactivity = $this->params()->fromRoute('id');
            $activity = $this->getProjectGrantService()->getActivityById($idactivity);
        } catch (\Exception $e){
            return $this->getResponseInternalError("Impossible de charger l'activité : " . $e->getMessage());
        }

        $this->getOscarUserContextService()->check(Privileges::DEPENSE_DETAILS, $activity);

        try {
            if( !$activity->getCodeEOTP() ){
                throw new OscarException(sprintf(_("Cette activité n'a pas de Numéro financier")));
            }
            //$spents = $this->getSpentService()->getGroupedSpentsDatas($activity->getCodeEOTP());
            $spents = $this->getSpentService()->getSpentsDatas($activity->getCodeEOTP());

            $format = $this->params()->fromQuery('format', 'json');
            switch($format){
                case 'json' :
                    $datas = $this->baseJsonResponse();
                    $datas['spents'] = $spents;
                    return $this->jsonOutput($datas);
                    break;

                case 'excel':
                    $this->getOscarUserContextService()->check(Privileges::DEPENSE_DOWNLOAD, $activity);
                    $mode = $this->params()->fromQuery('mode', 'normal');
                    if( $mode == 'normal' ){
                        $formatter = new SpentActivityExcelFormater($spents, $activity);
                        $content = $formatter->format(['download' => true]);
                        die();
                    }
                    elseif( $mode == 'details' ){
                        $formatter = new SpentActivityDetailsExcelFormater($spents, $activity);
                        $content = $formatter->format(['download' => true]);
                        die("Pas encore disponible");
                    }
                    else {
                        throw new OscarException("Impossible de télécharger les dépenses, le mode $mode n'est pas disponible.");
                    }

                    break;

                default:
                    throw new OscarException("Format demandé non-pris en charge");
                    break;
            }


        } catch (\Exception $e){
            return $this->getResponseInternalError("Impossible de charger l'activité : " . $e->getMessage());
        }
    }
}