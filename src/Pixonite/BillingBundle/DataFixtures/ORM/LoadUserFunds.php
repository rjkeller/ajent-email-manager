<?php
namespace Pixonite\BillingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Pixonite\BillingBundle\Entity\UserFunds;

class LoadUserFunds extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
		$funds = new UserFunds();
		$funds->user_id = 1;
		$funds->amount = 20.53;
		$funds->hasLowBalanceAlert = false;
		$funds->create();
	}

    public function getOrder()
    {
        return 1;
    }
}
