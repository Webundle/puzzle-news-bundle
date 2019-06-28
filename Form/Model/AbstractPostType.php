<?php

namespace Puzzle\NewsBundle\Form\Model;

use Puzzle\NewsBundle\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Puzzle\NewsBundle\Entity\Category;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class AbstractPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        parent::buildForm($builder, $options);
        $builder
            ->add('name', TextType::class)
            ->add('shortDescription', TextareaType::class,['required' => false])
            ->add('description', TextareaType::class,['required' => false])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('picture', HiddenType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('pictures', HiddenType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('tags', TextType::class, ['required' => false])
            ->add('enableComments', CheckboxType::class, ['required' => false])
            ->add('visible', CheckboxType::class, ['required' => false])
            ->add('flash', CheckboxType::class, array('required' => false))
            ->add('flashExpiresAt', TextType::class, [
                'mapped' => false,
                'required' => false
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => Post::class,
            'validation_groups' => array(
                Post::class,
                'determineValidationGroups',
            ),
        ));
    }
}