<?php
namespace Oranges\FormsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;

use Oranges\FormsBundle\Helper\CidManager;

class PrintCidController extends RequireLoginController
{
	public function indexAction($form_name)
	{
		return new Response("<input type=\"hidden\" name=\"cid\" value=\"". CidManager::getFormCidCode($form_name) ."\">");
	}
}
