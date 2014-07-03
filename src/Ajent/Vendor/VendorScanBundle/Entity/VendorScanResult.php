<?php
namespace Ajent\Vendor\VendorScanBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="vendor_scan_results")
*/
class VendorScanResult extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $user_id;

	/** @ORM\Column(type="string", length="200") */
	protected $mailbox;

	/** @ORM\Column(type="integer") */
	protected $message_id;

	protected function getTable()
	{
		return "vendor_scan_results";
	}
}
