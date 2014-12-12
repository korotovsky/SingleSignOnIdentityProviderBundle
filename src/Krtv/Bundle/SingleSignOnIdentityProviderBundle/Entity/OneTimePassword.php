<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FM\SingleSignOnBundle\Entity\OTP
 *
 * @ORM\Table(name="fm_otp")
 * @ORM\Entity
 */
class OneTimePassword extends \Krtv\SingleSignOn\Model\OneTimePassword
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
     * @ORM\Column(name="password", type="string", unique=true)
     */
    protected $password;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", unique=true)
     */
    protected $hash;

    /**
     * @var boolean $used
     *
     * @ORM\Column(name="used", type="boolean")
     */
    protected $used;

    /**
     * @var \Datetime $created
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;
}
