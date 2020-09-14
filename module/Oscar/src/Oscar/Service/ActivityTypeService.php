<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityType;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class ActivityTypeService implements UseEntityManager
{
    use UseEntityManagerTrait;

    /**
     * @param $id
     * @return ActivityType
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActivityType($id)
    {
        return $this->getBaseQuery()
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param array $ids
     * @return ActivityType[]
     */
    public function getActivityTypesById(array $ids)
    {
        return $this->getBaseQuery()
            ->where('t.id IN(:id)')
            ->setParameter('id', $ids)
            ->getQuery()
            ->getResult();
    }

    public function getTypeIdsInside($id)
    {
        $ids = [];
        try {
            $t = $this->getActivityType($id);
            $qb = $this->getBaseQuery()
                ->where('t.lft >= :lft AND t.rgt <= :rgt')
                ->setParameters([
                    'lft' => $t->getLft(),
                    'rgt' => $t->getRgt(),
                ]);
            foreach ($qb->getQuery()->getResult() as $type) {
                $ids[] = $type->getId();
            }

        } catch (\Exception $e) {

        }
        return $ids;
    }

    /**
     * Retourne la chemin Complet du type d'activité
     *
     * @param Activity $activityType
     * @return string
     */
    public function getActivityFullText($activityType)
    {
        if( $activityType == null ){
            return "";
        }

        $typeChain = $this->getActivityTypeChain($activityType);

        if (count($typeChain) <= 1) {
            return "";
        } else {
            return implode(" > ", array_slice($typeChain, 1));
        }
    }

    /**
     * @param $id
     * @return ActivityType
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActivityTypeChain($activity)
    {
        if ($activity === null) {
            return [];
        }
        $chain = $this->getBaseQuery()
            ->where('t.lft <= :lft AND t.rgt >= :rgt')
            ->setParameters([
                'lft' => $activity->getLft(),
                'rgt' => $activity->getRgt(),
            ])
            ->getQuery()
            ->getResult();

        return $chain;
    }

    /**
     * @return ActivityType[]
     */
    public function getActivityTypes($asArray = false)
    {
        $datas = $this->getBaseQuery()
            ->getQuery()
            ->getResult();

        // Cette variante est une solution (provisoire) pour afficher le
        // select de la valeur sous la forme d'un arbre.
        if ($asArray == true) {
            $array = [];
            $open = [];

            /** @var ActivityType $activityType */
            foreach ($datas as $activityType) {
                if ($activityType->getLabel() === "ROOT") {
                    continue;
                }
                $array[$activityType->getId()] = $activityType->getLabel();

                $close = count($open);
                $prefix = '';
                while ($close > 0) {
                    if ($open[count($open) - 1] < $activityType->getLft()) {
                        array_pop($open);
                    } else {
                        $prefix .= " - - ";
                    }
                    $close--;
                }

                if ($activityType->getLft() + 1 != $activityType->getRgt()) {
                    $open[] = $activityType->getRgt();
                }
                $array[$activityType->getId()] = $prefix . strval($activityType);
            }

            return $array;
        } else {
            return $datas;
        }
    }

    public function deleteNode(ActivityType $activityType, $deleteEntity = true)
    {
        $lft = $activityType->getLft();
        $rgt = $activityType->getRgt();
        $decalage = $rgt - $lft + 1;

        if ($deleteEntity === true) {
            // Suppression
            $this->getEntityManager()->createNativeQuery(
                'DELETE FROM activitytype WHERE lft >= :lft AND rgt <= :rgt',
                new ResultSetMapping()
            )->execute(['lft' => $lft, 'rgt' => $rgt]);
        }

        // Mise à jour des bornes
        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt - :decalage WHERE rgt > :rgt', new ResultSetMapping()
        )->execute(['decalage' => $decalage, 'rgt' => $rgt, 'lft' => $lft]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft - :decalage WHERE lft >= :lft', new ResultSetMapping()
        )->execute(['decalage' => $decalage, 'rgt' => $rgt, 'lft' => $lft]);
    }

    public function getActivityTypeByCentaureId($centaureId)
    {
        try {
            return $this->getEntityManager()->getRepository(ActivityType::class)->createQueryBuilder('t')
                ->where("t.centaureId = :single")
                ->orWhere("t.centaureId LIKE :in ")
                ->orWhere("t.centaureId LIKE :start")
                ->orWhere("t.centaureId LIKE :end")
                ->setParameters([
                    'in' => '%|' . $centaureId . '|%',
                    'start' => $centaureId . '|%',
                    'end' => '%|' . $centaureId,
                    'single' => $centaureId,
                ])
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retourne un tableau associatif contenant comme clef l'identifiant du
     * type d'activité et comme valeur le nombre d'activité dans ce type.
     *
     * @return array
     */
    public function distribution()
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(a.id) as nbr_activities', 't.id AS type_id')
            ->from(ActivityType::class, 't')
            ->leftJoin(Activity::class, 'a', Query\Expr\Join::WITH, 'a.activityType = t')
            ->groupBy('t.id');

        $distribution = [];
        foreach ($query->getQuery()->getArrayResult() as $key => $dist) {
            $distribution[$dist['type_id']] = $dist['nbr_activities'];
        }

        return $distribution;
    }


    /**
     * Inserte en tant qu'enfant direct (en dernier)
     * @param null $node
     */
    public function insertIn(ActivityType $activityType, $node = null)
    {
        if ($node === null) {
            $node = $this->getRoot();
        }

        $lft = $node->getRgt();
        $rgt = $lft + 1;

        $rsm = new ResultSetMapping();

        // Mise à jour des bornes
        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft+2 WHERE lft > :lft', new ResultSetMapping()
        )->execute(['lft' => $lft]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt+2 WHERE rgt >= :rgt', new ResultSetMapping()
        )->execute(['rgt' => $lft]);

        $activityType->setLft($lft)->setRgt($rgt);

        $this->getEntityManager()->flush([$node, $activityType]);
    }


    /**
     * Déplacement d'un noeud dans un autre, le noeud déplacé est disposé à la
     * suite des noeuds déjà présent.
     *
     * @param ActivityType $moved
     * @param ActivityType $dest
     */
    public function moveIn(ActivityType $moved, ActivityType $dest)
    {
        $brancheDeplacee = $this->getBaseQuery()
            ->where('t.lft >= :lft AND t.rgt <= :rgt ')
            ->setParameters(['lft' => $moved->getLft(), 'rgt' => $moved->getRgt()])
            ->getQuery()
            ->getResult();

        if (count($brancheDeplacee) <= 0) {
            die("Errur");
        }

        // On "simule" la suppression du noeud pour recalculer les bornes
        $this->deleteNode($moved, false);

        // Taille du noeud à déplacer
        $size = count($brancheDeplacee) * 2;

        // On actualise la destination
        $this->getEntityManager()->refresh($dest);

        // Nouvelle borne gauche
        $newLeft = $dest->getRgt();

        // Mise à jour des bornes dans l'arbre
        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft + :insertSize WHERE lft > :lft', new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt + :insertSize WHERE rgt >= :lft', new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        // Mise à jour de la branche déplacée
        // le décalage concervera l'arborescence existante
        $decalage = $moved->getLft() - $newLeft;
        foreach ($brancheDeplacee as $n) {
            $this->getEntityManager()->createNativeQuery(
                'UPDATE activitytype SET lft = :lft, rgt = :rgt WHERE id = :id', new ResultSetMapping()
            )->execute([
                'lft' => $n->getLft() - $decalage,
                'rgt' => $n->getRgt() - $decalage,
                'id' => $n->getId()
            ]);
        }
    }

    public function moveAfter($moved, $dest)
    {
        $brancheDeplacee = $this->getBaseQuery()
            ->where('t.lft >= :lft AND t.rgt <= :rgt ')
            ->setParameters(['lft' => $moved->getLft(), 'rgt' => $moved->getRgt()])
            ->getQuery()
            ->getResult();

        if (count($brancheDeplacee) <= 0) {
            die("Errur");
        }

        // On "simule" la suppression du noeud pour recalculer les bornes
        $this->deleteNode($moved, false);

        // Taille du noeud à déplacer
        $size = count($brancheDeplacee) * 2;

        // On actualise la destination
        $this->getEntityManager()->refresh($dest);

        // Nouvelle borne gauche
        $newLeft = $dest->getRgt() + 1;

        // Mise à jour des bornes dans l'arbre
        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft + :insertSize WHERE lft >= :lft', new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt + :insertSize WHERE rgt >= :lft', new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        // Mise à jour de la branche déplacée
        // le décalage concervera l'arborescence existante
        $decalage = $moved->getLft() - $newLeft;
        foreach ($brancheDeplacee as $n) {
            $this->getEntityManager()->createNativeQuery(
                'UPDATE activitytype SET lft = :lft, rgt = :rgt WHERE id = :id', new ResultSetMapping()
            )->execute([
                'lft' => $n->getLft() - $decalage,
                'rgt' => $n->getRgt() - $decalage,
                'id' => $n->getId()
            ]);
        }
    }

    public function moveBefore($moved, $dest)
    {
        $brancheDeplacee = $this->getBaseQuery()
            ->where('t.lft >= :lft AND t.rgt <= :rgt ')
            ->setParameters(['lft' => $moved->getLft(), 'rgt' => $moved->getRgt()])
            ->getQuery()
            ->getResult();

        if (count($brancheDeplacee) <= 0) {
            die("Errur");
        }

        // On "simule" la suppression du noeud pour recalculer les bornes
        $this->deleteNode($moved, false);

        // Taille du noeud à déplacer
        $size = count($brancheDeplacee) * 2;

        // On actualise la destination
        $this->getEntityManager()->refresh($dest);

        // Nouvelle borne gauche
        $newLeft = $dest->getLft();

        // Mise à jour des bornes dans l'arbre
        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft + :insertSize WHERE lft >= :lft', new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt + :insertSize WHERE rgt >= :lft', new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        // Mise à jour de la branche déplacée
        // le décalage concervera l'arborescence existante
        $decalage = $moved->getLft() - $newLeft;
        foreach ($brancheDeplacee as $n) {
            $this->getEntityManager()->createNativeQuery(
                'UPDATE activitytype SET lft = :lft, rgt = :rgt WHERE id = :id', new ResultSetMapping()
            )->execute([
                'lft' => $n->getLft() - $decalage,
                'rgt' => $n->getRgt() - $decalage,
                'id' => $n->getId()
            ]);
        }
    }

    /**
     * @return ActivityType
     */
    private function getRoot()
    {
        $nodes = $this->getBaseQuery()
            ->where('t.lft = 1')
            ->getQuery()
            ->getResult();

        if (count($nodes) == 1) {
            return $nodes[0];
        } else if (count($nodes) == 0) {
            $root = new ActivityType();
            $root->setLft(1)
                ->setRgt(2)
                ->setNature('ROOT')
                ->setDescription('Automatic root, may never displayed')
                ->setLabel('ROOT');
            $this->getEntityManager()->persist($root);
            $this->getEntityManager()->flush($root);
            return $root;
        } else {
            throw new \Exception("Incohérence de l'arbre, veuillez contacter l'administrateur pour déclencher un recalcule de la hérarchie");
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBaseQuery($nonActive = false)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(ActivityType::class, 't')
            ->orderBy('t.lft', 'ASC');
    }
}