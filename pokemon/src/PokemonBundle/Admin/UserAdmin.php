<?php
/**
 * Created by PhpStorm.
 * User: artoa
 * Date: 26.07.2016
 * Time: 13:43
 */

namespace PokemonBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use PokemonBundle\Entity\User;
use Sonata\UserBundle\Admin\Model\UserAdmin as SonataUserAdmin;

class UserAdmin extends SonataUserAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->add('email')
            //->add('groups')
            ->add('enabled', null, array('editable' => true))
            ->add('locked', null, array('editable' => true))
            ->add('createdAt')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array()
                )
            ))
        ;

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', 'string', array('template' => 'SonataUserBundle:Admin:Field/impersonating.html.twig'))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('id')
            ->add('username')
            ->add('locked')
            ->add('email')
            // ->add('groups')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('General')
                ->add('username')
                ->add('email')
            ->end()
            ->with('Profile')
                ->add('dateOfBirth')
                ->add('firstname')
              //  ->add('lastname')
                ->add('website')
                ->add('biography')
                ->add('gender')
                ->add('locale')
                ->add('timezone')
                ->add('phone')
            ->end()
            ->with('Social')
                ->add('facebookUid')
                ->add('vkontakteUid')
                ->add('gplusUid')
                ->add('instagramUid')
                //->add('facebookName')
                //->add('twitterUid')
                //->add('twitterName')
                //->add('gplusUid')
                //->add('gplusName')
            ->end()
          /*  ->with('Security')
                ->add('token')
                ->add('twoStepVerificationCode')
            ->end()*/
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        /**
         * @var User $object
         */
        $object = $this->getSubject();
        $fileImageOptions = array('required' => false);
        if ($object && ($webPath = $object->getImageUrl()))
            $fileImageOptions['help'] = '<img src="'.$webPath.'" class="admin-preview thumbnail"  style="max-width: 500px; max-height: 500 px;" />';

        // define group zoning
        $formMapper
            ->tab('User')
                ->with('Profile', array('class' => 'col-md-6'))->end()
                ->with('General', array('class' => 'col-md-6'))->end()
                ->with('Social', array('class' => 'col-md-6'))->end()
            ->end()
            ->tab('Security')
                ->with('Status', array('class' => 'col-md-12'))->end()
                //->with('Keys', array('class' => 'col-md-6'))->end()
                ->with('Roles', array('class' => 'col-md-12'))->end()
            ->end()
        ;

        $now = new \DateTime();

        $formMapper
            ->tab('User')
                ->with('General')
                    ->add('username')
                    ->add('email')
                    ->add('plainPassword', 'text', array(
                        'required' => (!$this->getSubject() || is_null($this->getSubject()->getId())),
                    ))
                ->end()
            ->with('Profile')
                ->add('dateOfBirth', 'sonata_type_date_picker', array(
                    'years' => range(1900, $now->format('Y')),
                    'dp_min_date' => '1-1-1900',
                    'dp_max_date' => $now->format('c'),
                    'required' => false,
                ))
                ->add('firstname', null, array('required' => false))
                ->add('fileImage', 'file', $fileImageOptions)
                ->add('website', 'url', array('required' => false))
                ->add('biography', 'text', array('required' => false))
                ->add('gender', 'sonata_user_gender', array(
                    'required' => true,
                    'translation_domain' => $this->getTranslationDomain(),
                ))
                ->add('locale', 'locale', array('required' => false))
                ->add('timezone', 'timezone', array('required' => false))
                ->add('phone', null, array('required' => false))
            ->end()
            ->with('Social')
                ->add('facebookUid', null, array('required' => false))
                ->add('vkontakteUid', null, array('required' => false))
                ->add('gplusUid', null, array('required' => false))
                ->add('instagramUid', null, array('required' => false))
            ->end()
            ->end()
            ->tab('Security')
            ->with('Status')
                ->add('locked', null, array('required' => false))
               // ->add('expired', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
               // ->add('credentialsExpired', null, array('required' => false))
            ->end()
            ->with('Roles')
                ->add('realRoles', 'sonata_security_roles', array(
                    'label' => 'form.label_roles',
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                ))
            ->end()
           /* ->with('Keys')
                ->add('token', null, array('required' => false))
                ->add('twoStepVerificationCode', null, array('required' => false))
            ->end()*/
            ->end()
        ;
    }

    public function prePersist($pm) {
        /**
         * @var User $pm
         */
        $this->saveFile($pm);
    }

    public function preUpdate($pm) {
        $this->saveFile($pm);
    }

    public function saveFile($pm) {
        /**
         * @var User $pm
         */
        $pm->upload('Image');
    }
}