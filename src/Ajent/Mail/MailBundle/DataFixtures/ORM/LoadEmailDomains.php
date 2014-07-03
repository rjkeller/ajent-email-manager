<?php
namespace Ajent\Mail\PeopleScannerBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Ajent\Mail\MailBundle\Entity\EmailDomain;
use Oranges\framework\BuildOptions;
use Oranges\MongoDbBundle\Helper\MongoDb;

class LoadEmailDomains extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
		$db = MongoDb::getDatabase();
		$db->email_domains->remove();
		$db->email_categories->remove();
		$db->email_external_accounts->remove();
		$db->email_messages->remove();
		$db->vendors->remove();
		$db->vendor_categories->remove();

		$emailDomain = new EmailDomain();
		$emailDomain->domain = BuildOptions::$get['MailBundle']['DefaultEmailDomain'];
		$emailDomain->create();
    }

    public function getOrder()
    {
        return 1;
    }
}
