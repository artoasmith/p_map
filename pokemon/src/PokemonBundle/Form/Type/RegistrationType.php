<?php

namespace PokemonBundle\Form\Type;

use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationType extends BaseAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password')
            ->add('repassword')
            ->add('login')
            ->add('email','email')
            ->add('save', 'submit', ['label'=>'Create']);
    }

    public function getName()
    {
        return 'registration';
    }
}