<?php

namespace Puzzle\NewsBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Puzzle\NewsBundle\Form\Model\AbstractCategoryType;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class CategoryCreateType extends AbstractCategoryType
{
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'category_create');
        $resolver->setDefault('validation_groups', ['Create']);
    }
    
    public function getBlockPrefix() {
        return 'puzzle_admin_news_category_create';
    }
}