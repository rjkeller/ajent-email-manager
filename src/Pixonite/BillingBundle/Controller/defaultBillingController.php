<?php
namespace Pixonite\BillingBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\User;

use Oranges\forms\WgForm;
use Oranges\forms\StdTypes;

use Oranges\sql\SqlUtility;
use Oranges\sql\WgDbh;

use Oranges\gui\MessageBoxHandler;
use Oranges\frontend\WgSmarty;

class defaultBillingController extends RequireLoginController
{
	public function indexAction()
	{
		if (!User::$permissions->enableCcBilling)
		{
			$smarty = WgSmarty::getInstance();
			return $this->createResponse($smarty->fetch("billing/contact_support.smarty"));		
		}


		$contactHandler = " onblur=\"changecolor('#FFF', 'contact')\" onfocus=\"changecolor('#ddeaff', 'contact')\"";
		$billingHandler = " onblur=\"changecolor('#FFF', 'billing')\" onfocus=\"changecolor('#ddeaff', 'billing')\"";
		$cardHandler = " onblur=\"changecolor('#FFF', 'card')\" onfocus=\"changecolor('#ddeaff', 'card')\"";

		$frm = new WgForm("modprofile");
		$frm->fillWithArray(SqlUtility::getArray("contact", User::$userinfo->contactid));
		$frm->addField("fname", "First Name", new StdTypes("str"), null, array("extra" => $contactHandler));
		$frm->addField("lname", "Last Name", new StdTypes("str"), null, array("extra" => $contactHandler));
		$frm->addField("address1", "Address", new StdTypes("str"), null, array("extra" => $billingHandler));
		$frm->addField("city", "City", new StdTypes("str"), null, array("extra" => $billingHandler));
		$frm->addField("state", "State", new StdTypes("str"), null, array("extra" => $billingHandler));
		$frm->addField("address2", "Address (line 2)", new StdTypes("str"), null, array("optional" => true, "extra" => $billingHandler));
		$frm->addField("zip", "Postal Code", new StdTypes("str"), null, array("extra" => $billingHandler));
		$frm->addField("country", "Country", new StdTypes("country"), null, array("extra" => $billingHandler));

		$frm->addField("num", "Credit Card Number", new StdTypes("int"), null, array("extra" => $cardHandler ." style=\"width:140px;\""));

		//5 years from now

		$date = date("y");
		$vals = array($date => $date);
		for ($i = 1; $i <= 5; $i++)
			$vals[$date+$i] = $date+$i;
		$frm->addField("exp-yr", "Credit Card Expiration Year", new StdTypes("list"), null,
			array("vals" => $vals, "extra" => $cardHandler));
		$vals = array("01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05",
			"06" => "06", "07" => "07", "08" => "08", "09" => "09", 10 => 10, 11 => 11, 12 => 12);
		$frm->addField("exp-mo", "Credit Card Expiration Month", new StdTypes("list"), null,
			array("vals" => $vals, "extra" => $cardHandler));

		$vals = array("Visa" => "visa", "MasterCard" => "mastercard");
		$vals['-Type-'] = "";
		$frm->addField("cardtype", "Credit Card Type", new StdTypes("list"), null,
		    array("vals" => $vals, "extra" => $cardHandler));


		$contact = null;
		$billing = null;
		if ($frm->onsubmit())
		{
		  $billing  = new SqlTable("billing_cc");
		  $billing->number = $_POST['num'];
		  $billing->expirationDate = $_POST['exp-mo'].$_POST['exp-yr'];
		  $billing->numberOnBack = $_POST['nob'];
		  $billing->uid = User::$id;
		  $billing->cardtype = $_POST['cardtype'];

		  //update contact
		  $contact = new SqlTable("contact");
		  $contact->uid = User::$id;

		  $contact->fname = $_POST['fname'];
		  $contact->lname = $_POST['lname'];
		  $contact->address1 = $_POST['address1'];
		  $contact->address2 = $_POST['address2'];
		  $contact->city = $_POST['city'];
		  $contact->state = $_POST['state'];
		  $contact->country = $_POST['country'];
		  $contact->zip = $_POST['zip'];
		  $contact->tableInsert();

		  $billing->contact = $contact->id;
		  $billing->tableInsert();

		  $billId = User::$userinfo->defaultBilling;
		  WgDbh::query("DELETE FROM billing_cc WHERE id = '$billId'");
		  WgDbh::query("UPDATE user SET defaultBilling = '$billing->id' WHERE id = '". User::$id ."' LIMIT 1");
		  User::$userinfo->defaultBilling = $billing->id;

		  if ($_GET['checkout'] == 1)
		  {
			  	header("Location: /purchase/checkout");
			  	die();
		  	}
		  MessageBoxHandler::happy("Your billing information has been successfully updated.");
		}

		$smarty = $request->smarty;
		return $this->createResponse($smarty->fetch("billing/default_billing.smarty"));		
	}
}
