<?php
namespace AjentApps\AppStoreBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="app_store_purchase_app_prep")
*/
class PurchaseAppPrep extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $app_id;

	protected function getTable()
	{
		return "app_store_purchase_app_prep";
	}
}
