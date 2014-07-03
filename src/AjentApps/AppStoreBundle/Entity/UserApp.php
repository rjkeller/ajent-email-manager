<?php
namespace AjentApps\AppStoreBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

use Pixonite\CartBundle\Entity\ProductType;

/**
 This represents an application that can be downloaded from the app store.

 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="app_store_user_apps")
*/
class UserApp extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $app_id;

	/** @ORM\Column(type="integer") */
	protected $user_id;

	/** @ORM\Column(type="boolean") */
	protected $is_enabled = true;


	protected function getTable()
	{
		return "app_store_user_apps";
	}
}
