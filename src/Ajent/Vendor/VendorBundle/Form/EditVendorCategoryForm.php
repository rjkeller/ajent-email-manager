<?php
namespace Ajent\Vendor\VendorBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\sql\Database;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\InternalException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

class EditVendorCategoryForm extends WgForm
{
	/** @Assert\Type("numeric") */
	public $id;

	/** @Assert\Type("string") */
	public $category_select;

	/** @Assert\Type("string") */
	public $new_vendor_name;

	public function getName()
	{
		return "EditVendorCategory";
	}

	public function submitForm()
	{
		$vendor = new Vendor();
		$vendor->load($this->id);

		$old_category_id = $vendor->category_id;

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

		//check if the old category is now abandoned. If so, then delete it.
		$numVendorsWithCategory = Database::scalarQuery("
			SELECT
				COUNT(*)
			FROM
				vendors
			WHERE
				category_id = '". $old_category_id ."'
			LIMIT
				1
			");
		if ($numVendorsWithCategory <= 0)
		{
			$category = new Category();
			$category->load($old_category_id);
			$category->delete();
		}
	}
}
