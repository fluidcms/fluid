<?php
namespace Fluid\User;

use Fluid\Session\SessionCollection;

class UserEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var SessionCollection
     **/
    private $sessions;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string|null
     */
    private $name = null;

    public function __construct()
    {
        $this->setSessions(new SessionCollection);
    }

    /**
     * @return array
     */
    public function toArray()
    {
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param SessionCollection $sessions
     * @return $this
     */
    public function setSessions(SessionCollection $sessions)
    {
        $this->sessions = $sessions;
        return $this;
    }

    /**
     * @return SessionCollection
     */
    public function getSessions()
    {
        return $this->sessions;
    }
}