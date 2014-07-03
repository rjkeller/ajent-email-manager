<?php
namespace Oranges\UserBundle\Helper;

class TwigUserBundleExtension extends \Twig_Extension
{
    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    function getGlobals() {
		return array(
			'is_logged_in' => SessionManager::$logged_in,
			'user' => SessionManager::$user
		);
	}

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    function getName() {
		return 'UserBundleExtension';
	}
}