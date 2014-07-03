<?php
namespace Pixonite\CartBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="reseller")
*/
class Reseller extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	protected function getTable()
	{
		return "reseller";
	}

    public static function getInstance()
    {
        $reseller = new Reseller();
        $reseller->id = 0;
        return $reseller;
    }
}
