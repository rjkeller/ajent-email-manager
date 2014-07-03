<?php

namespace Pixonite\TagCloudBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;

use Pixonite\TagCloudBundle\Helper\KeywordCloud;
use Pixonite\TagCloudBundle\Entity\Tag;
use Pixonite\TagCloudBundle\Entity\RelatedTag;


class SyncTagsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('tags:cache:sync')
			->setHelp("Sync's up the tags cache with the latest additions.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$tagCls = BuildOptions::$get['TagCloud.TagInterface'];
		$tagInterface = new $tagCls();

		$q = $tagInterface->getIterator();
		
		$keywords = new KeywordCloud();

		foreach ($q as $beg)
		{
			$beg_text = $tagInterface->getText($beg);
			$beg_id = $tagInterface->getProductId($beg);

			$keywords->appendVerbiage($beg_text);

			$begwords = new KeywordCloud();
			$begwords->appendVerbiage($beg_text);

			Database::query("DELETE FROM tags_related WHERE beg_id = '". $beg_id ."'");

			foreach ($begwords->words as $key => $value)
			{
				$tag = new RelatedTag();
				$tag->num = $value;
				$tag->keyword = $key;
				$tag->beg_id = $beg_id;
				$tag->create();
			}
		}
		foreach ($keywords->words as $key => $value)
		{
			Database::query("DELETE FROM tags WHERE keyword = '". $key ."'");

			$tag = new Tag();
			$tag->num = $value;
			$tag->keyword = $key;
			$tag->create();
		}
	}
}