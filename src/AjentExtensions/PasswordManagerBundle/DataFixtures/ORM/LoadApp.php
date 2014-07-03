<?php
namespace AjentApps\AppStoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use AjentApps\AppStoreBundle\Entity\App;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
        $app = new App();
		$app->name = "Password Manager";
		$app->price = 5.50;
		$app->description = "Keep track of your passwords for various websites in one single place.";
		$app->image = "/bundles/passwordmanager/images/logout_32.png";
		$app->is_enabled = true;
		$app->create();
    }

    public function getOrder()
    {
        return 1;
    }
}
