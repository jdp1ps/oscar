<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 31/10/19
 * Time: 14:00
 */

namespace Oscar\Service;

use Doctrine\ORM\NoResultException;
use Oscar\Entity\AdministrativeDocumentSection;
use Oscar\Entity\AdministrativeDocumentSectionRepository;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;

class AdministrativeDocumentService implements UseEntityManager
{
    use UseEntityManagerTrait;

    /**
     * @return AdministrativeDocumentSectionRepository
     */
    protected function getAdministrativeDocumentSectionRepository(){
        return $this->getEntityManager()->getRepository(AdministrativeDocumentSection::class);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function getSections( $asArray = true ){
        return $this->getAdministrativeDocumentSectionRepository()->getAll(true);
    }

    /**
     * @param $sectionId
     * @param bool $asArray
     * @param bool $throw
     * @return AdministrativeDocumentSection|null
     * @throws OscarException
     */
    public function getSectionById( $sectionId, $asArray = false, $throw = true ){
        try {
            $section = $this->getAdministrativeDocumentSectionRepository()->getOne($sectionId, $asArray);
            return $section;
        } catch ( NoResultException $e ) {
            if( $throw ){
                throw new OscarException(sprintf(_("Impossible de charger la section %s"), $sectionId));
            }
            return null;
        }
    }

    /**
     * @param $datas
     * @return AdministrativeDocumentSection|null
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createOrUpdateSection( $datas ){
        $sectionId = $datas['id'] ?? null;
        if( $sectionId ){
            $section = $this->getSectionById($sectionId);
        } else {
            $section = new AdministrativeDocumentSection();
            $this->getEntityManager()->persist($section);
        }
        $section->setLabel($datas['label']);
        $this->getEntityManager()->flush($section);
        return $section;
    }

    /**
     * @param $sectionId
     * @return bool
     * @throws OscarException
     */
    public function removeSection( $sectionId ){
        try {
            $section = $this->getSectionById($sectionId);
            $this->getEntityManager()->remove($section);
            $this->getEntityManager()->flush($section);
            return true;
        } catch (NoResultException $e) {
            throw new OscarException(sprintf(_("Impossible de charger la section '%s'.", $sectionId)));
        } catch ( \Exception $e ){
            throw new OscarException(sprintf(_("Impossible de supprimer la section '%s' : %s"), $section, $e->getMessage()));
        }
    }
}