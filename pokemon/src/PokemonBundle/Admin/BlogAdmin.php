<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 05.08.16
 * Time: 15:41
 */

namespace PokemonBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use PokemonBundle\Entity\Blog;

class BlogAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createAt',
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('title')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->add('id')
            ->add('title')
            ->add('createAt', null, array('format' => 'Y-m-d H:i'))
            ->add('image', 'image', array(
                'template' => 'PokemonBundle:Default:image_value.html.twig'
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
        /**
         * @var Blog $object
         */
        $object = $this->getSubject();
        $fileImageOptions = array('required' => false);
        if ($object && ($webPath = $object->getImageUrl()))
            $fileImageOptions['help'] = '<img src="'.$webPath.'" class="admin-preview thumbnail" style="max-width: 500px; max-height: 500px;"/>';
        $fileImageDetailOptions = array('required' => false);
        if ($object && ($webPath = $object->getImageDetailUrl()))
            $fileImageDetailOptions['help'] = '<img src="'.$webPath.'" class="admin-preview thumbnail" style="max-width: 500px; max-height: 500px;"/>';

        $formMapper
            ->tab('Главная')
                ->with('Основная информация')
                    ->add('title')
                    ->add('createAt','date')
                    ->add('fileImage','file',$fileImageOptions)
                    ->add('fileImageDetail','file',$fileImageDetailOptions)
                    ->add('anotation','textarea')
                    ->add('text', 'textarea', array('attr' => array('class'=>'ckeditor'), 'help' => '<script src="/js/admin/ckeditor/ckeditor.js" type="text/javascript"></script>'))
                ->end()
            ->end()
            ->tab('Настройки')
                ->with('')
                    ->add('code',null, array('required' => false))
                    ->add('enabled')
                    ->add('titletitle','text',array('required' => false))
                    ->add('keywords','textarea',array('required' => false))
                    ->add('description','textarea',array('required' => false))
                ->end()
            ->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('createAt', null, array('format' => 'Y-m-d H:i'))
            ->add('image', 'image', array('template' => 'PokemonBundle:Default:image_value.html.twig'))
            ->add('imageDetail', 'image', array('template' => 'PokemonBundle:Default:image_detail_value.html.twig'))
            ->add('title')
            ->add('anotation')
            ->add('text',null,['template'=>'PokemonBundle:Default:fck.html.twig'])
            ->add('code')
            ->add('enabled')
            ->add('titletitle')
            ->add('keywords')
            ->add('description')
        ;
    }

    public function prePersist($pm) {
        /**
         * @var Blog $pm
         */
        $this->saveFile($pm);
    }

    public function preUpdate($pm) {
        $this->saveFile($pm);
    }

    public function saveFile($pm) {
        /**
         * @var Blog $pm
         */
        $pm->upload('Image');
        $pm->upload('ImageDetail');
    }
}