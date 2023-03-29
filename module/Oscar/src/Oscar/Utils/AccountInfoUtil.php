<?php


namespace Oscar\Utils;


use Oscar\Service\SpentService;

class AccountInfoUtil
{
    private array $correspondances;
    private array $accountInfos;


    public static function getInstance(SpentService $spentService): AccountInfoUtil
    {
        // Liste des comptes général dans les dépenses rééls (Donnèes SIFAC)
        $usedAccounts = $spentService->getSpentTypeRepository()->getUsedAccount();
        $instance = new AccountInfoUtil();

        $instance->correspondances = [];
        $instance->accountInfos = [];

        foreach ($usedAccounts as $compte) {
            $infos = $spentService->getCompte($compte);
            $compteId = $infos["id"];

            $instance->correspondances[$compte] = $compteId;

            if (!array_key_exists($compteId, $instance->accountInfos)) {
                $instance->accountInfos[$compteId] = $infos;
                $instance->accountInfos[$compteId]['compteGeneral'] = [];
            }
            $instance->accountInfos[$compteId]['compteGeneral'][] = $compte;
        }

        return $instance;
    }

    public function getAccountInfoById(int $id): ?array
    {
        if (array_key_exists($id, $this->accountInfos)) {
            return $this->accountInfos[$id];
        }
        return null;
    }

    public function getAccountIdByCompteGeneral(string $compteGeneral): ?int
    {
        if (array_key_exists($compteGeneral, $this->correspondances)) {
            return $this->correspondances[$compteGeneral];
        }
        return null;
    }

    public function getCompteGeneralListByAccountIds(array $accountIds): array
    {
        $ids = [];
        foreach ($accountIds as $accountId) {
            $accountInfo = $this->getAccountInfoById($accountId);
            $ids = array_merge($ids, $accountInfo['compteGeneral']);
        }
        return array_unique($ids);
    }

    public function getAccountByCompteGeneral(string $compteGeneral): ?array
    {
        $accountId = $this->getAccountIdByCompteGeneral($compteGeneral);
        if($accountId){
            return $this->getAccountInfoById($accountId);
        }
        return null;
    }

    public function getAccounts() :array {
        return $this->accountInfos;
    }

    public function getCorrespondances() :array {
        return $this->correspondances;
    }
}