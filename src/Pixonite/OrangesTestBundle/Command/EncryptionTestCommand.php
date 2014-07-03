<?php

namespace Pixonite\OrangesTestBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Pixonite\OrangesTestBundle\Entity\TestTable;

class EncryptionTestCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('test:dbmodel')
			->setHelp("Tests the email importing mechanism.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$test = new TestTable();
		$test->col = "test";
		$test->create();
		
		$test1 = new TestTable();
		$test1->load($test->id);
		print_r($test1->getArray());

		$test1->col = "test2";
		$test1->save();

		$test2 = new TestTable();
		$test2->load($test->id);
		print_r($test2->getArray());
	}
}