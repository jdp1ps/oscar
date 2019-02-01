<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/11/18
 * Time: 09:53
 */

namespace Oscar\Service;


use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Utils\ValidationInput;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UserParametersService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface, ConfigurationAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait, ConfigurationAwareTrait;

    /**
     * @return ActivityLogService
     */
    protected function getActivityLogService(){
        return $this->getServiceLocator()->get('ActivityLogService');
    }

    /**
     * Modification du mode de déclaration de l'utilisateur.
     *
     * @param $userInput
     * @throws OscarException
     */
    public function performChangeDeclarationMode( $userInput )
    {
        if( !$this->getConfiguration('oscar.declarationsHoursOverwriteByAuth', false) ){
            throw new OscarException(_('Cette option ne peut pas être modifiée'));
        }

        try {
            $settings = $this->getCurrentAuth()->getSettings() ?: [];
            $declarationsHours = $userInput == 'on' ? true : false;
            $mode = $declarationsHours == true ? "HEURE" : "POURCENTAGE";
            $settings['declarationsHours'] = $declarationsHours;
            $this->getCurrentAuth()->setSettings($settings);
            $this->getEntityManager()->flush($this->getCurrentAuth());
            $this->getActivityLogService()->addUserInfo(sprintf(_('%s a modifié le mode de déclaration en %s'), $this->getCurrentAuth()->getDisplayName(), $mode));
            return true;
        } catch ( \Exception $e ){
            throw new OscarException(sprintf('%s : %s', _('Impossible de modifier le mode de déclaration'), $e->getMessage()));
        }
    }

    public function performChangeFrequency( $userInput )
    {
        if( !$this->getConfiguration('oscar.declarationsHoursOverwriteByAuth', false) ){
            throw new OscarException(_('Cette option ne peut pas être modifiée'));
        }

        try {
            $settings = $this->getCurrentAuth()->getSettings() ?: [];
            $frequency = ValidationInput::frequency($userInput);
            $this->getServiceLocator()->get('Logger')->debug($userInput);
            $this->getServiceLocator()->get('Logger')->debug(print_r($frequency, true));
            $settings['frequency'] = $frequency;
            $this->getCurrentAuth()->setSettings($settings);
            $this->getEntityManager()->flush($this->getCurrentAuth());
            $this->getActivityLogService()->addUserInfo(sprintf(_("%s a modifié la fréqence de ces notifications '%s'."),
                $this->getCurrentAuth()->getDisplayName(), implode(",", $frequency))
            );
            return true;
        } catch ( \Exception $e ){
            throw new OscarException(sprintf('%s : %s', _('Impossible de modifier la fréquence des notification'), $e->getMessage()));
        }
    }

    public function performChangeSchedule( $userInput, Person $person, $save = true ){

        try {
            $daysLength = json_decode($userInput, JSON_OBJECT_AS_ARRAY);
            $allowDays = ['1', '2', '3', '4', '5'];
            $settings = $person->getCustomSettingsObj();

            if( $save == true ){
                $key = "days";
            } else {
                $key = "days_request";
            }

            // Tester l'authorisation de déclarer le weekend
            $settings[$key] = [];
            foreach ($daysLength as $day => $duration) {
                if (in_array($day, $allowDays)) {
                    $value = floatval($duration);
                    if (is_float($value)) {
                        $settings[$key][$day] = $value;
                    }
                }
            }

            $this->getActivityLogService()->addUserInfo(sprintf('Modification de la répartition horaire de %s pour %s', (string)$person, print_r($settings, true)));
            $person->setCustomSettingsObj($settings);
            $this->getEntityManager()->flush($person);
            return true;
        } catch (\Exception $e) {
            throw new OscarException(_("Impossible de modifier la répartition horaire"));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// OBTENTION des DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function scheduleEditable(){
        return $this->getConfiguration('oscar.userSubmitSchedule');
    }
}