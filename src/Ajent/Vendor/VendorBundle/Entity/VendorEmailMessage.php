<?php
namespace Ajent\Vendor\VendorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\Category;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class VendorEmailMessage extends EmailMessage
{
	/** @ORM\Column(type="integer") */
//	protected $vendor_id = -1;

	/**
	 Sets the vendor for this user if a vendor is detected in this email. This
	 method assumes that all the fields except for vendor_id and id are
	 already set.
	*/
	public function setVendor($user_id = null)
	{
		if (isset($this->vendor_id) && $this->vendor_id != -1 && !empty($this->vendor_id))
		{
			return true;
		}

		$emailAccount = new EmailAccount();
		$emailAccount->loadUser($this->recipient_user_id);

		//if the name and email hasn't yet been extracted from the email
		//headers, then extract that information now.
		if (empty($this->from_email))
			$this->parseFromEmail();

		//check for an existing vendor match for this email. If it exists, then
		//set that equal to the vendor and return.
		$vendor = new Vendor();
		if ($vendor->tryLoadVendor(
				$emailAccount->user_id,
				$this->from_email))
		{
			$this->vendor_id = $vendor->id;

			//yeah, i know, this is goofy, but sometimes it seems to
			//happen.
			if (!isset($vendor->category_id))
			{
				$category = new Category();
				$category->loadGeneralCategory($emailAccount->user_id);

				$vendor->category_id = $category->id;
				$vendor->save();
			}

			$this->category_id = $vendor->category_id;
			return true;
		}

		//If we reached this far, then this is a new vendor the user must've
		//subscribed to on their own, so let's add it to the vendor list.
		$email_addr = explode('@', $this->from_email);

		if (!isset($email_addr[1]))
		{
			echo "WARNING: invalid from email address: ". $this->from_email ."\n";
		}
		$vendor = new Vendor();
		$vendor->email_suffix = $this->from_email;
		if ($this->from_name == "")
			$vendor->name = $this->from_email;
		else
			$vendor->name = $this->from_name;
		$vendor->user_id = $emailAccount->user_id;
		$vendor->create();

		$this->vendor_id = $vendor->id;
		return true;
	}
}
