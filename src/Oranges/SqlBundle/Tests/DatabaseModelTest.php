<?php
namespace Oranges\SqlBundle\Tests;

use Bundle\Pixonite\CartBundle\Tests\Helper\TestProductFactory;
use Bundle\Pixonite\CartBundle\Helper\Cart;

use Oranges\UserBundle\Entity\User;
use Oranges\framework\BuildOptions;
use Oranges\sql\Database;

class DatabaseModelTest extends \PHPUnit_Framework_TestCase
{
    public function testDatabaseModel()
    {
		BuildOptions::loadBuildOptions(__DIR__. '/../../../../../ScamsList/config/build_options.yml');

        $num_users = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                users
        ");
        $user = new User();
        $user->create();

        $current_num_users = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                users
        ");

        $this->assertEquals($num_users + 1, $current_num_users);
        $this->assertTrue($user->id != "");

        $user->delete();

        $current_num_users = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                users
        ");
        $this->assertEquals($current_num_users, $num_users);
    }
}