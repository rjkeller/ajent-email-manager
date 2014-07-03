<?php
namespace Oranges\gui;

class Shadowbox
{
	public $name;

	public static $ALERT = 0;
	public static $WARNING = 1;
	public static $SECURITY = 10;

	/** styles **/
	public static $YES_NO_CANCEL = 0;
	public static $OK = 1;

	public static function loadShadowbox($params, &$smarty)
	{
		if (empty($params['name']))
			$params['name'] = $params['file'];
		$params['name'] .= $params['incr'];

		$shadowbox = new SmartyShadowbox();
		$shadowbox->name = $params['name'];

		$smarty->register_object('shadowbox', $shadowbox);
		$smarty->display("shadowbox/".$params['file'].".php");
	}

	public function buttons($params, &$smarty)
	{
		switch ($params['type'])
		{
		case 'yesNo':
			global $cmd;
			$cmd = $params['cmd'];
			include("generic_yesnobuttons.php");
			break;
		}
	}

	public static function import($name)
	{
		$smarty = WgSmarty::getInstance();
		$smarty->display("shadowbox/$name.php");
	}

	public static function show($name, $noJs = false, $incr = "")
	{
		$name .= $incr;

        if (!$noJs)
            echo "javascript:";
        echo "showLightbox(document.getElementById('$name'));";
	}

	public static function showShadowbox($params, &$smarty)
	{
		self::show($params['name'], $params['noJs'], $params['incr']);
	}
}
