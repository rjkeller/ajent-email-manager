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
use Oranges\framework\BuildOptions;

use Pixonite\TagCloudBundle\Helper\KeywordCloud;
use Pixonite\TagCloudBundle\Entity\RelatedBeg;


class RefreshRelatedBegsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('relatedBegs:cache:refresh')
			->setHelp("beg stop words.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$tagCls = BuildOptions::$get['TagCloudBundle']['TagProductInterface'];
		$tagInterface = new $tagCls();
		$q = $tagInterface->getIterator();

		foreach ($q as $product)
		{
			$product_id = $tagInterface->getProductId($product);
			echo "++ Parsing beg: ". $product_id ."\n";
			$q = Database::query("
				SELECT
					keyword
				FROM
					tags_related
				WHERE
					product_id = '". $product_id ."'
				ORDER BY
					num DESC
			");
			$keywords = "";
			$i = 0;
			while ($keyword = $q->fetch_object())
			{
				//only use the first 15 keywords
				if ($i++ > 3)
					break;

				if ($keywords == "")
					$keywords = "keyword = '". $keyword->keyword ."'";
				else
					$keywords .= " OR keyword = '". $keyword->keyword ."'";
			}

			Database::query("DELETE FROM related_begs WHERE product_id = '". $product_id ."'");

			if (empty($keywords))
				continue;

			Database::query("
				INSERT INTO
					related_begs
				SELECT
					NULL, '". $product_id ."', product_id, COUNT(keyword)
				FROM
					tags_related
				WHERE
					(". $keywords .") AND
					product_id != '". $product_id ."'
				GROUP BY
					product_id
				ORDER BY
					COUNT(keyword) DESC,
					RAND()
				LIMIT
					5
			");

			//since the above query takes up a lot of server resources, we'll
			//let the server catch its breadth before continuing.
//			sleep(2);
		}
	}
}