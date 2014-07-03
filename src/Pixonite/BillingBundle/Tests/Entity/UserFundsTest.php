<?php
namespace Pixonite\BillingBundle\Tests\Invoice;

use Pixonite\BillingBundle\Entity\UserFunds;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;

class UserFundsTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadCartInformation()
    {
		BuildOptions::loadBuildOptions(__DIR__. '/../../../../../../ScamsList/config/build_options.yml');

		SessionManager::startSession();
		SessionManager::impersonate("rjkeller");

        $funds = UserFunds::getInstance();
        
        $balance = $funds->getBalance();

        $funds->addFunds(81.23);
        $this->assertTrue($funds->canPurchase(8));
        $this->assertEquals($balance + 81.23, $funds->getBalance());

        $funds->removeFunds(81.23);
        $this->assertEquals($balance, $funds->getBalance());

        $this->assertFalse($funds->canPurchase(8 + $balance));
    }
}