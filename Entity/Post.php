<?php
namespace Puzzle\NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Puzzle\AdminBundle\Traits\Describable;
use Puzzle\MediaBundle\Traits\Pictureable;
use Puzzle\AdminBundle\Traits\Nameable;
use Puzzle\UserBundle\Traits\PrimaryKeyTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;

/**
 * News Post
 * 
 * @author qwincy <qwincypercy@fermentuse.com>
 *
 * @ORM\Table(name="puzzle_news_post")
 * @ORM\Entity(repositoryClass="Puzzle\NewsBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Post
{
    use Timestampable, Sluggable, Blameable;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @var string
     */
    private $name;
    
    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @var string
     */
    private $description;
    
    /**
     * @ORM\Column(name="short_description", type="string", length=255, nullable=true)
     * @var string
     */
    private $shortDescription;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @ORM\Column(name="tags", type="array", nullable=true)
     * @var array
     */
    private $tags;
    
    /**
     * @ORM\Column(name="enable_comments", type="boolean")
     * @var boolean
     */
    private $enableComments;
    
    /**
     * @ORM\Column(name="visible", type="boolean")
     * @var boolean
     */
    private $visible;
    
    /**
     * @ORM\Column(name="flash", type="boolean")
     * @var boolean
     */
    private $flash;
    
    /**
     * @ORM\Column(name="flash_expires_at", type="datetime", nullable=true)
     * @var \DateTime
     */
    private $flashExpiresAt;
    
    /**
     * @ORM\Column(name="pictures", type="simple_array", nullable=true)
     * @var array
     */
    private $pictures;
   
    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="posts")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;
    
    /**
     * @ORM\ManyToOne(targetEntity="Archive", inversedBy="posts")
     * @ORM\JoinColumn(name="archive_id", referencedColumnName="id")
     */
    private $archive;
    
    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post")
     */
    private $comments;
    
    /**
     * Constructor
     */
    public function __construct() {
    	$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->visible = true;
    }
    
    public function getSluggableFields() {
        return ['name'];
    }
    
    public function getId() :?int {
        return $this->id;
    }
    
    public function setName($name) :self {
        $this->name = $name;
        return $this;
    }
    
    public function getName() :?string {
        return $this->name;
    }
    
    public function setDescription($description) :self {
        $this->description = $description;
        return $this;
    }
    
    public function getDescription() :?string {
        return $this->description;
    }
    
    public function setShortDescription($shortDescription) :self {
        $this->shortDescription = $shortDescription;
        return $this;
    }
    
    public function getShortDescription() :? string {
        return $this->shortDescription;
    }

    public function setTags($tags) : self {
        $this->tags = $tags;
        return $this;
    }
   
    public function addTag($tag) : self {
        $this->tags = array_unique(array_merge($this->tags, [$tag]));
    	return $this;
    }
 
    public function removeTag($tag) : self {
    	$this->tags = array_diff($this->tags, [$tag]);
    	return $this;
    }

    public function getTags() {
        return $this->tags;
    }

    public function setEnableComments($enableComments) : self {
        $this->enableComments = $enableComments;
        return $this;
    }

    public function getEnableComments() :? bool {
        return $this->enableComments;
    }
    
    public function setVisible(bool $visible) : self {
        $this->visible = $visible;
        return $this;
    }
    
    public function isVisible() :? bool {
        return $this->visible;
    }
    
    public function setFlash(bool $flash) : self {
        $this->flash = $flash;
        return $this;
    }
    
    public function isFlash() :? bool {
        return $this->flash;
    }
    
    public function setFlashExpiresAt(\DateTime $flashExpiresAt = null) : self {
        $this->flashExpiresAt = $flashExpiresAt;
        return $this;
    }
    
    public function getFlashExpiresAt() :? \DateTime {
        return $this->flashExpiresAt;
    }
    
    public function setFile($file) :self {
        $this->file = $file;
        return $this;
    }
    
    public function getFile() :?string {
        return $this->file;
    }
    
    public function setPictures($pictures) :self {
        $this->pictures = $pictures;
        return $this;
    }
    
    public function addPicture($picture) :self {
        $this->pictures = array_unique(array_merge($this->pictures, [$picture]));
        return $this;
    }
    
    public function removePicture($picture) :self {
        $this->pictures = array_diff($this->pictures, [$picture]);
        return $this;
    }
    
    public function getPictures() {
        return $this->pictures;
    }
    
    public function setCategory(Category $category) : self {
        $this->category = $category;
        return $this;
    }

    public function getCategory() :? Category {
        return $this->category;
    }
    
    public function setArchive(Archive $archive) : self {
        $this->archive = $archive;
        return $this;
    }
    
    public function getArchive() :? Archive {
        return $this->archive;
    }

    public function addComment(Comment $comment) : self {
        if ($this->comments === null || $this->comments->contains($comment) === false) {
            $this->comments->add($comment);
        }
        
        return $this;
    }

    public function removeComment(Comment $comment) : self {
        $this->comments->removeElement($comment);
    }

    public function getComments() :? Collection {
        return $this->comments;
    }
    
    public function __toString(){
        return $this->getName();
    }
}
