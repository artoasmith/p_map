<?php

namespace PokemonBundle\Form\Type;


use Propel\Bundle\PropelBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PointType extends BaseAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locationX')
            ->add('locationY')
            ->add('pokemon','entity',['class' => 'PokemonBundle:Pokemon'])
            ->add('save', 'submit', ['label'=>'Create']);
    }

    public function getName()
    {
        return 'point';
    }
}