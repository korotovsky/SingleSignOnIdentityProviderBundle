<?php

namespace FM\SingleSignOnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FM\SingleSignOnBundle\Entity\OTP
 *
 * @ORM\Table(name="fm_otp")
 * @ORM\Entity
 */
class OneTimePassword
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", unique="true")
     */
    protected $password;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", unique="true")
     */
    protected $hash;

    /**
     * @var boolean $used
     *
     * @ORM\Column(name="used", type="boolean")
     */
    protected $used;

    /**
     * @var datetime $created
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set hash
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set used
     *
     * @param boolean $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }

    /**
     * Get used
     *
     * @return boolean
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }
}