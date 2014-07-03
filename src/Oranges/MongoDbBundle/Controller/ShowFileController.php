<?php
namespace Oranges\MongoDbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ShowFileController extends Controller
{
	public function showImageAction($database, $id)
	{
		$mid = new \MongoId($id);
		$mongo = new \Mongo();
		$db = $mongo->selectDB($database);

		$grid = $db->getGridFS();

		header("Content-Type: image/jpeg");
		$file = $grid->get($mid);
		$stream = $file->getResource();
		while (!feof($stream))
			echo fread($stream, 8192);
		die();
	}
}
