<?php

namespace Pixonite\TagCloudBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Pixonite\TagCloudBundle\Helper\TagManager;


class RefreshCacheCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('tags:cache:refresh')
			->setHelp("beg stop words.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		TagManager::refreshTagCache();
	}
}