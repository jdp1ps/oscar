<?php

namespace Oscar\Controller;

use Oscar\Entity\Person;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Formatter\PersonToJsonConnectorFormatter;
use Oscar\OscarVersion;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class ApiController extends AbstractOscarController implements UseOscarConfigurationService, UsePersonService, UseLoggerService
{
    use UseOscarConfigurationServiceTrait, UsePersonServiceTrait, UseLoggerServiceTrait;

    public function personsAction(){
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Oscar');
            header('HTTP/1.0 401 Unauthorized');
            echo "Accès à l'API Oscar limitée";
            exit;
        } else {

            // Vérification accès
            try {
                $apiaccess = $this->getOscarConfigurationService()->getConfiguration('apiaccess');

                if( is_array($apiaccess) ){
                    $user = $_SERVER['PHP_AUTH_USER'];
                    $pass = md5($_SERVER['PHP_AUTH_PW']);

                    if( !array_key_exists($user, $apiaccess) ){
                        return $this->getResponseUnauthorized("Accès interdit l'API Oscar");
                    }

                    if( $apiaccess[$user]['pass'] != $pass ){
                        return $this->getResponseUnauthorized("Accès interdit l'API Oscar");
                    }

                    if( !in_array('persons', $apiaccess[$user]['api']) ){
                        return $this->getResponseUnauthorized("Accès interdit l'API Oscar");
                    }

                    $persons = [];
                    $personToJsonFormatter = new PersonToJsonConnectorFormatter();

                    /** @var Person $p */
                    foreach( $this->getPersonService()->getPersons() as $p ){
                        $persons[] = $personToJsonFormatter->format($p);
                    }

                    $datas = [
                      "version"         => OscarVersion::getBuild(),
                      "datecreated"     => date('c'),
                      'persons'         => $persons
                    ];


                    return $this->jsonOutput($datas);



                } else {
                    throw new OscarException("L'accès à l'API Oscar est mal configuré");
                }
            } catch (OscarException $e) {
                return $this->getResponseInternalError(_("Oscar n'est pas configurer pour authoriser les accès à son API"));
            } catch (\Exception $e) {
                return $this->getResponseInternalError(sprintf(_("Erreur inconnue : %s"),$e->getMessage()));
            }
        }
        die("TODO");
    }

    public function helpAction(){
        die("Consultez l'aide technique pour obtenir");
    }


    public function apiAction()
    {
        $action = $this->params()->fromQuery('a');
        $result = [];

        if (!$action) {
            $this->response->setStatusCode(500);
            $result['error'] = 'Mauvais utilisation';
        } else {
            try {
                $projectId = $this->params()->fromQuery('projectId');
                switch ($action) {
                    case 'partners':

                        if ($this->getRequest()->isPost()) {
                            $dateStart = $this->params()->fromPost('dateStart');
                            $dateEnd = $this->params()->fromPost('dateEnd');
                            $role = $this->params()->fromPost('role');
                            $enrolId = $this->params()->fromPost('enrolid');

                            $this->addPartner($projectId, $enrolId, $role, $dateStart, $dateEnd);
                        } elseif ($this->getRequest()->isDelete()) {
                            var_dump($this->getRequest()->getParameters());
                            $projectPartner = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectPartner')->find($this->param('idRole'));
                            $this->getEntityManager()->remove($projectPartner);
//                            throw new \Exception("SUPPRESSION");
                        }

                        $result = $this->getProject($projectId)['partners'];

                        break;

                    case 'members':
                        $result = $this->getProject($projectId)['members'];
                        break;

                    case 'identification':
                        $result = $this->getProject($projectId);
                        break;

                    case 'delete-partner':
                        $result = $this->deleteRole();
                        break;

                    default:
                        throw new \Exception('Unknow API action');
                        break;
                }
            } catch (\Exception $ex) {
                $this->response->setStatusCode(500);
                $result['error'] = $ex->getMessage();
            }
        }

        return new JsonModel($result);
    }

    protected function addPartner($projectId, $enrolId, $role, $dateStart, $dateEnd)
    {
        $project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($projectId);
        $organisation = $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($enrolId);
        $partner = new ProjectPartner();
        $partner->setProject($project)
            ->setOrganization($organisation)
            ->setDateStart(new \DateTime($dateStart))
            ->setDateEnd(new \DateTime($dateEnd))
            ->setRole($role);
        $this->getEntityManager()->persist($partner);
        $this->getEntityManager()->flush();
    }

    protected function deleteRole()
    {
        $projectId = $this->params()->fromQuery('projectId');

        return ['response' => 'TOTO '.$projectId];
    }

    protected function getProject($projectId)
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')->getSingle($projectId)->toArray();
    }

    protected function getProjectMembers($projectId)
    {
        return $this->getProject($projectId)['members'];
    }

    protected function getProjectPartners($projectId)
    {
        return $this->getProject($projectId)['partners'];
    }

    /**
     * @return ViewModel
     */
    public function searchStaffAction()
    {
        $sl = $this->getServiceLocator();
        $search = $this->getRequest()->getQuery()->get('q');

        if (strlen($search) >= 4) {
            $t = $sl->get('PersonnelService');
            /* @var \Application\Service\PersonnelService */
            $result = $t->searchStaff($search);
            return new JsonModel($result);
        } else {
            $response = new Response();
            $response->setStatusCode(400);
            return $response;
        }
    }
}
