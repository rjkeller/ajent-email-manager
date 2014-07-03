<?php
namespace AjentApps\Ajent\Widgets\CategoryWidgetBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Mail\MailBundle\Entity\Category;

use AjentApps\Ajent\Widgets\CategoryWidgetBundle\Form\AddCategoryForm;
use AjentApps\Ajent\Widgets\CategoryWidgetBundle\Form\EditCategoryForm;
use AjentApps\Ajent\Widgets\CategoryWidgetBundle\Form\DeleteCategoryForm;

class IndexController extends RequireLoginController
{
	public function indexAction()
	{
        new EditCategoryForm();
        new DeleteCategoryForm();
        new AddCategoryForm();

		$db = MongoDb::getDatabase();

		$categories = MongoDb::modelQuery($db->email_categories->find(array(
				"user_id" => SessionManager::$user->id,
				))
				->sort(array(
					"num_new_messages" => -1,
					"num_messages" => -1,
					"name" => 1
					))
				->limit(7),
			"Ajent\Mail\MailBundle\Entity\Category");

		$template_vars['categories'] = $categories;
		$template_vars['controller'] = $this;
		$template_vars['print_more_button'] = $categories->count() >= 6;

		return $this->render('CategoryWidgetBundle:pages:Index.twig.html',
			$template_vars);
	}


	public function getVendors(Category $category)
	{
		$template_vars = array();

		$db = MongoDb::getDatabase();
		return MongoDb::modelQuery($db->vendor_categories->find(array(
				"category_id" => $category->id,
				'is_invisible' => false
				))
			->sort(array(
				"num_new_messages" => -1,
				"num_messages" => -1
				)),
			"Ajent\Vendor\VendorBundle\Entity\VendorCategory");
	}
}

