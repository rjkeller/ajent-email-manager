<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;

use Ajent\AjentBundle\Query\VendorSearch;

class FindVendorController extends RequireLoginController
{
	public function indexAction()
	{
		$template_vars = array();

        $search = new VendorSearch();

        $template_vars['vendors'] = $search->getSqlQuery();
        $template_vars['searchResults'] = $search;

		return $this->render("MailRegistrationBundle:pages:FindVendor.twig.html",
			$template_vars);
	}
}
