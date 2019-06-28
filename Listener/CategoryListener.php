<?php

namespace Puzzle\NewsBundle\Listener;

use Doctrine\ORM\EntityManager;
use Puzzle\NewsBundle\Entity\Archive;
use Puzzle\NewsBundle\Event\PostEvent;
use Puzzle\AdminBundle\Event\AdminInstallationEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Puzzle\NewsBundle\Entity\Category;

class CategoryListener
{
    /**
     * @var EntityManager
     */
    private $em;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    public function __construct(EntityManager $em, TranslatorInterface $translator){
        $this->em = $em;
        $this->translator = $translator;
    }
    
    public function onAdminInstalling(AdminInstallationEvent $event) {
        if (! $this->em->getRepository(Category::class)->findOneBy(['default' => true])) {
            $category = new Category();
            $category->setName($this->translator->trans('news.category.default.name', [], 'news'))
                     ->setDescription($this->translator->trans('news.category.default.description', [], 'news'))
                     ->setDefault(true);
            
            $this->em->persist($category);
            $this->em->flush($category);
            
            $event->notifySuccess($this->translator->trans('news.category.default.success', [], 'news'));
        }
        
        return;
    }
}

?>
