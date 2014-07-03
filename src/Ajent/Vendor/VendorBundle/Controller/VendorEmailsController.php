<?php
namespace Ajent\Vendor\VendorBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\sql\Database;

use Ajent\Mail\MailBundle\Form\AddCategoryForm;
use Ajent\Mail\MailBundle\Form\EditCategoryForm;
use Ajent\Mail\MailBundle\Form\DeleteCategoryForm;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Controller\MailManagerController;
use Ajent\Mail\MailBundle\Query\EmailSpec;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

class VendorEmailsController extends MailManagerController
{

	public function indexAction($vendor_id)
	{
		$v = new Vendor();
		$v->load($vendor_id);

		$category = new Category();
		$category->load($v->category_id);

		return $this->showMessages(
			$category->name,
			"Vendor: ". $v->name,
			new EmailSpec("inbox", null, $v->id)
		);
	}

	public function viewCategoryVendorAction($vendor_id, $category_name)
	{
		$v = new Vendor();
		$v->load($vendor_id);

		$category = new Category();
		$category->loadCategoryName($category_name);

		return $this->showMessages(
			$category->name,
			$category->name,
			new EmailSpec("inbox", $category->id, $v->id)
		);
		
	}
}

