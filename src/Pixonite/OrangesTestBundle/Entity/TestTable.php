<?php
namespace Pixonite\OrangesTestBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="test_dbmodel")
*/
class TestTable extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="string", length="700") */
	protected $col;


	public function __construct()
	{
		parent::__construct();

		$this->__key = md5("encrypt me!");
		$this->__encrypt['col'] = true;
	}

	protected function getTable()
	{
		return "test_dbmodel";
	}
}
