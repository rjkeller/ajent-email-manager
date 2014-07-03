<?php
namespace Ajent\Vendor\VendorLogoBundle\Helper;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\MongoDbBundle\Helper\FileUploadManager;
use Oranges\MasterContainer;

use Ajent\Vendor\VendorLogoBundle\Entity\Logo;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class LogoManager extends DatabaseModel
{
	/**
	 Checks if a logo exists. If so, returns the URL to that image. If the logo
	 does not exist, then it returns the URL of the generic Ajent logo.
	*/
	public function getLogo($id)
	{
		$logo = new Logo();
		$isSuccessful = $logo->tryLoadId($id);

		if ($isSuccessful)
		{
			$router = MasterContainer::get("router");
			return $router->generate(
				'LogoBundleViewLogo',
				array('mongo_image_id' => $logo->mongo_image_id ));
		}
		else
			return "/bundles/logomanager/images/generic.jpg";
	}

	/**
	 If a new vendor is being created, you can use this method to try and check
	 the vendor's website for a logo. Or we can use this to check our internal
	 database for a logo. If both fail, then this method returns -1. If we
	 were successful in finding a logo, this returns the I
	*/
	public function loadLogo(Vendor $vendor)
	{
		//check if we already have a logo for this vendor.
		$logo = new Logo();
		$isSuccessful = $logo->tryLoadEmailPrefix($email_prefix);
		if ($isSuccessful)
			return $logo->id;

		//check the vendor's website for an apple iPad logo. These are generally the highest resolution.
		$metadata = array();
		FileUploadManager::storeFile($file, "LogoBundleLogos", $metadata);
		
		//if that fails, try to find a favicon.ico. It's not exactly optimal
		//and the resolution will suck, but it's better than using our generic
		//logo.

		//if all else fails, then we won't be successful in finding the logo,
		//so return -1 so the generic logo will be displayed.
		return -1;
	}
}
