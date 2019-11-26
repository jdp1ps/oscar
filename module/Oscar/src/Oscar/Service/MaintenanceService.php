<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 25/11/19
 * Time: 10:40
 */

namespace Oscar\Service;


use Oscar\Entity\CategoriePrivilege;
use Oscar\Entity\Privilege;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * Regroupe les différentes opérations de maintenance TECHNIQUE dans Oscar.
 *
 * Class MaintenanceService
 * @package Oscar\Service
 */
class MaintenanceService implements UseEntityManager
{
    use UseEntityManagerTrait;

    protected function updatePrivilegeWitDatas(Privilege $privilege, $stdObject, OutputStyle $outputStyle)
    {
        $flush = false;

        if ($privilege->getCategorie()->getId() != $stdObject->category_id) {
            return true;
        }

        $privilegeRoot = $privilege->getRoot() ? $privilege->getRoot()->getFullCode() : null;

        if (property_exists($stdObject, 'root') && $privilegeRoot != $stdObject->root ) {
            return true;
        }

        if ($privilege->getCode() != $stdObject->code) {
            $privilege->setCode($stdObject->code);
            return true;
        }

        if ($privilege->getSpot() != $stdObject->spot) {
            $privilege->setSpot($stdObject->spot);
            return true;
        }

        if ($privilege->getLibelle() != $stdObject->libelle) {
            $privilege->setLibelle($stdObject->libelle);
            return true;
        }

        return false;
    }

