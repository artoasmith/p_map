<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Point
 *
 * @ORM\Table(name="point")
 * @ORM\Entity(repositoryClass="PokemonBundle\Repository\PointRepository")
 */
class Point
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
     *
     * @ORM\Column(name="createAt", type="datetime")
     */
    private $createAt;

    /**
     * @var float
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = -90,
     *      max = 90,
     *      minMessage = "This value must be at least {{ limit }} bigger",
     *      maxMessage = "This value must be lower than {{ limit }}"
     * )
     * @ORM\Column(name="locationX", type="float")
     */
    private $locationX;

    /**
     * @var float
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = -180,
     *      max = 180,
     *      minMessage = "This value must be at least {{ limit }} bigger",
     *      maxMessage = "This value must be lower than {{ limit }}"
     * )
     * @ORM\Column(name="locationY", type="float")
     */
    private $locationY;

    /**
     * @var Pokemon
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Pokemon")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pokemon", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Exclude
     */
    private $pokemon;

    /**
     * @var string
     * @ORM\Column(name="jsonInfo", type="string", length=255, nullable=true)
     */
    private $jsonInfo;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="author", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     * @Exclude
     */
    private $author;

    /**
     * @var boolean
     * @ORM\Column(name="confirm", type="boolean")
     */
    private $confirm=false;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled=true;

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
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Point
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set locationX
     *
     * @param float $locationX
     *
     * @return Point
     */
    public function setLocationX($locationX)
    {
        $this->locationX = $locationX;

        return $this;
    }

    /**
     * Get locationX
     *
     * @return float
     */
    public function getLocationX()
    {
        return $this->locationX;
    }

    /**
     * Set locationY
     *
     * @param float $locationY
     *
     * @return Point
     */
    public function setLocationY($locationY)
    {
        $this->locationY = $locationY;

        return $this;
    }

    /**
     * Get locationY
     *
     * @return float
     */
    public function getLocationY()
    {
        return $this->locationY;
    }


    /**
     * Set pokemon
     *
     * @param \PokemonBundle\Entity\Pokemon $pokemon
     * @return Point
     */
    public function setPokemon(\PokemonBundle\Entity\Pokemon $pokemon = null)
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * Get pokemon
     *
     * @return \PokemonBundle\Entity\Pokemon
     */
    public function getPokemon()
    {
        return $this->pokemon;
    }

    /**
     * @return string
     */
    public function getJsonInfo()
    {
        return $this->jsonInfo;
    }

    /**
     * @param string $jsonInfo
     * @return Point
     */
    public function setJsonInfo($jsonInfo)
    {
        $this->jsonInfo = $jsonInfo;
        return $this;
    }

    /**
     * Set author
     *
     * @param \PokemonBundle\Entity\User $author
     * @return Point
     */
    public function setAuthor(\PokemonBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \PokemonBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set confirm
     *
     * @param boolean $confirm
     * @return Point
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * Get confirm
     *
     * @return boolean
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Point
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    public function __toString()
    {
        return ($this->getId()>0?$this->getLocationX().' X '.$this->getLocationY().' '.($this->getPokemon()?$this->getPokemon()->getName():''):'-');
    }
}

