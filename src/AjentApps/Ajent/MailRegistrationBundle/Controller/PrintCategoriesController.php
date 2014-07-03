<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Ajent\Mail\MailBundle\Entity\Category;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\sql\Database;

class PrintCategoriesController extends RequireLoginController
{
	private static $categories = null;

	public function indexAction()
	{
		return $this->setCategoryAction(-1);
	}

	public function setCategoryAction($category_id)
	{
		if (self::$categories == null)
		{
			$db = MongoDb::getDatabase();
			$allCategories = MongoDb::modelQuery(
				$db->email_categories->find(array(
					"user_id" => SessionManager::$user->id,
					"name" => array('$ne' => 'Miscellaneous')
				))->sort(array("name" => 1)),
				"Ajent\Mail\MailBundle\Entity\Category");
			self::$categories = $allCategories;
		}

		$response = "";
		foreach (self::$categories as $c)
		{
			if ($c->id == $category_id)
				$response .= "<option value=\"". $c->id ."\" selected>". $c->name ."</option>\n";
			else
				$response .= "<option value=\"". $c->id ."\">". $c->name ."</option>\n";
		}
		$c = new Category();
		$c->loadGeneralCategory();
		$response .= "<option value=\"". $c->id ."\">". $c->name ."</option>\n";

		return new Response($response);
	}

}
