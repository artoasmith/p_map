<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use PokemonBundle\Base\UploaderEntity;
/**
 * Settings
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("code")
 */
class Settings extends UploaderEntity
{
    // No ORM attributes
    private $fileLoaded = false;
    private $uniqid;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type = 'string';

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     */
    private $category;



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
     * Set value
     *
     * @param string $value
     * @return Settings
     */
    public function setValue($value)
    {
        if( $this->getType() == 'image' && empty($value) )
            return $this;
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setUniqid($uniqid)
    {
        $this->uniqid = $uniqid;

        return $this;
    }

    public function getUniqid()
    {
        return $this->uniqid;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Settings
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Settings
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * Set category
     *
     * @param string $category
     * @return Settings
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    public function getAbsolutePath()
    {
        return null === $this->value ? null : $this->getUploadRootDir().'/'.$this->value;
    }

    public function getWebPath()
    {
        return null === $this->value ? null : '/'.$this->getUploadDir().'/'.$this->value;
    }

    public function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/'. $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'upload'.'/'.'settings';
    }

    public function upload($a='',$b='')
    {
        if (null === $this->value || empty($this->value) || (!(get_class((object)$this->value) === 'Symfony\Component\HttpFoundation\File\UploadedFile')) ) {
            return;
        }

        $filename = substr( md5($this->value->getClientOriginalName().rand()), 0, 7).'.'.$this->value->guessExtension();
        $filePath = $this->getUploadDir();
        $filename = str_replace('..', '.', $filename);

        $file = $this->value;
        $newfile = $this->documentRoot() .'/'. $filePath .'/'. $filename;

        if (\copy($file, $newfile)) {
            $this->setValue($filename);
            $this->fileLoaded = true;
        }
    }

    /**
     * @return string
     */
    public function getSettingValue(){

        if($this->getType()=="image"){
            $request = Request::createFromGlobals();
            $server_name = '';
            $value = $server_name.$this->getWebPath();
        }else $value = $this->getValue();
        return $value;
    }

    public function __toString()
    {
        return ($this->id != "") ? $this->getCategory()." настройка" : "-";
    }
}
