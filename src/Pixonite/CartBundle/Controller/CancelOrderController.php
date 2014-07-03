<?php
namespace Pixonite\CartBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\frontend\WgSmarty;

use Pixonite\CartBundle\Helper\Cart;

class CancelOrderController extends RequireLoginController
{
	public function runAction(PageRequest $request)
	{
		Cart::emptyCart();

		return $this->render('CartBundle:pages:cancelOrder.twig.html',
			array());
	}
}
