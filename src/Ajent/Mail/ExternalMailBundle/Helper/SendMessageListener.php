<?php
namespace Ajent\Mail\ExternalMailBundle\Helper;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
interface SendMessageListener
{
	/**
	 Returns true if message data should be passed to the parseMessage()
	 function.
	*/
	public function isParsable($heaaders, $fromAddress);
	public function parseMessage($subject, $fromAddress, $body);
	/**
	 Returns true if we should stop scanning for more vendors.
	*/
	public function isVendorCapHit();
}