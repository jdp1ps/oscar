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
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use UnicaenSignature\Service\LoggerServiceAwareTrait;

class ActivityTypeService implements UseEntityManager, UseLoggerService
{
    use UseEntityManagerTrait, UseLoggerServiceTrait;

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
        if ($activityType == null) {
            return "";
        }

        $typeChain = $this->getActivityTypeChain($activityType);

        if (count($typeChain) <= 1) {
            return "";
        }
        else {
            return implode(" > ", array_slice($typeChain, 1));
        }
    }

    /**
     * @param $activityType
     * @return array
     */
    public function getActivityTypeChain($activityType): array
    {
        if ($activityType === null) {
            return [];
        }
        $chain = $this->getBaseQuery()
            ->where('t.lft <= :lft AND t.rgt >= :rgt')
            ->setParameters([
                                'lft' => $activityType->getLft(),
                                'rgt' => $activityType->getRgt(),
                            ])
            ->getQuery()
            ->getResult();

        return $chain;
    }

    public function getActivityTypeChainFormatted($activityType): string
    {
        $output = [];
        if ($activityType) {
            $chain = $this->getActivityTypeChain($activityType);
            /** @var ActivityType $node */
            foreach ($chain as $node) {
                if ($node->getLabel() == 'ROOT') {
                    continue;
                }
                $output[] = $node->getLabel();
            }
        }

        if (count($output) == 0) {
            return "Pas de type";
        }
        else {
            return implode(" > ", $output);
        }
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
                    }
                    else {
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
        }
        else {
            return $datas;
        }
    }


    protected function getTreeArray(&$index, $datas, &$children)
    {
        if (!array_key_exists($index, $datas)) {
            return;
        }

        /** @var ActivityType $activityType */
        $activityType = $datas[$index];

        $out = [
            'id'       => $activityType->getId(),
            'label'    => $activityType->getLabel(),
            'children' => []
        ];

        $index++;

        if ($activityType->getLft() + 1 < $activityType->getRgt()) {
            for (
            ; $datas[$index] && $datas[$index]->getLft() > $activityType->getLft() && $datas[$index]->getRgt(
            ) - 1 <= $activityType->getRgt();
            ) {
                $this->getTreeArray($index, $datas, $out['children']);
            }
        }

        $children[] = $out;
    }

    /**
     * Retourne un tableau imbriqué des types pour l'utilisation au format JSON.
     * @param false $asArray
     * @return array
     */
    public function getActivityTypesTree($asArray = false)
    {
        $datas = $this->getBaseQuery()
            ->getQuery()
            ->getResult();

        $out = [];
        $start = 0;
        $this->getTreeArray($start, $datas, $out);

        return $out;
    }

    public function deleteNode(ActivityType $activityType, $deleteEntity = true): void
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
            'UPDATE activitytype SET rgt = rgt - :decalage WHERE rgt > :rgt',
            new ResultSetMapping()
        )->execute(['decalage' => $decalage, 'rgt' => $rgt, 'lft' => $lft]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft - :decalage WHERE lft >= :lft',
            new ResultSetMapping()
        )->execute(['decalage' => $decalage, 'rgt' => $rgt, 'lft' => $lft]);
    }

    /**
     * Retourne un tableau associatif contenant comme clef l'identifiant du
     * type d'activité et comme valeur le nombre d'activité dans ce type.
     *
     * @return array
     */
    public function distribution(): array
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
     * @throws \Exception
     */
    public function insertIn(ActivityType $activityType, $node = null): void
    {
        if ($node === null) {
            $node = $this->getRoot();
        }

        $lft = $node->getRgt();
        $rgt = $lft + 1;

        $rsm = new ResultSetMapping();

        // Mise à jour des bornes
        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET lft = lft+2 WHERE lft > :lft',
            new ResultSetMapping()
        )->execute(['lft' => $lft]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt+2 WHERE rgt >= :rgt',
            new ResultSetMapping()
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
            'UPDATE activitytype SET lft = lft + :insertSize WHERE lft > :lft',
            new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt + :insertSize WHERE rgt >= :lft',
            new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        // Mise à jour de la branche déplacée
        // le décalage concervera l'arborescence existante
        $decalage = $moved->getLft() - $newLeft;
        foreach ($brancheDeplacee as $n) {
            $this->getEntityManager()->createNativeQuery(
                'UPDATE activitytype SET lft = :lft, rgt = :rgt WHERE id = :id',
                new ResultSetMapping()
            )->execute([
                           'lft' => $n->getLft() - $decalage,
                           'rgt' => $n->getRgt() - $decalage,
                           'id'  => $n->getId()
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
            'UPDATE activitytype SET lft = lft + :insertSize WHERE lft >= :lft',
            new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt + :insertSize WHERE rgt >= :lft',
            new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        // Mise à jour de la branche déplacée
        // le décalage concervera l'arborescence existante
        $decalage = $moved->getLft() - $newLeft;
        foreach ($brancheDeplacee as $n) {
            $this->getEntityManager()->createNativeQuery(
                'UPDATE activitytype SET lft = :lft, rgt = :rgt WHERE id = :id',
                new ResultSetMapping()
            )->execute([
                           'lft' => $n->getLft() - $decalage,
                           'rgt' => $n->getRgt() - $decalage,
                           'id'  => $n->getId()
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
            'UPDATE activitytype SET lft = lft + :insertSize WHERE lft >= :lft',
            new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        $this->getEntityManager()->createNativeQuery(
            'UPDATE activitytype SET rgt = rgt + :insertSize WHERE rgt >= :lft',
            new ResultSetMapping()
        )->execute(['lft' => $newLeft, 'insertSize' => $size]);

        // Mise à jour de la branche déplacée
        // le décalage concervera l'arborescence existante
        $decalage = $moved->getLft() - $newLeft;
        foreach ($brancheDeplacee as $n) {
            $this->getEntityManager()->createNativeQuery(
                'UPDATE activitytype SET lft = :lft, rgt = :rgt WHERE id = :id',
                new ResultSetMapping()
            )->execute([
                           'lft' => $n->getLft() - $decalage,
                           'rgt' => $n->getRgt() - $decalage,
                           'id'  => $n->getId()
                       ]);
        }
    }

    /**
     * @return ActivityType
     * @throws OscarException
     */
    private function getRoot()
    {
        $nodes = $this->getBaseQuery()
            ->where('t.label = :label')
            ->setParameter('label', 'ROOT')
            ->getQuery()
            ->getResult();

        if (count($nodes) == 1) {
            return $nodes[0];
        }
        else {
            if (count($nodes) == 0) {
                try {
                    // ZONE 51 ----
                    $createRoot = new ActivityType();
                    $this->getEntityManager()->persist($createRoot);

                    // SANS EFFET
                    $createRoot->setLft(1);
                    $createRoot->setRgt(2);
                    $createRoot->setLabel('ROOT');
                    $createRoot->setDescription('');
                    $this->getEntityManager()->flush();

                    return $createRoot;
                } catch (\Exception $e) {
                    $this->getLoggerService()->critical("Impossible de créer le noeud ROOT : " . $e->getMessage());
                    throw new OscarException("Impossible d'initialiser l'arbre des types d'activité");
                }
            }
            else {
                $this->getLoggerService()->critical("Hiérarchie des types d'activité corrompue !");
                throw new \Exception(
                    "Incohérence de l'arbre, veuillez contacter l'administrateur pour déclencher un recalcule de la hérarchie : "
                    . $e->getMessage()
                );
            }
        }
    }

    public function verify(bool $cure = false): array
    {
        $output = [];
        $errors = [];
        $details = [];

        // On commence par récupérer le noeud root et voir si il est
        // à sa place (position 1)
        $root = $this->getRoot();

        $rootId = $root->getId();
        if ($root->getLft() != 1) {
            $errors[] = "Le noeud ROOT n'est pas à la racine !";
        }


        // Recherche des bornes
        $min = $this->getEntityManager()->getRepository(ActivityType::class)->createQueryBuilder('t')
            ->orderBy('t.lft', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();

        $max = $this->getEntityManager()->getRepository(ActivityType::class)->createQueryBuilder('t')
            ->orderBy("t.rgt", "DESC")
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        if ($min->getId() != $rootId) {
            $errors[] = "Le premier noeud n'est pas le noeud ROOT !";
        }

        $types = $this->getActivityTypes();
        if (count($types) <= 1) {
            die("Aucun type configuré");
        }

        $expectedOpen = [1];
        $expectedClause = [count($types) * 2];
        $index = 0;

        $openTracker = 0;
        $openGap = 0;

        $first = $types[0];
        $borneEnd = count($types) * 2;

        if ($first->getLft() != 1) {
            $errors[] = "Le noeud racine a une incohérence de borne ouvrante";
        }
        $expectedMaxRgt = (count($types)) * 2;
        if ($first->getRgt() != $expectedMaxRgt) {
            $first->setRgt($expectedMaxRgt);
            $errors[] = "Le noeud racine a une incohérence de borne fermante (" . $first->getRgt(). " attendu $expectedMaxRgt)";
        }

        $nextClause = [];

        $tree = [

        ];

        $lastOpen = [];
        $depth = 3;
        $gap = 0;
        $rootReRooted = false;

        // On met ROOT en premier
        if ($types[0] !== $root) {
            $errors[] = "Le noeud ROOT n'est pas en premier, ces bornes ont été recalculées à partir des autres bornes";
            $minPos = $min->getLft() - 1;
            $maxPos = $max->getRgt() + 1;
            $root->setLft($minPos);
            $root->setRgt($maxPos);

            $newTypes = [$root];
            foreach ($types as $t) {
                if ($t != $root) {
                    $newTypes[] = $t;
                }
            }
            $types = $newTypes;
        }

        foreach ($types as $type) {
            $openTracker++;
            $curentNode = $openTracker;

            $tree[$openTracker] = [
                "type"       => $type,
                "label"      => $type->getLabel(),
                "open"       => $openTracker,
                "info"       => "",
                "end"        => 0,
                "close"      => null,
                "depth"      => $depth,
                'patched'    => false,
                "openBefore" => json_encode($nextClause) . "/" . json_encode($lastOpen)
            ];
            // echo " - " . str_pad(substr($type, 0, 10), 10, ' ') . "\t\t";

            if( $type == $root && $type->getRgt() != $expectedMaxRgt ){
                $tree[$openTracker]['close'] = $expectedMaxRgt;
            }

            if ($openTracker != $type->getLft()) {
                $gap = $openTracker - $type->getLft();
                $tree[$curentNode]['info'] .= "[!D$gap]";
            }

            $trac = $type->trac();
            $open = $type->getLft() + $gap;
            $clause = $type->getRgt() + $gap;

            if ($open != $openTracker + $openGap) {
                $openGap = $open - $openTracker;
                $errors[] = "Le noeud $trac a une borne ouvrante décalée";
            }

            // echo "[$openTracker\t-";
            if( $clause - $open == 1 ){
            } elseif ($clause - $open > 1 ){
                $sumbornes = ($clause - $open - 1);
                if ($sumbornes % 2 != 0) {
                    $nbr = $sumbornes/2;
                    $errors[] = "Nombre de noeud incohérent dans $trac ($nbr)";
                    $tree[$curentNode]['info'] .= "[!NN]";
                }
            } else {
                $errors[] = "Borne inversée ou incohérente $trac";
            }

            if ($clause > $open + 1) {
                // echo "\n --- [N] ";
                $tree[$curentNode]['info'] .= '[N]';
                $lastOpen[] = $curentNode;
                $nextClause[] = $clause;
                $depth += 3;
            }
            else {
                $openTracker++;
                // echo "$openTracker] ";
                $tree[$curentNode]['info'] .= '[F]';
                $tree[$curentNode]['close'] .= $openTracker;
            }
            while ($nextClause && $nextClause[count($nextClause) - 1] <= $openTracker + 1) {
                $openTracker++;
                $lastOpenId = array_pop($lastOpen);
                $clauseValue = array_pop($nextClause);
                $tree[$lastOpenId]['close'] = $openTracker;
                $tree[$curentNode]['info'] .= '[X]';
                // echo " $openTracker>>";
                $depth -= 3;
            }
        }

        for($i=count($lastOpen)-1; $i >= 0; $i--) {
            $openTracker++;
            $closeId = $lastOpen[$i];
            $tree[$closeId]['close'] = $openTracker;
            $tree[$closeId]['info'] .= '[Cm]';
        }

        $err = false;
        $unique = [];
        foreach ($tree as $i => $t) {
            if ($i === 0) {
                continue;
            }

            $open = $t['open'];
            $close = $t['close'];

            if( array_key_exists($open, $unique) ){
                $errors[] = "La borne '$open' dans " . $t['type'] . ' utilisée dans ' . $unique[$open];
            } else {
                $unique[$open] = sprintf('%s [%s,%s]', $t['type']->getLabel(), $t['open'], $t["close"]);
            }
            if( array_key_exists($close, $unique) ){
                $errors[] = "La borne '$close'(c) dans " . $t['type'] . ' utilisée dans ' . $unique[$close];
            } else {
                $unique[$close] = sprintf('%s [%s,%s]', $t['type']->getLabel(), $t['open'], $t["close"]);
            }

            $ok = ($t['type']->getLft() == $open && $t['type']->getRgt() == $close);
            if (!$ok) {
                $errors[] = "Bornes décalées pour '" . $t['label'] . "' => " . sprintf('(%s,%s)', $open, $close);
                $err = true;
            }
        }

        if ($err || $errors) {
            $needfix = true;
            if($cure) {
                foreach ($tree as $i => $t) {
                    if ($i === 0) {
                        continue;
                    }
                    $patched = false;
                    $type = $t['type'];

                    if( !$t['close'] || !$t['open'] ){
                        $errors[] = "Impossible de recalculer une borne dans " . $type->trac();
                        continue;
//                        throw new OscarException("Problème lors du recalcule des bornes : " . print_r($t, true));
                    }

                    if( $type->getLft() != $t['open'] ){
                        $type->setLft($t['open']);
                        $patched = true;
                    }
                    if( $type->getRgt() != $t['close'] ){
                        $type->setRgt($t['close']);
                        $patched = true;
                    }
                    $t['patched'] = $patched;
                }
                try {
                    $this->getEntityManager()->flush();
                } catch (\Exception $e) {
                    die("Erreur fatale : " . $e->getMessage());
                }
            }
        }
        else {
            $needfix = false;
        }

        foreach ($tree as $i => $t) {
            if ($i === 0) {
                continue;
            }

            $ok = ($t['type']->getLft() == $t['open'] && $t['type']->getRgt() == $t['close']);

            $details[] = [
                'id'       => $t['type']->getId(),
                'status'   => $ok ? true : false,
                'label'    => str_pad("", $t['depth'], '.')
                    . str_pad(substr($t['label'], 0, 80 - $t['depth']), 80 - $t['depth'], '-'),
                'expected' => sprintf(" (%s,%s)", $t['open'], $t['close']),
                'infos'    => $t['info'],
                'opens'    => $t['openBefore']
            ];
        }

        return [
            'needfix' => $needfix,
            'errors'  => $errors,
            'details' => $details
        ];
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