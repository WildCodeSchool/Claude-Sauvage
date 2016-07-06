<?php

namespace CS\GrcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idreceiver')
            ->add('content')
            ->add('idcategory', 'entity', array(
                'class'    => 'GrcBundle:Grccategory',
                'property' => 'name',
                'multiple' => false
                ))
            ->add('idsouscategory')
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CS\GrcBundle\Entity\Ticket'
        ));
    }
}


