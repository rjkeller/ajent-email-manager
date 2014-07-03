<?php
namespace Oranges\UserBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;

use Doctrine\ORM\Mapping as ORM;

/**
 If a user forgot his password, and requests a new one, then an email is sent
 to the user with a Request ID. This class stores the Request ID, and
 associates it with a user. So if the user clicks the link in their email
 (that stores the request ID), then they can reset their password.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="user_forgot_password_request")
*/
class ForgotPasswordRequest extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="string", length="33") */
	protected $request_id = "";

	/** @ORM\Column(type="integer") */
	protected $user_id = 0;

    public function __construct()
    {
        $this->request_id = WgTextTools::uniqueid();

        parent::__construct();
    }

	protected function getTable()
	{
		return "user_forgot_password_request";
	}
}
