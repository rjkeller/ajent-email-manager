<?php
namespace Oranges\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Oranges\UserBundle\Entity\Permissions;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
		$permissions = new Permissions();
		$permissions->name = "Admin";
		$permissions->is_active = true;
		$permissions->admin_access = true;
		$permissions->create();

		$permissions = new Permissions();
		$permissions->name = "Customer";
		$permissions->is_active = true;
		$permissions->admin_access = false;
		$permissions->create();

		$permissions = new Permissions();
		$permissions->name = "Inactive";
		$permissions->is_active = false;
		$permissions->admin_access = false;
		$permissions->create();
    }

    public function getOrder()
    {
        return 1;
    }
}
