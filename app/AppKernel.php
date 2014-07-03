<?php
include(__DIR__."/autoload.php");

date_default_timezone_set('GMT');

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\ClassLoader\DebugUniversalClassLoader;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

class AppKernel extends Kernel
{
    public function registerRootDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        $bundles = array(
			new AjentExtensions\PasswordManagerBundle\PasswordManagerBundle(),

			new AjentApps\Webmail\MailLandingBundle\SocialMailBundle(),
			new AjentApps\Ajent\AjentHomePageBundle\AjentHomePageBundle(),
			new AjentApps\Social\Top10Bundle\Top10Bundle(),
			
			new AjentApps\MailServerManagerBundle\MailServerManagerBundle(),
			new AjentApps\AppStoreBundle\AppStoreBundle(),

			new AjentServers\MailServerBundle\MailServerBundle(),
			new AjentServers\ExternalMailServerBundle\ExternalMailServerBundle(),

			new AjentApps\Ajent\Widgets\CategoryWidgetBundle\CategoryWidgetBundle(),
			new AjentWidgets\InviteAFriendBundle\InviteAFriendBundle(),

			new Ajent\LoggingHandlerBundle\LoggingHandlerBundle(),
			new Ajent\AddonBundle\AddonBundle(),
			new Ajent\Mail\ExternalMailBundle\ExternalMailBundle(),
			new Ajent\Mail\MailBundle\MailBundle(),
			new AjentApps\Ajent\MailRegistrationBundle\MailRegistrationBundle(),
			new AjentApps\Social\SocialPostsBundle\SocialPostsBundle(),
			new Ajent\Mail\PeopleScannerBundle\PeopleScannerBundle(),
			new Ajent\Vendor\VendorBundle\VendorBundle(),
			new Ajent\AlertBundle\AlertBundle(),
			new Ajent\Mail\Testing\EmailTestBundle\EmailTestBundle(),
			new Ajent\Vendor\VendorScanBundle\VendorScanBundle(),

			new Pixonite\UserAdminBundle\UserAdminBundle(),
			new Pixonite\TagCloudBundle\TagCloudBundle(),
			new Pixonite\BillingBundle\BillingBundle(),
			new Pixonite\CartBundle\CartBundle(),

			new Oranges\UserBundle\UserBundle(),
			new Oranges\FrontendBundle\FrontendBundle(),
			new Oranges\LoggingBundle\LoggingBundle(),
			new Oranges\SqlBundle\SqlBundle(),
			new Oranges\FormsBundle\FormsBundle(),
			new Oranges\SearchBundle\SearchBundle(),
			new Oranges\RedisBundle\RedisBundle(),
			new Oranges\MongoDbBundle\MongoDbBundle(),

            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
	        new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
        );

        if ($this->isDebug()) {
        //    $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function init()
    {
        if ($this->debug) {
            ini_set('display_errors', 1);
            error_reporting(-1);

            DebugUniversalClassLoader::enable();
            ErrorHandler::register();
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
//            ini_set('display_errors', 0);
        }
    }

	public function boot()
	{
		parent::boot();

		\Oranges\MasterContainer::$container = $this->getContainer();
		BuildOptions::loadBuildOptions(__DIR__. '/config/build_options.yml');
		$sm = $this->getContainer()->get("Oranges.UserBundle.SessionManager");
		$sm->startSession();
	}

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}


function OrangesLogger($str, $type = "general", $otherAttrs = null, $isWarning = false, $extra = null)
{
//	echo $str ."\n";
	\Oranges\LoggingBundle\Helper\Logger::log($str, $type, $otherAttrs, $isWarning, $extra);
}
