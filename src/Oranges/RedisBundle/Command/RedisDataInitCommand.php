<?php

namespace Oranges\RedisBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class RedisDataInitCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('redis:data:init')
			->setHelp("Flushes out Redis stats.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$bundles = $this->getContainer()->getParameter("kernel.bundles");
		foreach ($bundles as $i)
		{
			$bundle = new $i();
			$path = $bundle->getPath() . "/DataFixtures/Redis/init.php";

			if (file_exists($path))
			{
			    echo "Executing $path...\n";
				include($path);
			}
		}

		$path = $this->getContainer()->getParameter("kernel.root_dir") ."/DataFixtures/Redis/init.php";

		if (file_exists($path))
		{
		    echo "Executing $path...\n";
			include($path);
		}
	}
}