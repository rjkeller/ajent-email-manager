<?php
namespace Pixonite\BillingBundle\Helper\paypal;

use Pixonite\BillingBundle\Helper\BillingOption;

class BillCC implements BillingOption
{
	public function getName() { return "cc"; }

	public function makePayment($dollars, $billingid)
	{
		header("Location: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=$paypalEmail&item_name=$itemName&no_shipping=1&no_note=1&currency_code=USD&bn=PP%2dSubscriptionsBF&charset=UTF%2d8&a3=$price&p3=1&t3=$billingperiod&src=1&sra=1");
		die();
	}
}

?>
