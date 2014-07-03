<?php
namespace Ajent\Vendor\VendorBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

/**
 * Displays a vendor logo for the specified vendor.
 */
class VendorLogoController extends RequireLoginController
{
	public function indexAction($vendor_id)
	{
		if ($vendor_id == -1)
			return new Response("/bundles/vendor/images/logos/ajent.png");
		$vendor = new Vendor();
		$vendor->load($vendor_id);
		
		$vendor_icon_file = __DIR__ .
			"/../Resources/public/images/logos/".
			$vendor->email_suffix .
			'.png';

		if (file_exists($vendor_icon_file))
		{
			return new Response(
				"/bundles/vendor/images/logos/".
				$vendor->email_suffix .
				'.png');
		}

		return new Response("/bundles/vendor/images/logos/ajent.png");
	}
}
