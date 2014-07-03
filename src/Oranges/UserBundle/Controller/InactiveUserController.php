<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InactiveUserController
{
	public function runAction(PageRequest $request)
	{
		return $request->render("UserBundle:pages:inactive.twig.html", array());
	}
}
