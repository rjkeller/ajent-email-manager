<?php
namespace Pixonite\BillingBundle\Controller;

use Pixonite\BillingBundle\Entity\Invoice;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\frontend\WgSmarty;
use Oranges\errorHandling\ForceError;
use Oranges\sql\WgDbh;
use Oranges\sql\SqlUtility;
use Oranges\forms\FormCoder;

class invoiceController extends RequireLoginController
{
	public function indexAction($invoiceid)
	{
		$template_vars = $this->loadInvoice($invoiceid);
		return $this->render('BillingBundle:pages:invoice.twig.html',
			$template_vars);
	}

	public function PrintViewAction($invoiceid)
	{
		$template_vars = $this->loadInvoice($invoiceid);
		return $this->render('BillingBundle:pages:invoicePrintView.twig.html',
			$template_vars);
	}

	public function loadInvoice($invoiceid)
	{
		//set active tab equal to billing
		$active = "billing";

		ForceError::$inst->checkId($invoiceid);

		$invoice = new Invoice();
		$invoice->load($invoiceid);

		if (isset($_POST['autorenew']) && $_POST['autorenew'] == "true")
		{
			$product = new Product();
			$product->load($this->productid);

			$product->enableAutorenew = true;
			$product->save();
		}
		else if (isset($_POST['autorenew']) && $_POST['autorenew'] == "false")
		{
			$product = new Product();
			$product->load($this->productid);

			$product->enableAutorenew = false;
			$product->save();
		}

        return array("invoice" => $invoice);
	}
}
