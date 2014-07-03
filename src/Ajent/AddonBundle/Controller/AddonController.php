<?php
namespace Ajent\AddonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Oranges\framework\BuildOptions;

class AddonController extends Controller
{
	public static $template_vars = null;

	public function setVariable($name, $value)
	{
		if (self::$template_vars == null)
			self::$template_vars = array();

		self::$template_vars[$name] = $value;
	}

	public function construct($addon_page)
	{
		if (!isset(BuildOptions::$get['AddonBundle']) ||
			!isset(BuildOptions::$get['AddonBundle']['Extensions'][$addon_page]))
			return;

		$pages = BuildOptions::$get['AddonBundle']['Extensions'][$addon_page];
		foreach ($pages as $page)
		{
			$cls = $page['class'];
			$obj = new $cls;
			$obj->ConstructAddon();
		}
	}

	public function destruct($addon_page)
	{
		if (!isset(BuildOptions::$get['AddonBundle']) ||
			!isset(BuildOptions::$get['AddonBundle']['Extensions'][$addon_page]))
			return;

		$pages = BuildOptions::$get['AddonBundle']['Extensions'][$addon_page];
		foreach ($pages as $page)
		{
			$cls = $page['class'];
			$obj = new $cls;
			$obj->DestructAddon();
		}
	}

	public function printBlockAction($addon_page, $block)
	{
		if (!isset(BuildOptions::$get['AddonBundle']) ||
			!isset(BuildOptions::$get['AddonBundle']['Extensions'][$addon_page]))
			return new Response();

		if (self::$template_vars == null)
			self::$template_vars = array();

		$pages = BuildOptions::$get['AddonBundle']['Extensions'][$addon_page];

		return $this->render("AddonBundle:pages:Addon.twig.html",
			array('pages' => $pages,
				'block' => $block,
				'template_vars' => self::$template_vars));
	}
}
