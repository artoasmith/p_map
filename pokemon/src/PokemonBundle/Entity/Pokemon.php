<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PokemonBundle\Base\UploaderEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pokemon
 *
 * @ORM\Table(name="pokemon")
 * @ORM\Entity(repositoryClass="PokemonBundle\Repository\PokemonRepository")
 */
class Pokemon extends UploaderEntity
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Assert\File(maxSize="10000000")
     */
    private $fileImage;

    /**
     * @var integer
     *
     * @ORM\Column(name="rare", type="integer",length=2)
     */
    private $rare;

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
     * Set name
     *
     * @param string $name
     * @return Pokemon
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Pokemon
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return (empty($this->image)?'':$this->defaultFolderPath().$this->image);
    }
    /**
     * @return mixed
     */
    public function getFileImage()
    {
        return $this->fileImage;
    }

    /**
     * @param mixed $fileImage
     *
     * @return Pokemon
     */
    public function setFileImage($fileImage)
    {
        $this->fileImage = $fileImage;

        return $this;
    }

    /**
     * Set rare
     *
     * @param integer $rare
     * @return Pokemon
     */
    public function setRare($rare)
    {
        $this->rare = $rare;

        return $this;
    }

    /**
     * Get rare
     *
     * @return integer
     */
    public function getRare()
    {
        return $this->rare;
    }

    /**
     * @return string
     */
    public function defaultFolderPath(){
        return '/upload/pokemonImages/';
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return ($this->getName()?$this->getName():'-');
    }
}
