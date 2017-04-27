<?php
namespace UnicaenAuth\Authentication\Storage;

use Zend\EventManager\Event;

/**
 * Événement propagé dans la chaîne de responsabilité {@see Chain},
 * utilisé pour collecter les diverses données concernant l'identité authentifiée.
 *
 * Exemples de données collectées :
 *  - <code>array('ldap' => object(UnicaenApp\Entity\Ldap\People), 'db' => object(UnicaenAuth\Entity\Db\User))</code>
 *  - <code>array('ldap' => object(UnicaenApp\Entity\Ldap\People), 'db' => null)</code>
 *  - <code>array('ldap' => null, 'db' => object(UnicaenAuth\Entity\Db\User))</code>
 *  - <code>array('ldap' => null, 'db' => null)</code>
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see Chain
 */
class ChainEvent extends Event
{
    /**
     * @var array
     */
    protected $contents = [];

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If reading contents from storage is impossible
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws \Zend\Authentication\Exception\ExceptionInterface If writing $contents to storage is impossible
     * @return void
     */
    public function addContents($key, $contents)
    {
        $this->contents[$key] = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @throws \Zend\Authentication\Exception\ExceptionInterface If clearing contents from storage is impossible
     * @return void
     */
    public function clearContents()
    {
        $this->contents = [];
    }
}