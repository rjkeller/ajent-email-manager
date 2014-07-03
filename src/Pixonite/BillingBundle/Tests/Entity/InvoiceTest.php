<?php
namespace Pixonite\BillingBundle\Tests\Invoice;

use Pixonite\BillingBundle\Helper\BillingUtility;
use Pixonite\BillingBundle\Entity\AdminBillingMethod;
use Pixonite\BillingBundle\Entity\Invoice;
use Pixonite\CartBundle\Entity\CartEntry;
use Pixonite\CartBundle\Entity\Product;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;

class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadCartInformation()
    {
		BuildOptions::loadBuildOptions(__DIR__. '/../../../../../../ScamsList/config/build_options.yml');

		SessionManager::startSession();
		SessionManager::impersonate("rjkeller");

        $cartEntry = new CartEntry();
        $cartEntry->term = "Flat";
        $cartEntry->price = 5.50;

        $product = new Product();
        $product->id = 55;
        $product->expires = date("Y-m-d H:i:s");
        
        $transaction = new AdminBillingMethod();
        $transaction->id = 66;
        
        $invoice = new Invoice();
        $invoice->loadCartInformation($cartEntry,
            $product, $transaction, "Test Invoice!");

        $invoice->create();
        return true;
    }
}