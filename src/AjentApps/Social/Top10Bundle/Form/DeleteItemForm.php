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

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class DeleteItemForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $category_id;

    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $slot_num;

	public function getName()
	{
		return "DeleteItemForm";
	}

	public function submitForm()
	{
		$listItem = new ListItem();
		$isLoaded = $listItem->tryLoadItem($this->category_id, $this->slot_num);
		if ($isLoaded)
		{
			$listItem->delete();

			//reload slot numbers for other entries.
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
}
