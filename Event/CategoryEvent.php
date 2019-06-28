<?php 

namespace Puzzle\NewsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Puzzle\NewsBundle\Entity\Category;

class CategoryEvent extends Event
{
	/**
	 * @var Category
	 */
	private $category;
	
	/**
	 * @var array
	 */
	private $data;
	
	public function __construct(Category $category, array $data = null){
		$this->category= $category;
		$this->data = $data;
	}
	
	public function getCategory(){
		return $this->category;
	}
	
	public function getData(){
	    return $this->data;
	}
	
}

?>