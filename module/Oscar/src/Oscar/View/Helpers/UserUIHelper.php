<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/12/15 08:33
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use Oscar\Entity\Activity;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Service\AccessResolverService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class UserUIHelper extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    function __invoke()
    {
        return $this;
    }

    public function profil()
    {

       $entity = $this->getAccessResolverService()->getCurrentPerson();
        ob_start();
        ?>
        <table class="table table-bordered small">
            <tbody>
            <tr>
                <th>displayname</th>
                <td><?= $entity->getDisplayName() ?></td>
            </tr>
            <tr>
                <th>email</th>
                <td><?= $entity->getEmail() ?></td>
            </tr>
            <tr>
                <th>ldapAffectation</th>
                <td><?= $entity->getLdapAffectation() ?></td>
            </tr>
            <tr>
                <th>phone</th>
                <td><?= $entity->getPhone() ?></td>
            </tr>

            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }


    public function projectButtonBar( Project $project )
    {
        ob_start(); ?>
        <?php if ($this->hasProjectAccess($project,'information/manage')): ?>
            <a href="<?= $this->getView()->url('project/edit',
                ['id' => $project->getId()]) ?>" class="item">
                <i class="icon-tag"></i>
                <span class="item-label">
                    Modifier les informations
                </span>
            </a>
        <?php endif; ?>

        <?php if ($this->hasProjectAccess($project, 'contract/manage')): ?>
        <a href="<?= $this->getView()->url('contract/new',
            ['projectid' => $project->getId()]) ?>" class="item">
            <i class="icon-cube"></i>
                <span class="item-label">
                    Nouvelle activité de recherche
                </span>
        </a>
        <?php endif; ?>

        <span class="heading">
            Participants
        </span>
        <?php if ($this->hasProjectAccess($project, 'person/manage')): ?>
            <a href="<?= $this->getView()->url('personproject/new',
                ['idenroller' => $project->getId()]) ?>" class="item">
                <i class="icon-user"></i>
                <span class="item-label">
                    Ajouter une personne au projet
                </span>
            </a>
        <?php endif;
        if ($this->hasProjectAccess($project, 'organization/manage')): ?>
            <a href="<?= $this->getView()->url('organizationproject/new',
                ['idenroller' => $project->getId()]) ?>" class="item">
                <i class="icon-chart-bar-outline"></i>
                <span class="item-label">
                    Ajouter un partenaires au projet
                </span>
            </a>
        <?php endif;?>
        <div class="divider"></div>
        <?php return ob_get_clean();
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return AccessResolverService
     */
    protected function getAccessResolverService()
    {
        return $this->getServiceLocator()->getServiceLocator()->get('AccessResolverService');
    }

    protected function getProjectAccess( Project $project )
    {
        return $this->getAccessResolverService()->getProjectAccess($project);
    }

    public function hasProjectAccess( Project $project, $access )
    {
        return in_array($access, $this->getProjectAccess($project));
    }

    public function hasAccess( $context, $action = 'all' )
    {
        return $this->getAccessResolverService()->hasContextAccess( $context, $action);
    }
}