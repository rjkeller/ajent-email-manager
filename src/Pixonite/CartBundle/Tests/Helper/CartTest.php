<?php
namespace Pixonite\CartBundle\Tests\Helper;

use Pixonite\CartBundle\Tests\Helper\TestProductFactory;
use Pixonite\CartBundle\Helper\Cart;
use Pixonite\BillingBundle\Entity\AdminBillingMethod;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\UserErrorHandler;
use Oranges\framework\BuildOptions;
use Oranges\sql\Database;

class CartTest extends \PHPUnit_Framework_TestCase
{
    public function testCart()
    {
		BuildOptions::loadBuildOptions(__DIR__. '/../../../../../../ScamsList/config/build_options.yml');

		SessionManager::startSession();
		SessionManager::impersonate("rjkeller");

        Database::query("DELETE FROM cart_prep");
        Database::query("DELETE FROM cart");

        $factory = new TestProductFactory();

        $prep = Cart::prepCartItem($factory, array());

        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart_prep
        ");
        $this->assertEquals(1, $q);

        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart
        ");
        $this->assertEquals(0, $q);


        Cart::addPrepToCart($prep);

        $errors = UserErrorHandler::$inst->toString();
        $this->assertTrue(empty($errors));


        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart_prep
        ");
        $this->assertEquals(0, $q);

        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart
        ");
        $this->assertEquals(1, $q);

        Cart::emptyCart();

        $errors = UserErrorHandler::$inst->toString();
        $this->assertTrue(empty($errors));


        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart_prep
        ");
        $this->assertEquals(0, $q);

        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart
        ");
        $this->assertEquals(0, $q);

        $prep = Cart::prepCartItem($factory, array());
        Cart::addPrepToCart($prep);
        Cart::purchaseAll(new AdminBillingMethod());


        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart_prep
        ");
        $this->assertEquals(0, $q);

        $q = Database::scalarQuery("
            SELECT
                COUNT(*)
            FROM
                cart
        ");
        $this->assertEquals(0, $q);
    }
}