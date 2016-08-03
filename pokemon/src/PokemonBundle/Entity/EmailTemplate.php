<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 01.08.16
 * Time: 12:07
 */

namespace PokemonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityManager;
use PokemonBundle\PokemonBundle;

/**
 * EmailTemplate
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EmailTemplate
{
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
     * @ORM\Column(name="fromName", type="string", length=255)
     */
    private $fromName;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

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
     * Set fromName
     *
     * @param string $fromName
     * @return EmailTemplate
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return EmailTemplate
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
     * Set subject
     *
     * @param string $subject
     * @return EmailTemplate
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return EmailTemplate
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
     * Set text
     *
     * @param string $text
     * @return EmailTemplate
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

    public function __toString()
    {
        return ($this->id != "") ? substr($this->name,0,50).(strlen($this->name)>50?'...':'')." (код ".$this->getCode().")" : "Шаблон письма";
    }

    public function parseText($attributes)
    {
        $keys = array();
        $values = array();

        foreach ($attributes as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        return str_replace($keys, $values, $this->text);
    }

    public function parseSubject($attributes)
    {
        $keys = array();
        $values = array();

        foreach ($attributes as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        return str_replace($keys, $values, $this->subject);
    }

    public function parseFromName($attributes)
    {
        $keys = array();
        $values = array();

        foreach ($attributes as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        return str_replace($keys, $values, $this->fromName);
    }


    public static function sendEmail($code, $attributes, $container)
    {
        $em = $container->get('doctrine')->getManager();
        /**
         * @var EmailTemplate $template
         * @var Users $user
         */
        $template = $em->getRepository('PokemonBundle:EmailTemplate')->findOneByCode($code);

        if( !$template )
        {
            throw new HttpException(404, 'EmailTempalte with code "'.$code.'" not found.');
        }

        if(!empty($attributes['emailTo'])) {

            $message = \Swift_Message::newInstance()
                ->setSubject($template->parseSubject($attributes))
                ->setFrom(array($attributes['emailFrom'] => $template->parseFromName($attributes)))
                ->setTo($attributes['emailTo'])
                ->setBody(

                    $container->get('templating')->render(
                        'PokemonBundle:EmailTemplate:default.html.twig',
                        array('content' => $template->parseText($attributes))
                    ),
                    'text/html'
                );

            if ($container->get('mailer')->send($message) == 1)
                return true;
        }
        return false;
    }
}