<?php
namespace Pixonite\TagCloudBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="tags_related")
*/
class RelatedTag extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="string", length="33") */
	protected $user_id = "";

	/** @ORM\Column(type="string", length="33") */
	protected $product_id = "";

	/** @ORM\Column(type="string") */
	protected $keyword = "";

	/** @ORM\Column(type="integer") */
	protected $num = "";

	protected function getTable()
	{
		return "tags_related";
	}

    public function tryLoad($keyword, $product_id)
    {
        return $this->loadQuery("keyword = '". $keyword ."' AND product_id = '". $product_id ."'", true);
    }
}
