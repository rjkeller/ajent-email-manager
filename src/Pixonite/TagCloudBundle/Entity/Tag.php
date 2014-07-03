<?php
namespace Pixonite\TagCloudBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="tags")
*/
class Tag extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="integer") */
	protected $num = "";

	/** @ORM\Column(type="integer") */
	protected $user_id = -1;

	/** @ORM\Column(type="string") */
	protected $keyword = "";

	protected function getTable()
	{
		return "tags";
	}

	public function loadKeyword($keyword)
	{
		return $this->loadQuery("keyword = '". $keyword ."'");
	}

    public function tryLoadKeyword($keyword)
    {
        return $this->loadQuery("keyword = '". $keyword ."'", true);
    }
}
