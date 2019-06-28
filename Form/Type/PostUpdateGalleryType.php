<?php

namespace Puzzle\NewsBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Puzzle\NewsBundle\Form\Model\AbstractPostType;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class PostUpdateGalleryType extends AbstractPostType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder
            ->remove('name')
            ->remove('description')
            ->remove('category')
            ->remove('enableComments')
            ->remove('visible')
            ->remove('flash')
            ->remove('flashExpiresAt')
            ->remove('tags')
            ->remove('picture')
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'post_update_gallery');
        $resolver->setDefault('validation_groups', ['Update']);
    }
    
    public function getBlockPrefix() {
        return 'puzzle_admin_news_post_update_gallery';
    }
}