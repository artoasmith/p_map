<?php

namespace PokemonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;

class SettingsAdmin extends Admin
{

    public $last_position = 0;

    private $positionService;


    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'category',
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $categories = array();
        $query = $this->getModelManager()->getEntityManager('PokemonBundle:Settings')->createQueryBuilder('c')
            ->select('c.category as category')
            ->from('PokemonBundle:Settings', 'c')
            ->where('c.category IS NOT NULL')
            ->groupBy('c.category')
            ->distinct()
            ->getQuery()->getResult();
        foreach( $query as $category )
        {
            $categories[$category['category']] = $category['category'];
        }
        $datagridMapper
            ->add('id')
            ->add('code')
            ->add('category', null, array(), 'choice', array(
                'choices' => $categories
            ))
            ->add('value')
            ->add('type', null, array(), 'choice', array(
                'choices' => array('file' => 'File', 'string' => 'String', 'text' => 'Text','checkbox' => 'Checkbox'),
                'preferred_choices' => array('file'),
            ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('code')
            ->add('category')
            ->add('value', 'image', array(
                'template' => 'PokemonBundle:Settings:list_value.html.twig'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array()
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $settings = $this->getSubject();
        $imagePreview = '';
        $required = true;
        if( $settings )
        {
            if( $settings->getType() == 'image')
            {
                $imagePreview = '<img class="form_image" src="'.$settings->getWebPath().'" style="max-width: 500px; max-height: 500px;" >';
                $required = false;
            }
        }
        $formMapper
            ->add('uniqid','hidden')
            ->add('code')
            ->add('category')
            ->add('type', 'choice', array(
                'choices' => array('image' => 'Image', 'string' => 'String', 'text' => 'Text','checkbox' => 'Switcher'),
                'data' => ($settings && strlen($settings->getType()) > 0) ? $settings->getType() : 'image',
                'help' => $imagePreview,
            ))
            ->add('value', ($settings->getType()=='chackbox'?'checkbox':'text'), array('required' => $required, 'help' => '<script src="/js/sonata_settings.js"></script>'))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('code')
            ->add('category')
            ->add('value', 'image', array('template' => 'GulpfishBackendBundle:Settings:show_value.html.twig'))
        ;
    }

    public function prePersist($settings)
    {
        if( $settings->getType() == 'image' ) {
            $this->saveFile($settings);
        }
    }


    public function preUpdate($settings)
    {
        if ($settings->getType() == 'image') {
            $this->saveFile($settings);
        }
    }

    public function saveFile($settings)
    {
        $settings->upload();
    }

    function postRemove($settings)
    {
        if(  $settings->getType() == 'image' && file_exists($settings->getAbsolutePath()) )
        {
            unlink($settings->getAbsolutePath());
        }
    }

    public function preRemove($settings)
    {

    }
}
