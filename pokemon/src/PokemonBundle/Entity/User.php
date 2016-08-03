<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="PokemonBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="rate", type="integer", nullable=true)
     */
    private $rate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createTokenAt", type="datetime")
     */
    private $createTokenAt;

    public function __construct()
    {
        parent::__construct();
    }

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
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param int $rate
     * @return User
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }


    /**
     * Set createTokenAt
     *
     * @param \DateTime $createTokenAt
     * @return User
     */
    public function setCreateTokenAt($createTokenAt)
    {
        $this->createTokenAt = $createTokenAt;

        return $this;
    }

    /**
     * Get createTokenAt
     *
     * @return \DateTime
     */
    public function getCreateTokenAt()
    {
        return $this->createTokenAt;
    }
}
