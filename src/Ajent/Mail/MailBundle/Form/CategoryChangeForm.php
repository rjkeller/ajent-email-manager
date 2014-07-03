<?php
namespace Ajent\Mail\MailBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;
use Oranges\MasterContainer;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use AjentApps\Social\SocialPostsBundle\Entity\FavoriteSite;

use Oranges\MongoDbBundle\Helper\MongoDb;

class CategoryChangeForm extends WgForm
{
	/** @Assert\Type("string") */
	public $id;

	/** @Assert\Type("string") */
	public $message_id;

	/** @Assert\Type("string") */
	public $move_all_emails;

    /**
     * @Assert\True(message = "Invalid Input")
     */
	public function isMoveEmailsOptionValid()
	{
		return $this->move_all_emails == "no" ||
			$this->move_all_emails == "yes";
	}

	public function getName()
	{
		return "CategoryChange";
	}

	public function submitForm()
	{
	    $category = new Category();
	    $category->load($this->id);

		$message = new VendorEmailMessage();
		$message->load($this->message_id);
		$message->category_id = $category->_id;

		$message->save();

		if ($this->move_all_emails == "yes")
		{
			$db = MongoDb::getDatabase();
			$q = MongoDb::modelQuery($db->email_messages
					->find(array("vendor_id" => $message->vendor_id))
				, "Ajent\Mail\MailBundle\Entity\EmailMessage");

			$vendor = new Vendor();
			$vendor->load($message->vendor_id);
			$vendor->category_id = $this->id;
			$vendor->save();

			foreach ($q as $msg)
			{
				$msg->category_id = $category->_id;
				$msg->save();
			}
		}

		$router = MasterContainer::get("router");
		$url = $router->generate(
			'VendorBundleViewCategoryVendor',
			array('category_name' => $category->name,
			 	'vendor_id' => $message->vendor_id ));

        header("Location: ". $url . "?message_id=". $this->message_id);
        die();
	}
}