    public function privilegesCheckUpdate(OutputStyle $output){

        $cheminFichier = realpath(__DIR__ . '/../../../../../install/privileges.json');

        $output->title("Mise à jour des privilèges");
        $output->writeln(sprintf("Mise à jour depuis <bold>%s</bold>", $cheminFichier));

        // Accès au fichier
        if (!file_exists($cheminFichier)) {
            $output->error("ERREUR : Le fichier de configurationn est introuvable/inaccessible en lecture.");
            return;
        }

        // Récupération du fichier
        $contenuFichier = file_get_contents($cheminFichier);
        if (!$contenuFichier) {
            $output->error("Impossible de convertir le contenu du fichier.");
            return;
        }

        // Conversion
        $datas = json_decode($contenuFichier);
        if (!$datas) {
            $output->error("Impossible de traiter les données du fichier : ". json_last_error_msg());
            return;
        }

        $datasCreate = $datas;

        $operations = [
            'delete'    => [],
            'add'       => [],
            'update'    => [],
        ];

        $toRemove = [];
        $toAdd = [];
        $toUpdate = [];
        $verbose = false;

        // Récupération des privilèges existants
        $privileges = $this->getEntityManager()->getRepository(Privilege::class)->findAll();


        // Commencer par créer les activités manquantes
        /** @var Privilege $p */
        foreach ($privileges as $p) {

            $property = $p->getFullCode();
            $output->write(sprintf("> [%s] <bold>%s</bold> : ", $property, $p));
            $do = "";
            try {
                if (property_exists($datas, $property)) {
                    // Mise à jour
                    $updatable = $this->updatePrivilegeWitDatas($p, $datas->$property, $output);

                    if (false !== $updatable) {
                        $output->writeln("<comment>Mise à jour requise</comment>");
                        $toUpdate[] = [ 'privilege' => $p, 'data' => $datas->$property ];
                    } else {
                        $output->writeln("<green>OK</green>");
                    }
                    unset($datas->$property);
                    unset($datasCreate->$property);
                } else {
                    $toRemove[] = $p;
                    $output->writeln(sprintf("<comment>SUPPRESSION</comment>"));
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
                continue;
            }
        }

        $anythingToDo = false;

        $output->section("Résumé des opérations de maintenance : ");

        if (count($toUpdate)) {
            $output->writeln("Il y'a " . count($toUpdate) . " privilèges à mettre à jour");
            $anythingToDo = true;
        }

        if (count($toRemove)) {
            $output->writeln("Il y'a " . count($toRemove) . " privilèges à supprimer");
            $anythingToDo = true;
        }
        if (count(get_object_vars($datas))) {
            $output->writeln("Il y'a " . count(get_object_vars($datas)) . " privilèges à ajouter");
            $anythingToDo = true;
        }

        if (!$anythingToDo) {
            $output->success("Les privilèges sont à jour.");
            return;
        }

        $confirm = $output->confirm("Mettre à jour les privilèges ?", false);

        if ($confirm !== true) {
            $output->warning("Opération abandonnée");
            return;
        }

        foreach ($datasCreate as $fullCode => $privilegeData) {

            try {
                if( !property_exists($privilegeData, 'category_id')){
                    $output->error('Propriété categorie_id manquante dans la configuration : ' . print_r($privilegeData, true));
                    return;
                }
                $newPrivilege = new Privilege();
                $this->getEntityManager()->persist($newPrivilege);
                $newPrivilege->setCategorie($this->getEntityManager()->getRepository(CategoriePrivilege::class)->find($privilegeData->category_id))
                    ->setCode($privilegeData->code)
                    ->setSpot($privilegeData->spot)
                    ->setLibelle($privilegeData->libelle);

                if( property_exists($privilegeData, 'root')){
                    $newPrivilege->setRoot($this->getRootByFullCode($privilegeData->root));
                }

                $this->getEntityManager()->flush($newPrivilege);
                $output->writeln("<green>[ADD] Le privilège " . $privilegeData->code . " a bien été créé.</green>");

                $toUpdate[] = [ 'privilege' => $newPrivilege, 'data' => $privilegeData ];
            } catch (\Exception $e) {
                $output->error("Impossible de créé le privilège " . $privilegeData->code . " : " . $e->getMessage());
                return;
            }
        }

        /** @var Privilege $privilege */
        foreach ($toUpdate as $pToUpdate) {
            try {
                /** @var Privilege $privilege */
                $privilege = $pToUpdate['privilege'];
                $stdObject = $pToUpdate['data'];

                if ($privilege->getCategorie()->getId() != $stdObject->category_id) {
                    try {
                        $privilege->setCategorie($this->getEntityManager()->getRepository(CategoriePrivilege::class)->find($stdObject->category_id));
                    } catch (\Exception $e) {
                        throw new \Exception("La catégorie " . $stdObject->category_id . " n'existe pas.");
                    }
                }

                if(property_exists($stdObject, 'root')){
                    $privilege->setRoot($this->getRootByFullCode($stdObject->root));
                } else {
                    $privilege->setRoot(null);
                }

                $privilege->setCode($stdObject->code);
                $privilege->setSpot($stdObject->spot);
                $privilege->setLibelle($stdObject->libelle);
                $this->getEntityManager()->flush($privilege);
                $output->writeln("<green>[UPD] Le privilège " . $stdObject->code . " a bien été mis à jour.</green>");
            } catch (\Exception $e) {
                $output->writeln("<error>Impossible de mettre à jour le privilège " . $pToUpdate['privilege'] . " : " . $e->getMessage() . "</error>");
            }
        }

        foreach ($toRemove as $privilege) {
            try {
                $code = $privilege->getFullCode();
                $this->getEntityManager()->remove($privilege);
                $this->getEntityManager()->flush($privilege);
                $output->writeln("<green>[DEL] Le privilège " . $code . " a bien été supprimé.</green>");
            } catch (\Exception $e) {
                $output->writeln("<error>Impossible de supprimer le privilège " . $privilege->getFullCode() . " : " . $e->getMessage() . "</error>");
            }
        }

        $output->warning("Vous pouvez relancer la commande pour être sûr que les privilèges sont à jour.");

    }

    protected function getRootByFullCode($fullCode)
    {


            $re = '/(\w*)-(.*)/';
            preg_match_all($re, $fullCode, $matches, PREG_SET_ORDER, 0);
            $codeCategory = $matches[0][1];
            $codePrivilege = $matches[0][2];
            $category = $this->getEntityManager()->getRepository(CategoriePrivilege::class)->findOneBy([
                'code' => $codeCategory
            ]);
            try {
                return $this->getEntityManager()->getRepository(Privilege::class)->findOneBy([
                    'code' => $codePrivilege,
                    'categorie' => $category
                ]);
            } catch (\Exception $e) {
                return null;
            }



    }

    /**
     * Contrôle la mise à jour des privilèges.
     */
    public function checkPrivileges(){
//        $cheminFichier = realpath(__DIR__ . '/../../../../../install/privileges.json');
//        if (!file_exists($cheminFichier)) {
//            die("ERREUR : Fichier introuvable\n");
//        }
//        $contenuFichier = file_get_contents($cheminFichier);
//        if (!$contenuFichier) {
//            die("ERREUR : Impossible de lire le fichier : $contenuFichier\n");
//        }
//        $datas = json_decode($contenuFichier);
//        if (!$datas) {
//            die("ERREUR : Impossible de traiter les données du fichier ". json_last_error_msg()."\n");
//        }
//
//        // Mise à jour de la séquence
//        $rsm = new Query\ResultSetMapping();
//        $query = $this->getEntityManager()->createNativeQuery("select setval('privilege_id_seq',(select max(id)+1 from privilege), false)", $rsm);
//        $query->execute();
//
//        $toRemove = [];
//        $toAdd = [];
//        $toUpdate = [];
//        $verbose = false;
//
//        $privileges = $this->getEntityManager()->getRepository(Privilege::class)->findAll();
//
//        /** @var Privilege $p */
//        foreach ($privileges as $p) {
//            $property = $p->getFullCode();
//            $do = "";
//            try {
//                if (property_exists($datas, $property)) {
//                    // Mise à jour
//                    $updatable = $this->updatePrivilegeWitDatas($p,
//                        $datas->$property);
//                    if (false !== $updatable) {
//                        if ($verbose) {
//                            $this->consoleUpdateToDo($property . ' va être mis à jour');
//                        }
//                        $toUpdate[] = $updatable;
//                    } else {
//                        if ($verbose) {
//                            $this->consoleNothingToDo($property . ' est à jour');
//                        }
//                    }
//                    unset($datas->$property);
//                } else {
//                    $toRemove[] = $p;
//                    if ($verbose) {
//                        $this->consoleDeleteToDo($property . ' va être supprimé');
//                    }
//                }
//            } catch (\Exception $e) {
//                $this->consoleError($e->getMessage());
//                continue;
//            }
//
//        }
//
//        $this->consoleHeader("Opérations de maintenance à faire : ");
//        $anythingToDo = false;
//
//        if (count($toUpdate)) {
//            $this->consoleUpdateToDo("Il y'a " . count($toUpdate) . " privilèges à mettre à jour");
//            $anythingToDo = true;
//        }
//
//        if (count($toRemove)) {
//            $this->consoleDeleteToDo("Il y'a " . count($toRemove) . " privilèges à supprimer");
//            $anythingToDo = true;
//        }
//        if (count(get_object_vars($datas))) {
//            $this->consoleUpdateToDo("Il y'a " . count(get_object_vars($datas)) . " privilèges à ajouter");
//            $anythingToDo = true;
//        }
//
//        if (!$anythingToDo) {
//            $this->consoleSuccess("Les privilèges sont à jour.");
//            return;
//        }
//
//        $confirm = new Confirm("Continuer ? (Y/n) : ");
//        if (!$confirm->show()) {
//            return;
//        }
//
//
//        foreach ($datas as $fullCode => $privilegeData) {
//            try {
//                if( !property_exists($privilegeData, 'category_id')){
//                    $this->consoleError('Propriété categorie_id manquante dans la configuration : ' . print_r($privilegeData, true));
//                    continue;
//                }
//                $newPrivilege = new Privilege();
//                $this->getEntityManager()->persist($newPrivilege);
//                $newPrivilege->setCategorie($this->getEntityManager()->getRepository(CategoriePrivilege::class)->find($privilegeData->category_id))
//                    ->setCode($privilegeData->code)
//                    ->setSpot($privilegeData->spot)
//                    ->setLibelle($privilegeData->libelle);
//
//                $this->getEntityManager()->flush($newPrivilege);
//                $this->consoleSuccess("Le privilège " . $privilegeData->code . " a bien été créé.");
//            } catch (\Exception $e) {
//                $this->consoleError("Impossible de créé le privilège " . $privilegeData->code . " : " . $e->getMessage());
//
//            }
//        }
//
//        foreach ($toRemove as $privilege) {
//            try {
//                $this->getEntityManager()->remove($privilege);
//                $this->getEntityManager()->flush($privilege);
//                $this->consoleSuccess("Le privilège " . $privilegeData->code . " a bien été supprimé.");
//            } catch (\Exception $e) {
//                $this->consoleError("Impossible de supprimer le privilège " . $privilegeData->code . " : " . $e->getMessage());
//            }
//        }
//
//        /** @var Privilege $privilege */
//        foreach ($toUpdate as $privilege) {
//            try {
//                $this->getEntityManager()->flush($privilege);
//                $this->consoleSuccess("Le privilège " . $privilege->getCode() . " a bien été mis à jour.");
//            } catch (\Exception $e) {
//                $this->consoleError("Impossible de mettre à jour le privilège " . $privilege->getCode() . " : " . $e->getMessage());
//            }
//        }
//
//        $this->consoleWarn("Relancez la commande jusqu'à obtenir le message 'Les privilèges sont à jour'.");
    }
}