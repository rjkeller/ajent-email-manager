<?php
namespace Pixonite\CartBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;

class ViewProductsController extends RequireLoginController
{
	public function indexAction()
	{
		$q = Database::modelQuery("
			SELECT
				*
			FROM
				product_type
			",
			"Pixonite\CartBundle\Entity\ProductType"
		);

		return $this->render('CartBundle:pages:ViewProducts.twig.html',
			array("products" => $q));
	}
}
