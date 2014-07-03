<?php
namespace Oranges\UserBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\SessionManager;

use Doctrine\ORM\Mapping as ORM;

/**
 Permissions of each user in the system.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="permissions")
*/
class Permissions extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="string") */
	protected $name = "";

	/** @ORM\Column(type="boolean") */
	protected $is_active = false;

	/** @ORM\Column(type="boolean") */
	protected $admin_access = false;

	protected function getTable()
	{
		return "permissions";
	}

    public function getUserId()
    {
        if ($this->admin_access)
            return "!= -1";
        else
            return "= '". SessionManager::$user->id ."'";
    }

    public function loadRole($role)
    {
        $this->loadQuery("name = '". $role ."'");
    }
}
