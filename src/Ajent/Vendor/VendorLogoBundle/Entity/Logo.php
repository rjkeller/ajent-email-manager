<?php
namespace Ajent\Vendor\VendorLogoBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Logo extends DatabaseModel
{
	protected function getTable()
	{
		return "logos";
	}

	public function getFields()
	{
		return array(
			"email_prefix", //integer
			"mongo_image_id", //MongoId
		);
	}
}
