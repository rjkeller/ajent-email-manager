<?php

namespace Ajent\Vendor\VendorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Vendor\VendorBundle\Entity\VendorCategory;
use Ajent\Vendor\VendorBundle\Event\VendorCategoryMailListener;
use Ajent\Vendor\VendorBundle\Event\VendorCacheListener;
use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Mail\MailBundle\Event\CategoryMailListener;

class ReparseVendorsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('reparseVendors')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user_id = $input->getArgument("username");

		$db = MongoDb::getDatabase();
		$db->vendors->remove(array(
			"user_id" => $user_id));



		$q = MongoDb::modelQuery($db->email_messages->find(array(
			'recipient_user_id' => $user_id
			)),
			"Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage");

		foreach ($q as $message)
		{
			echo "msg[". $message->id ."].old_vendor_id = ". $message->vendor_id ."\n";
			$message->vendor_id = -1;
			$message->setVendor($message->recipient_user_id);
			$message->save();
			echo "msg[". $message->id ."].new_vendor_id = ". $message->vendor_id ."\n";
		}

		$q = MongoDb::modelQuery($db->email_messages->find(array(
			'folder' => array('$ne' => 'trash')
			)),
			"Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage");
		foreach ($q as $message)
		{
			$q = $db->vendor_categories->find(array(
					'vendor_id' => $message->vendor_id,
					'category_id' => $message->category_id
				))->count();
			if ($q <= 0)
			{

				$vendor = new Vendor();
				$doesVendorExist = $vendor->tryLoad($message->vendor_id);

				if (!$doesVendorExist)
				{
					echo "WARNING: vendor does not exist. Is this possible?\n";
					print_r($message->vendor_id);
					echo "Attempting to reset the vendor...\n";
					echo "Old Vendor: ". $message->vendor_id ."\n";
					$message->vendor_id = -1;
					$message->setVendor($message->recipient_user_id);
					$message->save();
					echo "New Vendor: ". $message->vendor_id ."\n";
				}

				$vc = new VendorCategory();
				$vc->vendor_id = $message->vendor_id;
				$vc->category_id = $message->category_id;
				$vc->create();
				echo "Creating new category...\n";
			}
		}



		$listener = new VendorCategoryMailListener();
		$listener->refreshVendorCategoryCache();

		$listener = new VendorCacheListener();
		$listener->refreshCache();

		$listener = new CategoryMailListener();
		$listener->refreshCache();

		$alerts = MongoDb::modelQuery($db->email_alerts->find(),
			"Ajent\AlertBundle\Entity\Alert");
		foreach ($alerts as $alert)
		{
			$cursor = $db->email_messages->find(array(
				"_id" => new \MongoId($alert->message_id)
				));
			$next = $cursor->getNext();
			$alert->is_invisible = $next['folder'] == "trash";
			$alert->save();
		}
	}
}