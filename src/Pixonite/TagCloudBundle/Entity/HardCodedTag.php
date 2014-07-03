<?php
namespace Pixonite\TagCloudBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="tags_hardcoded")
*/
class HardCodedTag extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/**
	Inflates the number of posts containing this tag by a certain number.
	@ORM\Column(type="integer")
	*/
	protected $inflate_num = "";

	/** @ORM\Column(type="string") */
	protected $keyword = "";

	protected function getTable()
	{
		return "tags_hardcoded";
	}

	public function loadKeyword($keyword)
	{
		return $this->loadQuery("keyword = '$keyword'");
	}
}
