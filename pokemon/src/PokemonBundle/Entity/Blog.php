<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PokemonBundle\Base\UploaderEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Blog
 *
 * @ORM\Table(name="blog")
 * @DoctrineAssert\UniqueEntity(fields="code", message="Пост с таким кодом уже существует")
 * @ORM\Entity(repositoryClass="PokemonBundle\Repository\BlogRepository")
 */
class Blog extends UploaderEntity
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
     * @ORM\Column(name="createAt", type="datetime", nullable=true)
     */
    private $createAt;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="anotation", type="text", nullable=true)
     */
    private $anotation;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

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
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled=true;

    /**
     * @var string
     *
     * @ORM\Column(name="titleTitle", type="string", length=255, nullable=true)
     */
    private $titleTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="text", nullable=true)
     */
    private $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @Assert\Regex(
     *     pattern="/^[_a-zA-Z][a-zA-z-_0-9]*$/",
     *     match=true,
     *     message="Недопустимый формат"
     * )
     * @ORM\Column(name="code", type="string", length=255, nullable=true, unique=true)
     */
    private $code;

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
     *
     * @return Blog
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
     * Set title
     *
     * @param string $title
     *
     * @return Blog
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set anotation
     *
     * @param string $anotation
     *
     * @return Blog
     */
    public function setAnotation($anotation)
    {
        $this->anotation = $anotation;

        return $this;
    }

    /**
     * Get anotation
     *
     * @return string
     */
    public function getAnotation()
    {
        return $this->anotation;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Blog
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Blog
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
     */
    public function setFileImage($fileImage)
    {
        $this->fileImage = $fileImage;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Blog
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

    /**
     * Set titleTitle
     *
     * @param string $title
     *
     * @return Blog
     */
    public function setTitleTitle($title)
    {
        $this->titleTitle = $title;

        return $this;
    }

    /**
     * Get titleTitle
     *
     * @return string
     */
    public function getTitleTitle()
    {
        return $this->titleTitle;
    }

    /**
     * Set keywords
     *
     * @param string $text
     *
     * @return Blog
     */
    public function setKeywords($text)
    {
        $this->keywords = $text;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set description
     *
     * @param string $text
     *
     * @return Blog
     */
    public function setDescription($text)
    {
        $this->description = $text;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set code
     *
     * @param string $text
     *
     * @return Blog
     */
    public function setCode($text)
    {
        $this->code = $text;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function defaultFolderPath(){
        return '/upload/blog/';
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return ($this->getTitle()?$this->getTitle():'-');
    }
}

