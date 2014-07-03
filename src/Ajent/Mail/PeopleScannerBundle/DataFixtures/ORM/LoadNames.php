<?php
namespace Ajent\Mail\PeopleScannerBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Ajent\Mail\PeopleScannerBundle\Entity\PersonName;

use Oranges\MongoDbBundle\Helper\MongoDb;

class LoadNames extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
		$db = MongoDb::getDatabase();
		$db->person_names->remove();

		$f = fopen(__DIR__."/names.txt", "r");
		while (($s = fgets($f)) !== false)
		{
			$name = new PersonName();
			$name->name = trim($s);
			$name->create();
		}
		fclose($f);
    }

    public function getOrder()
    {
        return 1;
    }
}
