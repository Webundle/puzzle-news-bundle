<?php

namespace Puzzle\NewsBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Puzzle\NewsBundle\Form\Model\AbstractPostType;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class PostCreateType extends AbstractPostType
{
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'post_create');
        $resolver->setDefault('validation_groups', ['Create']);
    }
    
    public function getBlockPrefix() {
        return 'puzzle_admin_news_post_create';
    }
}