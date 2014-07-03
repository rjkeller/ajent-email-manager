<?php
namespace Pixonite\BillingBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Oranges\UserBundle\Helper\RequireLoginController;
use Pixonite\BillingBundle\Entity\UserFunds;

class AccountBalanceController extends RequireLoginController
{
	public function indexAction()
	{
		$balance = UserFunds::getInstance();
		$balance = number_format($balance->getBalance(), 2, '.', ',');

		return new Response("$". $balance);
	}
}
