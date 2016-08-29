<?php

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Symfony\Component\Yaml\Parser;

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
     * @ORM\Column(name="createTokenAt", type="datetime", nullable=true)
     */
    private $createTokenAt;

    /**
     * @var string
     *
     * @ORM\Column(name="vkontakteUid", type="string", nullable=true)
     */
    private $vkontakteUid;

    /**
     * @var string
     *
     * @ORM\Column(name="instagramUid", type="string", nullable=true)
     */
    private $instagramUid;


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
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="balance", type="float",nullable=false)
     */
    private $balance;

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

    /**
     * Get vkontakteUid
     *
     * @return string
     */
    public function getVkontakteUid()
    {
        return $this->vkontakteUid;
    }

    /**
     * Set vkontakteUid
     *
     * @param string $vkontakteUid
     * @return User
     */
    public function setVkontakteUid($vkontakteUid)
    {
        $this->vkontakteUid = $vkontakteUid;

        return $this;
    }

    /**
     * Get instagramUid
     *
     * @return string
     */
    public function getInstagramUid()
    {
        return $this->instagramUid;
    }

    /**
     * Set instagramUid
     *
     * @param string $instagramUid
     * @return User
     */
    public function setInstagramUid($instagramUid)
    {
        $this->instagramUid = $instagramUid;

        return $this;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return User
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
     * @return User
     */
    public function setFileImage($fileImage)
    {
        $this->fileImage = $fileImage;

        return $this;
    }

    /**
     * Get balance
     *
     * @return string
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set balance
     *
     * @param string $balance
     * @return User
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return string
     */
    public function defaultFolderPath(){
        return '/upload/user_image/';
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return ($this->getId()?$this->getUsername():'-');
    }

    public function documentRoot(){
        $yaml = new Parser();
        $a = $yaml->parse(file_get_contents(__DIR__ . '/../../../app/config/params.yml'));
        return (isset($a['document_root'])?$a['document_root']:'');
    }


    public function clearOldUpload($type){
        if(!$type)
            return;

        $file = $this->documentRoot().$this->defaultFolderPath().$this->{'get'.$type}();
        @unlink($file);
    }

    public function upload($type, $fileurl = ''){
        if (empty($fileurl)) {
            if (null === $this->{'getFile' . $type}()) {
                return;
            }

            $filename = md5(time() . rand(5, 100)) . '.' . pathinfo($this->{'getFile' . $type}()->getClientOriginalName(), PATHINFO_EXTENSION);
            $filePath = $this->defaultFolderPath();
            $filename = str_replace('..', '.', $filename);


            $this->clearOldUpload($type);

            $file = $this->{'getFile' . $type}();
            $newfile = $this->documentRoot() . $filePath . $filename;

            if (\copy($file, $newfile)) {
                $this->{'set' . $type}($filename);
            }
        } else {
            $arr = @file($fileurl);
            if (!empty($arr)) {

                $pathinfo = pathinfo($fileurl);
                if (strpos($pathinfo['extension'], '?') !== false)
                    $filename = md5(time() . rand(5, 100)) . '.' . substr($pathinfo['extension'], 0, strpos($pathinfo['extension'], '?'));
                else
                    $filename = md5(time() . rand(5, 100)) . '.' . $pathinfo['extension'];

                $filePath = $this->defaultFolderPath();
                $filename = str_replace('..', '.', $filename);

                if (strpos($fileurl, '?') !== false) {
                    $filename = substr($filename, 0, strpos($fileurl, '?'));
                }

                $this->clearOldUpload($type);

                $newfile = $this->documentRoot() . $filePath . $filename;
                if (\copy($fileurl, $newfile)) {
                    $this->{'set' . $type}($filename);
                }
            }
        }
    }
}
