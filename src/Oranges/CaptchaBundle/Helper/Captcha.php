<?php
namespace Oranges\CaptchaBundle\Helper;

require_once(__DIR__ .'/recaptchalib.php');

use Oranges\framework\BuildOptions;

/**
 An OO interface to the Google ReCaptcha system.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Captcha
{
	/**
	 The ReCaptcha private key. This is automatically loaded from BuildOptions,
	 so only set this if you don't want to use BuildOptions.
	*/
	public $privateKey;

	/**
	 The ReCaptcha private key. This is automatically loaded from BuildOptions,
	 so only set this if you don't want to use BuildOptions.
	*/
	public $publicKey;

	/**
	 If an error occured, then it is printed in this variable.
	*/
	public $error = null;

	public function __construct()
	{
		if (isset(BuildOptions::$get['captcha_private_key']))
			$this->privateKey = BuildOptions::$get['captcha_private_key'];

		if (isset(BuildOptions::$get['captcha_public_key']))
			$this->publicKey = BuildOptions::$get['captcha_public_key'];
	}

	/**
	 Was the catchum successfully verified as entered correctly. If not, error
	 is stored in $this->error.
	
	 If the form was not submitted, this will return false, and $this->error
	 will be null.
	*/
	public function isValid()
	{
		if (!isset($_POST["recaptcha_challenge_field"]))
			return false;

		$resp = \recaptcha_check_answer($this->privateKey,
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]);
		
		$isValid = $resp->is_valid;

		if (!$isValid)
			$this->error = $resp->error;

		return $isValid;
	}

	public function getCaptchaHtml()
	{
		return \recaptcha_get_html($this->publicKey, $this->error);
	}
}
