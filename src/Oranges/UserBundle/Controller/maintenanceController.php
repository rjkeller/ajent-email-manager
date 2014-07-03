<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class maintenanceController extends Controller
{
	public function indexAction()
	{
		return $request->render("UserBundle:pages:maintenance.twig.html", array());
	}
}
