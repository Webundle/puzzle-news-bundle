<?php
namespace Puzzle\NewsBundle\Controller;

use Puzzle\NewsBundle\Entity\Archive;
use Puzzle\NewsBundle\Entity\Category;
use Puzzle\NewsBundle\Entity\Comment;
use Puzzle\NewsBundle\Entity\Post;
use Puzzle\NewsBundle\Form\Type\CategoryCreateType;
use Puzzle\NewsBundle\Form\Type\CategoryUpdateType;
use Puzzle\NewsBundle\Form\Type\PostCreateType;
use Puzzle\NewsBundle\Form\Type\PostUpdateType;
use Puzzle\MediaBundle\MediaEvents;
use Puzzle\MediaBundle\Event\FileEvent;
use Puzzle\MediaBundle\Util\MediaUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Puzzle\NewsBundle\Form\Type\PostUpdateGalleryType;
use Puzzle\NewsBundle\NewsEvents;
use Puzzle\NewsBundle\Event\PostEvent;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class AdminController extends Controller
{
    /***
     * Show categories
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCategoriesAction(Request $request) {
        return $this->render("NewsBundle:Admin:list_categories.html.twig", array(
            'categories' => $this->getDoctrine()->getRepository(Category::class)->findBy(['parentNode' => null])
        ));
    }
    
    /***
     * Show category
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCategoryAction(Request $request, Category $category) {
        $rep = $this->getDoctrine()->getRepository(Category::class);
        return $this->render("NewsBundle:Admin:show_category.html.twig", array(
            'category' => $category,
            'childNodes' => $rep->findBy(['parentNode' => $category->getId()])
        ));
    }
    
    /***
     * Create category
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCategoryAction(Request $request) {
        $category = new Category();
        $em = $this->getDoctrine()->getManager();
        $parentId = $request->query->get('parent');
        
        if ($parentId !== null && $parent = $em->getRepository(Category::class)->find($parentId)){
            $category->setParentNode($parent);
        }
        
        $form = $this->createForm(CategoryCreateType::class, $category, [
            'method' => 'POST',
            'action' => $parentId ? 
                        $this->generateUrl('puzzle_admin_news_category_create', ['parent' => $parentId]) : 
                        $this->generateUrl('puzzle_admin_news_category_create')
        ]);
        $form->handleRequest($request);
       
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $em->persist($category);
            $em->flush();
            
            $message = $this->get('translator')->trans('news.category.create.success', [
                '%categoryName%' => $category->getName()
            ], 'news');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            
            if (null !== $parentId) {
                return $this->redirectToRoute('puzzle_admin_news_category_show', ['id' => $parentId]);
            }
            
            return $this->redirectToRoute('puzzle_admin_news_category_list');
        }
        
        return $this->render("NewsBundle:Admin:create_category.html.twig", array(
            'form' => $form->createView()
        ));
    }
    
    
    /***
     * Update category
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateCategoryAction(Request $request, Category $category) {
        $parentId = $request->query->get('parent');
        
        $form = $this->createForm(CategoryUpdateType::class, $category, [
            'method' => 'POST', 
            'action' => $parentId ?
                        $this->generateUrl('puzzle_admin_news_category_update', ['id' => $category->getId(), 'parent' => $parentId]) :
                        $this->generateUrl('puzzle_admin_news_category_update', ['id' => $category->getId()])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $message = $this->get('translator')->trans('news.category.update.success', [
                '%categoryName%' => $category->getName()
            ], 'news');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            
            if (null !== $parentId) {
                return $this->redirectToRoute('puzzle_admin_news_category_show', ['id' => $parentId]);
            }
            
            return $this->redirectToRoute('puzzle_admin_news_category_show', ['id' => $category->getId()]);
        }
        
        return $this->render("NewsBundle:Admin:update_category.html.twig", array(
            'category' => $category,
            'form' => $form->createView()
        ));
    }
    
    /***
     * Delete category
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCategoryAction(Request $request, Category $category) {
        $message = $this->get('translator')->trans('news.category.delete.success', [
            '%categoryName%' => $category->getName()
        ], 'news');
        $parent = $category->getParentNode();
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        
        if (null !== $parent) {
            return $this->redirectToRoute('puzzle_admin_news_category_show', ['id' => $parent->getId()]);
        }
        
        return $this->redirectToRoute('puzzle_admin_news_category_list');
    }
    
    
    /***
     * Show Posts
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listPostsAction(Request $request){
        return $this->render("NewsBundle:Admin:list_posts.html.twig", array(
            'posts' => $this->getDoctrine()->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC'])
        ));
    }
    
    /***
     * Show Post
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPostAction(Request $request, Post $post){
        return $this->render("NewsBundle:Admin:show_post.html.twig", array('post' => $post));
    }
    
    /***
     * Create post
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createPostAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $categoryId = $request->query->get('category');
        
        if ($categoryId && $category = $em->getRepository(Category::class)->find($categoryId)) {
            $post->setCategory($category);
        }
        
        $form = $this->createForm(PostCreateType::class, $post, [
            'method' => 'POST',
            'action' => $categoryId ? $this->generateUrl('puzzle_admin_news_post_create', ['category' => $categoryId]) 
                                    : $this->generateUrl('puzzle_admin_news_post_create')
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['puzzle_admin_news_post_create'];
            
            $tags = $post->getTags() !== null ? explode(',', $post->getTags()) : null;
            $post->setTags($tags);
            
            $flashExpiresAt = isset($data['flash']) && $data['flash'] == true ? new \DateTime($data['flashExpiresAt']) : null;
            $post->setFlashExpiresAt($flashExpiresAt);
            
            $file = $request->request->get('file') !== null ? $request->request->get('file') : $data['file'];
            
            if ($file !== null) {
                $this->get('event_dispatcher')->dispatch(MediaEvents::COPY_FILE, new FileEvent([
                    'path' => $file,
                    'context' => MediaUtil::extractContext(Post::class),
                    'user' => $this->getUser(),
                    'closure' => function($filename) use ($post) {$post->setFile($filename);}
                ]));
            }
            
            $em->persist($post);
            $em->flush();
            
            $this->get('event_dispatcher')->dispatch(NewsEvents::NEWS_POST_CREATED, new PostEvent($post));
            
            $message = $this->get('translator')->trans('news.post.create.success', [
                '%postName%' => $post->getName()
            ], 'news');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            return $this->redirectToRoute('puzzle_admin_news_post_show', ['id' => $post->getId()]);
        }
        
        return $this->render("NewsBundle:Admin:create_post.html.twig", array(
            'form' => $form->createView()
        ));
    }
    
    
    /***
     * Update post
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatePostAction(Request $request, Post $post) {
        $form = $this->createForm(PostUpdateType::class, $post, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_news_post_update', ['id' => $post->getId()])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['puzzle_admin_news_post_update'];
            
            $tags = $post->getTags() !== null ? explode(',', $post->getTags()) : null;
            $post->setTags($tags);
            
            $flashExpiresAt = isset($data['flash']) && $data['flash'] == true ? new \DateTime($data['flashExpiresAt']) : null;
            $post->setFlashExpiresAt($flashExpiresAt);
            
            $file = $request->request->get('file') !== null ? $request->request->get('file') : $data['file'];
            
            if ($post->getFile() === null || $post->getFile() !== $file) {
                $this->get('event_dispatcher')->dispatch(MediaEvents::COPY_FILE, new FileEvent([
                    'path' => $file,
                    'context' => MediaUtil::extractContext(Post::class),
                    'user' => $this->getUser(),
                    'closure' => function($filename) use ($post) {$post->setFile($filename);}
                ]));
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $message = $this->get('translator')->trans('news.post.update.success', [
                '%postName%' => $post->getName()
            ], 'news');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            return $this->redirectToRoute('puzzle_admin_news_post_show', ['id' => $post->getId()]);
        }
        
        return $this->render("NewsBundle:Admin:update_post.html.twig", array(
            'post' => $post,
            'form' => $form->createView()
        ));
    }
    
    /**
     *
     * Update project
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updatePostGalleryAction(Request $request, Post $post) {
        $form = $this->createForm(PostUpdateGalleryType::class, $post, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_news_post_update_gallery', ['id' => $post->getId()])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['puzzle_admin_news_post_update_gallery'];
            $pictures = $request->request->get('pictures') !== null ? $request->request->get('pictures') : $data['pictures'];
            
            if ($pictures !== null) {
                $pictures = explode(',', $pictures);
                
                foreach ($pictures as $picture) {
                    $this->get('event_dispatcher')->dispatch(MediaEvents::COPY_FILE, new FileEvent([
                        'path' => $picture,
                        'context' => MediaUtil::extractContext(Post::class),
                        'user' => $this->getUser(),
                        'closure' => function($filename) use ($post) {
                            $post->addPicture($filename);
                        }
                        ]));
                }
            }
            
            if ($picturesToRemove = $request->request->get('pictures_to_remove')) {
                $picturesToRemove = explode(',', $picturesToRemove);
                foreach ($picturesToRemove as $pictureToRemove) {
                    $post->removePicture($pictureToRemove);
                }
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $message = $this->get('translator')->trans('news.post.manage_gallery.success', [
                '%postName%' => $post->getName()
            ], 'news');
            
            $this->addFlash('success', $message);
            return $this->redirectToRoute('puzzle_admin_news_post_update_gallery', ['id' => $post->getId()]);
        }
        
        return $this->render("NewsBundle:Admin:update_post_gallery.html.twig", array(
            'post' => $post,
            'form' => $form->createView()
        ));
    }
    
    /***
     * Delete Post
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletePostAction(Request $request, Post $post) {
        $message = $this->get('translator')->trans('news.post.delete.success', [
            '%postName%' => $post->getName()
        ], 'news');
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_news_post_list');
    }
    
    
    /***
     * Show Comments
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCommentsAction(Request $request, Post $post)
    {
        $comments = $this->getDoctrine()
                        ->getRepository(Comment::class)
                        ->findBy(['post' => $post->getId(), 'parentNode' => null],['createdAt' => 'DESC']);
        
        return $this->render("NewsBundle:Admin:list_comments.html.twig", array(
            'comments' => $comments,
            'post' => $post
        ));
    }
    
    /***
     * Show comment
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCommentAction(Request $request, Comment $comment)
    {
        return $this->render("NewsBundle:Admin:show_comment.html.twig", array(
            'comment' => $comment
        ));
    }
    
    /**
     * Approve Comment
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function approveCommentAction(Request $request, Comment $comment)
    {   
        $comment->setVisible(true);
        $this->get('doctrine.orm.entity_manager')->flush();
        
        $message = $this->get('translator')->trans('news.comment.approve.success', [
            '%author%' => $comment->getCreatedBy()
        ], 'news');
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirect($this->generateUrl('puzzle_admin_news_comment_list', ['id' => $comment->getPost()->getId()]));
    }
    
    /**
     * Approve Comment
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function disapproveCommentAction(Request $request, Comment $comment)
    {
        $comment->setVisible(true);
        $this->get('doctrine.orm.entity_manager')->flush();
        
        $message = $this->get('translator')->trans('news.comment.disapprove.success', [
            '%author%' => $comment->getCreatedBy()
        ], 'news');
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirect($this->generateUrl('puzzle_admin_news_comment_list', ['id' => $comment->getPost()->getId()]));
    }
    
    /***
     * Delete comment
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCommentAction(Request $request, Comment $comment)
    {
        $post = $comment->getPost();
        $message = $this->get('translator')->trans('news.comment.delete.success', [
            '%author%' => $comment->getCreatedBy()
        ], 'news');
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirect($this->generateUrl('puzzle_admin_news_comment_list', ['id' => $post->getId()]));
    }
}
