<?php
namespace Oranges\FrontendBundle\Helper;

class MessageBoxHandler
{
	private static $messages = array();

	public static function happy($message, $header = null)
	{
		array_push(self::$messages, array("happy", $message, $header));
	}

	public static function notice($message, $header = null)
	{
		array_push(self::$messages, array("notice", $message, $header));
	}

	public static function error($message, $header = null)
	{
		array_push(self::$messages, array("error", $message, $header));
	}

	public static function warning($message, $header = null)
	{
		array_push(self::$messages, array("warning", $message, $header));
	}

	public static function getTemplateVars()
	{
	    $template_vars = array(
	        "success" => array("title" => null, "message" => array()),
	        "error" => array("title" => null, "message" => array()),
	        "warning" => array("title" => null, "message" => array()),
	    );
		foreach (self::$messages as $i)
		{
			switch ($i[0])
			{
			case 'error':
			    if ($i[2] != null)
			        $template_vars['error']['title'] = $i[2];
			    else if (!isset($template_vars['error']['title']))
			        $template_vars['error']['title'] = "Invalid Input";

				$template_vars['error']['message'][] = $i[1];
				break;

			case 'happy':
    		    if ($i[2] != null)
    		        $template_vars['success']['title'] = $i[2];
			    else if (!isset($template_vars['success']['title']))
			        $template_vars['success']['title'] = "Success!";

    			$template_vars['success']['message'][] = $i[1];
				break;

            case 'notice':
            default:
    		    if ($i[2] != null)
    		        $template_vars['warning']['title'] = $i[2];
			    else if (!isset($template_vars['warning']['title']))
			        $template_vars['warning']['title'] = "Warning";

    			$template_vars['warning']['message'][] = $i[1];
				break;
			}
		}
		self::$messages = array();
		return $template_vars;
	}
}
