<?php
namespace AjentApps\Ajent\AjentHomePageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StaticPageController extends Controller
{
	public function ViewPageAction($page)
	{
		return $this->render("AjentHomePageBundle:pages:". $page .".twig.html",
			array());
	}
}
