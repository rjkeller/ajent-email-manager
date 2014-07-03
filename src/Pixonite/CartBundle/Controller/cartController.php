<?php
namespace Pixonite\CartBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\frontend\WgSmarty;
use Oranges\sql\Database;
use Oranges\sql\SqlIterator;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\ForceError;

use Pixonite\CartBundle\Helper\Cart;

class cartController extends RequireLoginController
{
	public function indexAction()
	{
		$vars = $this->getTemplateVars();
		$vars['printTableHeader'] = true;
		return $this->render('CartBundle:pages:cart.twig.html', $vars);
	}

	public function ajaxRemoveCartItemAction($cart_entry_id)
	{
		ForceError::$inst->checkId($cart_entry_id);

		$cart = $this->get("pixonite.cart");
		$cart->removeFromCart($cart_entry_id);

		return $this->render('CartBundle:ajax:cart.twig.html',
			$this->getTemplateVars());
	}

	public function ajaxCancelOrderAction()
	{
		$cart = $this->get("pixonite.cart");
		$cart->emptyCart();

		return $this->render('CartBundle:ajax:cart.twig.html',
			$this->getTemplateVars());
	}

	public function getTemplateVars()
	{
		$data = array();
		$total = 0.0;

		if (isset($_GET['did']))
		{
			if ($_GET['did'] == "e" || $_GET['did'] == "rmve")
			{
				Cart::emptyCart();
			}
			else
			{
				ForceError::$inst->checkId($_GET['did']);

				$cart = $this->get("pixonite.cart");
				$cart->removeFromCart($_GET['did']);
			}
		}

		$q = Database::modelQuery("
			SELECT
				*
			FROM
				cart
			WHERE
				user_id = '". SessionManager::$user->id ."'
		",
		"Pixonite\CartBundle\Entity\CartEntry");

		$template_vars = array();
		$template_vars["cartItems"] = $q;
		$template_vars["controller"] = $this;
		$template_vars['printTableHeader'] = false;

		$total = Database::scalarQuery("
			SELECT
				SUM(price)
			FROM
				cart
			WHERE
				user_id = '". SessionManager::$user->id ."'
		");
		$template_vars['total'] = number_format($total, 2, '.', ',');
		
		return $template_vars;
	}

	public function parseCartItem($data)
	{
	}

	public function getTotal()
	{
		return number_format($this->total, 2, '.', ',');
	}
}
