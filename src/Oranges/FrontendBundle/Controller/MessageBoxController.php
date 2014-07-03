<?php
namespace Oranges\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;

class MessageBoxController extends Controller
{
	public function printErrorBoxesAction()
	{
    	return $this->render("FrontendBundle:pages:MessageBox.twig.html",
	    	MessageBoxHandler::getTemplateVars());
	}
}
