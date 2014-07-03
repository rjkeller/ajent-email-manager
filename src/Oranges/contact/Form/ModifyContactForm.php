<?php
namespace Oranges\contact\Form;

use Oranges\forms\WgForm;
use Oranges\forms\StdTypes;
use Oranges\forms\FormCoder;
use Oranges\contact\Model\Contact;

/**
 This form is used to modify a contact in the WordGrab system. This is either a domain contact
 or a billing/user profile contact.

 This form does support the saving of multiple contacts simultaneoulsy. This is mostly used
 when modifying domain contacts.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class ModifyContactForm extends WgForm
{
	private $contacts = array();

	public function __construct()
	{
		parent::__construct("saveContact");
	}

	public function addContact(Contact $c, $strName = "", $blankKey = false)
	{
		$this->contacts[] = $c;
		$key = $c->id .":";
		if ($blankKey)
		{
			$key = ":";

			//hackish, i know. but let's keep this here until I rework this
			//contact form. Since right now a lot of scripts break when you hit
			//the colon.
			foreach ($_POST as $k => $value)
			{
				if ($k{0} == ":")
				{
					$k = substr($k, 1, strlen($k)-1);
					$_POST[$k] = $value;
				}
			}
		}

		$contactHandler = " onblur=\"changecolor('#FFF', '". $key ."contact1')\" onfocus=\"changecolor('#ddeaff', '". $key ."contact1')\"";
		$addressHandler = " onblur=\"changecolor('#FFF', '". $key ."address1')\" onfocus=\"changecolor('#ddeaff', '". $key ."address1')\"";
		$phoneEventHandler = " onblur=\"changecolor('#FFF', '". $key ."phone1')\" onfocus=\"changecolor('#ddeaff', '". $key ."phone1')\"";

		$this->addField($key."fname", "$strName First Name", new StdTypes("str"), null, array("extra" => $contactHandler));
		$this->addField($key."lname", "$strName Last Name", new StdTypes("str"), null, array("extra" => $contactHandler));
		$this->addField($key."company", "$strName Company", new StdTypes("str"), null, array("optional" => true, "extra" => $contactHandler));
		$this->addField($key."address1", "$strName Address", new StdTypes("str"), null, array("extra" => $addressHandler));
		$this->addField($key."city", "$strName City", new StdTypes("str"), null, array("extra" => $addressHandler));
		$this->addField($key."state", "$strName State", new StdTypes("str"), null, array("extra" => $addressHandler));
		$this->addField($key."address2", "$strName Address (line 2)", new StdTypes("str"), null, array("extra" => $addressHandler, "optional" => true));
		$this->addField($key."address3", "$strName Address (line 3)", new StdTypes("str"), null, array("extra" => $addressHandler, "optional" => true));
		$this->addField($key."zip", "$strName Postal Code", new StdTypes("str"), null, array("extra" => $addressHandler));
		$this->addField($key."country", "$strName Country", new StdTypes("country"), null, array("extra" => $addressHandler));
		$this->addField($key."phone", "$strName Phone", new StdTypes("phone"), null, array("extra" => $phoneEventHandler));
		$this->addField($key."phonecc", "$strName Phone CC", new StdTypes("phonecc"), null, array("extra" => $phoneEventHandler));
		$this->addField($key."phoneext", "$strName Phone Ext", new StdTypes("phoneext"), null, array("extra" => $phoneEventHandler));
		$this->addField($key."fax", "fax", new StdTypes("phone"), null, array("extra" => $phoneEventHandler, "optional" => true));
		$this->addField($key."faxcc", "fax", new StdTypes("phonecc"), null, array("extra" => $phoneEventHandler, "optional" => true));
		$this->addField($key."faxext", "fax", new StdTypes("phoneext"), null, array("extra" => $phoneEventHandler, "optional" => true));
		$this->addField($key."email", "$strName Email", new StdTypes("email"), null, array("extra" => $contactHandler));

		$this->fillWithArray($c->getArray(), $key);
	}

	/**
	 Run this once you've finished adding contacts to this form.
	*/
	public function submit()
	{
		if (FormCoder::checkCode("saveContact"))
		{
			foreach ($this->contacts as $c)
			{
				self::private_contactSave($c->id);
			}
			return true;
		}
		return false;
	}

	public function printContactField($contactName, $fieldName)
	{
		return $this->printField($contactName . $fieldName);
	}

	//__________PRIVATE FUNCTIONS________________//
	private static function private_contactSave($id)
	{
		$t = new Contact();
		$t->load($id);

		$array = $t->getArray();
		foreach ($array as $key => $value)
		{
			if ($key == "id" || $key == "uid" || $key == "encrypt" || $key == "extradata")
				continue;

			ForceError::$inst->checkStr($_POST[$id.$key]);
			$t->setColumn($key, $_POST[$id.$key]);
		}
		$t->save();
	}

}
