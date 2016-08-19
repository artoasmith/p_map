<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PayCheck
 *
 * @ORM\Table(name="pay_check")
 * @ORM\Entity(repositoryClass="PokemonBundle\Repository\PayCheckRepository")
 */
class PayCheck
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var User
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     * @Exclude
     */
    private $user;

    /**
     * @var User
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="Point")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="point", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     * @Exclude
     */
    private $point;


    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="value", type="float",nullable=false)
     */
    private $value;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PayCheck
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \PokemonBundle\Entity\User $user
     * @return PayCheck
     */
    public function setUser(\PokemonBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PokemonBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set point
     *
     * @param \PokemonBundle\Entity\Point $point
     * @return PayCheck
     */
    public function setPoint(\PokemonBundle\Entity\Point $point = null)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return \PokemonBundle\Entity\User
     */
    public function getPoint()
    {
        return $this->point;
    }


    /**
     * Set value
     *
     * @param float $value
     *
     * @return PayCheck
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}

