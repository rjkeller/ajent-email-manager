<?php
namespace Oranges\FormsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;

use Oranges\FormsBundle\Helper\CidManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FormCodeTestController extends RequireLoginController
{
	/**
	 * @Route("/form_code_test", name="SBEP")
	 */
	public function indexAction()
	{
		if (!isset($_POST['cid']))
			$_POST['cid'] = "";
		echo "<pre>";
		echo "POST: ". $_POST['cid'] ."\n";
		echo "is TEST submitted?\n";
		echo CidManager::isCidValid("TEST");
		echo "\n";
		echo "<form method=\"POST\">";
		$code = CidManager::getFormCidCode("TEST");

		echo "<input type=\"hidden\" name=\"cid\" value=\"". $code ."\">";
		echo "CID: ". $code ."\n";
		echo "<input type=\"submit\">";
		echo "</form>";
		die();
	}
}
