<?php
namespace AjentApps\AppStoreBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

use Pixonite\CartBundle\Entity\ProductType;

/**
 This represents an application that can be downloaded from the app store.

 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="app_store_apps")
*/
class App extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="string") */
	protected $name;

	/** @ORM\Column(type="decimal") */
	protected $price;

	/** @ORM\Column(type="string", length="500") */
	protected $description;

	/** @ORM\Column(type="string", length="100") */
	protected $image;

	/** @ORM\Column(type="boolean") */
	protected $is_enabled = true;


	protected function getTable()
	{
		return "app_store_apps";
	}

	public function create()
	{
		parent::create();
		
		//if we're successful creating this object, then create a
		//corresponding ProductType object to go with it, so we can purchase
		//it using the CartBundle system.
		$pt = new ProductType();
		$pt->name = $this->id;
		$pt->type = "app";
		$pt->price = $this->price;
		$pt->status = "ok";
		$pt->is_displayed = $this->is_enabled;
		$pt->create();
	}

	public function save()
	{
		parent::save();
		
		$pt = new ProductType();
		$pt->loadType($this->id, "app");
		$pt->price = $this->price;
		$pt->is_displayed = $this->is_enabled;
		$pt->save();
	}

	public function delete()
	{
		parent::delete();
		
		$pt = new ProductType();
		$pt->loadType($this->id, "app");
		$pt->delete();
	}

	/**
	 Whether or not this application is free to download.
	*/
	public function isFree()
	{
		return $this->price == 0;
	}
}
