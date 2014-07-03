<?php

namespace Oranges\SqlBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class ImportDataCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('sql:data:load')
			->setHelp("The <info>sql:data:load</info> loads the default dataset into the database.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$bundles = $this->getContainer()->getParameter("kernel.bundles");
		foreach ($bundles as $i)
		{
			$bundle = new $i();
			$path = $bundle->getPath() . "/Resources/data/fixtures/doctrine/data.php";

			if (file_exists($path))
			{
			    echo "Executing $path...\n";
				include($path);
			}
		}

		$path = $this->getContainer()->getParameter("kernel.root_dir") ."/data/fixtures/doctrine/data.php";

		if (file_exists($path))
		{
		    echo "Executing $path...\n";
			include($path);
		}
	}
}