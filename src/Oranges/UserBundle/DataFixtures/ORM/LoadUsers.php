<?php
namespace Oranges\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Oranges\UserBundle\Entity\User;
use Oranges\UserBundle\Entity\Contact;

class LoadUsers extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
		$contact = new Contact();
		$contact->id = 1;
		$contact->user_id = 1;
		$contact->first_name = "Roger";
		$contact->middle_name = "Lynn";
		$contact->last_name = "Keller";
		$contact->company = "Pixonite LLC";
		$contact->email = "rjkeller@pixonite.com";
		$contact->address1 = "345 N LaSalle St";
		$contact->address2 = "Suite 2204";
		$contact->city = "Chicago";
		$contact->state = "IL";
		$contact->zip = "60654";
		$contact->country = "US";
		$contact->phone = "6303646417";
		$contact->phone_country_code = "1";
		$contact->fax = "8156092902";
		$contact->fax_country_code = "1";
		$contact->create();

		$post = new User();
		$post->id = 1;
		$post->username = "rjkeller";
		$post->password = "5f5d8eb9bffa612c54a7068b466512d74e5f32c3733967574c4d7280acb4c131e164cb4cdfaa42eb94416468680f63382a689680d64ffd83ecf786594f6a9cef";
		$post->creation_date = date("Y-m-d H:i:s");
		$post->email = "rjkeller@pixonite.com";
		$post->role = "Admin";
		$post->contact_id = 1;
		$post->create();
	}

    public function getOrder()
    {
        return 1;
    }
}
