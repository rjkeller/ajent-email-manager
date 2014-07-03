<?php
namespace Ajent\Mail\PeopleScannerBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use AjentApps\Ajent\MailRegistrationBundle\Entity\InviteCode;
use Oranges\MongoDbBundle\Helper\MongoDb;

class LoadBetaInviteCodes extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
		$db = MongoDb::getDatabase();
		$db->beta_invite_codes->remove();

		$code = new InviteCode();
		$code->invite_code = "test";
		$code->is_active = true;
		$code->create();
    }

    public function getOrder()
    {
        return 1;
    }
}
