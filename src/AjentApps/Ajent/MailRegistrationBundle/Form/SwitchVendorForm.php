<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\InternalException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

class SwitchVendorForm extends WgForm
{
	/** @Assert\Type("string") */
	public $id;

	/** @Assert\Type("string") */
	public $category_select;

	/** @Assert\Type("string") */
	public $new_vendor_name;

	public function getName()
	{
		return "SwitchToAjent";
	}

	public function submitForm()
	{
		$vendor = new Vendor();
		$vendor->load($this->id);
		$vendor->pendingAddToAjent = true;
		$vendor->is_unsubscribed = false;
		$vendor->is_invisible = false;

		\OrangesLogger("Vendor Switch: ". $vendor->name, "switch",
			array("form" => array(
				"id" => $this->id,
				"category_select" => $this->category_select,
				"new_vendor_name" => $this->new_vendor_name)));

		if (!empty($this->new_vendor_name))
		{
			$category = new Category();

			//search if another category with this name exists. If so, then use
			//the existing category.
			if ($category->tryLoadCategoryName($this->new_vendor_name))
			{
				$vendor->category_id = $this->category_select;
			}
			else
			{
				$category->user_id = SessionManager::$user->id;
				$category->name = $this->new_vendor_name;
				$category->create();
			
				$vendor->category_id = $category->id;
			}
		}
        else if (!empty($this->category_select))
        {
            $vendor->category_id = $this->category_select;
        }
		$vendor->save();
	}
}
