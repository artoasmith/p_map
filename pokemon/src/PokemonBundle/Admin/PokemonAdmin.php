<?php
namespace PokemonBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use PokemonBundle\Entity\Pokemon;

class PokemonAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->add('id')
            ->add('name')
            ->add('rare')
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
         * @var Pokemon $object
         */
        $object = $this->getSubject();
        $fileImageOptions = array('required' => false);
        if ($object && ($webPath = $object->getImageUrl()))
            $fileImageOptions['help'] = '<img src="'.$webPath.'" class="admin-preview thumbnail" />';

        $formMapper
            ->with('Основная информация')
                ->add('name',null,['label'=>'Название'])
                ->add('rare')
                ->add('fileImage','file',$fileImageOptions)
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
            ->add('name')
            ->add('rare')
        ;
    }

    public function prePersist($pm) {
        /**
         * @var Pokemon $pm
         */
        $this->saveFile($pm);
    }

    public function preUpdate($pm) {
        $this->saveFile($pm);
    }

    public function saveFile($pm) {
        /**
         * @var Pokemon $pm
         */
        $pm->upload('Image');
    }
}