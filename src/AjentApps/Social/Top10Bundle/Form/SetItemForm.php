<?php
namespace AjentApps\Social\Top10Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Social\Top10Bundle\Entity\ListItem;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class SetItemForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $category_id;

    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $slot_num;

    /**
     */
	public $previous_vendor_id;

    /**
     */
	public $vendor_name;

    /**
     */
	public $vendor_website;

	public function getName()
	{
		return "SetItem";
	}

	public function submitForm()
	{
		//try to load item
		$item = new ListItem();
		$isLoaded = $item->tryLoadItem($this->category_id, $this->slot_num);

		if (!empty($this->vendor_name) &&
			!empty($this->vendor_website))
		{
			$vendor = new Vendor();
			$vendor->user_id = SessionManager::$user->id;
			$vendor->email_suffix = $this->vendor_website;
			$vendor->name = $this->vendor_name;
			$vendor->category_id = $this->category_id;
			$vendor->create();

			$item->vendor_id = $vendor->id;
		}
		else if (!empty($this->previous_vendor_id))
		{
			$item->vendor_id = $this->previous_vendor_id;			
		}

		if ($isLoaded)
			$item->save();
		else
		{
			$item->category_id = $this->category_id;
			$item->slot_num = $this->slot_num;
			$item->user_id = SessionManager::$user->id;
			$item->create();
		}

		$db = MongoDb::getDatabase();

		$items = MongoDb::modelQuery(
			$db->list_items->find(array(
				"category_id" => $this->category_id))
				->sort(array(
					'slot_num' => 1
				)),
			"AjentApps\Social\Top10Bundle\Entity\ListItem");

		$i = 0;
		foreach ($items as $item)
		{
			$item->slot_num = ++$i;
			$item->save();
		}

	}
}
