<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/06/15 09:54
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UnicaenUtilisateur\Entity\Db\AbstractUser;
use UnicaenUtilisateur\Entity\Db\RoleInterface;
use UnicaenUtilisateur\Entity\Db\UserInterface;


/**
 * User entity abstract mother class.
 * @ORM\Entity
 * @ORM\Table(name="authentification")
 * @ORM\Entity(repositoryClass="Oscar\Entity\AuthentificationRepository")
 */
class Authentification extends AbstractUser
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true,  length=255)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="display_name", type="string", length=50, nullable=false)
     */
    protected $displayName;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $password;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $state = 0;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Role", fetch="EAGER")
     */
    protected $roles;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateLogin;

    /**
     * @var array
     * @ORM\Column(type="object", nullable=true)
     */
    protected $settings;

    /**
     * @var string a secret generated string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $secret;



    /**
     * @return AbstractRole|null
     */
    public function getLastRole()
    {
        return null;
    }

    /**
     * @param AbstractRole|null $lastRole
     * @return self
     */
    public function setLastRole(RoleInterface $lastRole = null) :void
    {

    }

    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->settings = [];
    }

    public function hasRole( $roleId ){
        foreach ($this->getRoles() as $role ){
            if( $roleId == $role ){
                return true;
            }
        }
        return false;
    }

    public function toJson()
    {
        $out = [
            'id' => $this->getId(),
            'displayName' => $this->getDisplayName(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'lastLogin' => $this->getDateLogin() ? $this->getDateLogin()->format('Y-m-d H:i:s') : null,
            'roles' => []
        ];

        /** @var Role $role */
        foreach( $this->getRoles() as $role ){
            $out['roles'][] = $role->getRoleId();
        }
        return $out;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return \DateTime
     */
    public function getDateLogin()
    {
        return $this->dateLogin;
    }

    /**
     * @param \DateTime $dateLogin
     */
    public function setDateLogin($dateLogin)
    {
        $this->dateLogin = $dateLogin;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     *
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Get role.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles->getValues();
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role
     *
     * @return void
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role
     *
     * @return void
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    public function getSetting($key, $defaultValue){
        $settings = $this->getSettings();
        if( $settings && array_key_exists($key, $settings) ){
            return $settings[$key];
        }
        return $defaultValue;
    }

    public function updateSetting( $key, $value ){
        $settings = $this->getSettings();
        if( !is_array($settings) ){
            $settings = [];
        }
        $settings[$key] = $value;
        $this->setSettings($settings);
    }

    ////////////////////////////////////////////////////////////////////////////
    public function hasRolesIds( array $rolesIds ){
        foreach ($this->getRoles() as $role ){
            if( in_array($role->getRoleId(), $rolesIds) ){
                return true;
            }
        }
        return false;
    }

    public function getPasswordResetToken()
    {
        // TODO: Implement getPasswordResetToken() method.
    }

    public function setPasswordResetToken($passwordResetToken)
    {
        // TODO: Implement setPasswordResetToken() method.
    }

    public function isLocal()
    {
        // TODO: Implement isLocal() method.
    }


    /**
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getDisplayName();
    }
}
