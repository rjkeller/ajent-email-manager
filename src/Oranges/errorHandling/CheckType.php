<?php
namespace Oranges\errorHandling;

use Oranges\sql\SqlUtility;
use Oranges\user\Helper\User;

/**
 Performs validation for ceratin WordGrab data types.

 To add a new type, just create a new function for the data type (where it returns
 true if the vlaidation failed, and runs $this->error(...) otherwise).

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class CheckType
{
	public static $inst;

	/**
	*/
	public function error(ErrorMetaData $metaData = null)
	{
		return false;
	}

    public function checkCreditCardType($expr, ErrorMetaData $metaData = null)
    {
		if ($expr != "visa" && $expr != "mastercard")
			return $this->error($metaData);
		return true;
    }

    public function checkRegExp($expr, $id, ErrorMetaData $metaData = null)
    {
		error_reporting(E_ALL ^ E_DEPRECATED);
	
        $result = ereg($expr, $id, $group);
        $out = $result && sizeof($group) == 1;
		if (!$out)
			return $this->error($metaData);
		return true;
    }

    public function checkArraySize($array, $num, ErrorMetaData $metaData = null)
    {
	    $out = sizeof($array) <= $num;
		if (!$out)
			return $this->error($metaData);
		return true;
    }

    public function checkEmail($email, $allowEmpty = false, ErrorMetaData $metaData = null)
    {
        if ($allowEmpty && empty($email))
            return true;
        $out = $this->checkRegExp("[A-Za-z0-9]*@[A-Za-z0-9]*\.[A-Za-z0-9]*", $email, $metaData);
		if (!$out)
			return $this->error($metaData);
		return true;
    }

    public function checkId($id, $allowNull = false, ErrorMetaData $metaData = null)
    {
        if ($allowNull && $id == null)
            return true;

        if (!$this->checkStr($id, $allowNull)) //it actually happens!!!
            return $this->error($metaData);

        $len = strlen($id);
        for ($i = 0; $i < $len; $i++)
        {
            if (!((strcmp($id{$i}, 'A') >= 0 && strcmp($id{$i}, 'Z') <= 0) ||
                (strcmp($id{$i}, 'a') >= 0 && strcmp($id{$i}, 'z') <= 0) ||
                (strcmp($id{$i}, '0') >= 0 && strcmp($id{$i}, '9') <= 0)))
				return $this->error($metaData);
        }
        return true;
    }

    public function checkBool(&$item, ErrorMetaData $metaData = null)
    {
		if ($item == "checked" || $item)
			$item = true;
		else
			$item = false;
		return true;
    }

    public function checkInt(&$id, $isEmpty = false, ErrorMetaData $metaData = null)
    {
        if ($isEmpty && empty($id))
            return true;
		$id = trim($id);
        $isInt = preg_match('@^[-]?[0-9]+$@',$id) === 1;
        if (!$isInt)
            return $this->error($metaData);
		return true;
    }

	public function checkNs(&$id, ErrorMetaData $metaData = null)
	{
	    if (empty($id))
	        return $this->error($metaData);

	    $id = strtolower($id);
	    $len = strlen($id);

	    $numPeriods = 0;
	    for ($i = 0; $i < $len; $i++)
	    {
	        if ($id{$i} == ".")
	        {
	            $numPeriods++;
	        }
	        else if (!((strcmp($id{$i}, 'A') >= 0 && strcmp($id{$i}, 'Z') <= 0) ||
	            (strcmp($id{$i}, 'a') >= 0 && strcmp($id{$i}, 'z') <= 0) ||
	            (strcmp($id{$i}, '0') >= 0 && strcmp($id{$i}, '9') <= 0) ||
	            strcmp($id{$i}, '_') == 0 ||
	            strcmp($id{$i}, '-') == 0
	            ))
			{
	            return $this->error($metaData);
			}
	    }

		//DNS names need to have at least 3 periods
	    if ($numPeriods < 2)
			return $this->error($metaData);

		return true;
	}

	public function checkTld(&$id, ErrorMetaData $metaData = null)
	{
        $period = -1;
		$len = strlen($id);
        for ($i = 0; $i < $len; $i++)
        {
            if ($id{$i} == ".")
            {
            	if ($period == -1)
	                $period = $i;
            }
		}

		//if no TLD is specified, append .com by default.
		if ($period == -1)
		{
			$id .= ".com";
			return true;
		}

		//is the TLD valid? If not, error out.
        $tld = substr($id, $period+1, $len-$period-1);
        $count = SqlUtility::getCount("SELECT COUNT(*) FROM product_type WHERE type = 'domainRegistration' AND name = '$tld' LIMIT 1");
        if ($count <= 0)
			return $this->error($metaData);
		return true;
	}

	/**
	 Checks if the domain is valid, but does not modify the inputs for things like lower casing the letters in the
	 domain.
	
	 Does not check if the TLD is valid.
	*/
	public function readOnlyCheckDomain($id, ErrorMetaData $metaData = null)
	{
        if (empty($id))
			return $this->error($metaData);

        $len = strlen($id);

		if ($len > 255)
			return $this->error($metaData);

        $period = -1;
		$numPeriods = 0;
		$retval = true;
        for ($i = 0; $i < $len; $i++)
        {
            if ($id{$i} == ".")
            {
            	if ($period == -1)
	                $period = $i;
				$numPeriods++;
            }
            else if (!((strcmp($id{$i}, 'A') >= 0 && strcmp($id{$i}, 'Z') <= 0) ||
                (strcmp($id{$i}, 'a') >= 0 && strcmp($id{$i}, 'z') <= 0) ||
                (strcmp($id{$i}, '0') >= 0 && strcmp($id{$i}, '9') <= 0) ||
                strcmp($id{$i}, '_') == 0 ||
                strcmp($id{$i}, '-') == 0
                ))
			{
				return $this->error($metaData);
			}
        }
        //if the user put www. at the beginning, trim it off.
        if (substr($id, 0, 3) == "www")
			return $this->error($metaData);

		if ($period+1 > 63)
			return $this->error($metaData);
        if ($numPeriods < 0)
			return $this->error($metaData);
		if (!$retval)
			return $this->error($metaData);
		return true;
    }

	public function checkDomain(&$id, ErrorMetaData $metaData = null)
	{
        if (empty($id))
			return $this->error($metaData);

        $id = strtolower($id);
        $len = strlen($id);

		if ($len > 255)
			return $this->error($metaData);

        $period = -1;
		$retval = true;
		$numPeriods = 0;
        for ($i = 0; $i < $len; $i++)
        {
            if ($id{$i} == ".")
            {
				$numPeriods++;
            	if ($period == -1)
	                $period = $i;
            }
            else if (!((strcmp($id{$i}, 'A') >= 0 && strcmp($id{$i}, 'Z') <= 0) ||
                (strcmp($id{$i}, 'a') >= 0 && strcmp($id{$i}, 'z') <= 0) ||
                (strcmp($id{$i}, '0') >= 0 && strcmp($id{$i}, '9') <= 0) ||
                strcmp($id{$i}, '_') == 0 ||
                strcmp($id{$i}, '-') == 0
                ))
			{
				$retval = false;
				$id = str_replace($id{$i}, '', $id);
				$i = 0;
				$len = strlen($id);
			}
        }
        //if the user put www. at the beginning, trim it off.
        if (substr($id, 0, 3) == "www")
        	$id = substr($id, 4, strlen($id));

		if ($period+1 > 63)
			return $this->error($metaData);

		//is the TLD valid? If not, error out.
        $tld = substr($id, $period+1, $len-$period-1);
        $count = SqlUtility::getCount("SELECT COUNT(*) FROM product_type WHERE type = 'domainRegistration' AND name = '$tld' LIMIT 1");
        if ($count <= 0)
			return $this->error($metaData);

        if ($numPeriods < 0)
			return $this->error($metaData);
		if (!$retval)
			return $this->error($metaData);
		return true;
    }

	public function checkDomainBool($id, ErrorMetaData $metaData = null)
	{
        if (empty($id))
			return $this->error($metaData);

        $id = strtolower($id);
        $len = strlen($id);

		if ($len > 255)
			return $this->error($metaData);

        $period = -1;
		$retval = true;
        for ($i = 0; $i < $len; $i++)
        {
            if ($id{$i} == ".")
            {
            	if ($period == -1)
	                $period = $i;
            }
            else if (!((strcmp($id{$i}, 'A') >= 0 && strcmp($id{$i}, 'Z') <= 0) ||
                (strcmp($id{$i}, 'a') >= 0 && strcmp($id{$i}, 'z') <= 0) ||
                (strcmp($id{$i}, '0') >= 0 && strcmp($id{$i}, '9') <= 0) ||
                strcmp($id{$i}, '_') == 0 ||
                strcmp($id{$i}, '-') == 0
                ))
			{
				$retval = false;
				$id = str_replace($id{$i}, '', $id);
				$i = 0;
				$len = strlen($id);
			}
        }
        //if the user put www. at the beginning, trim it off.
        if (substr($id, 0, 3) == "www")
        	$id = substr($id, 4, strlen($id));

		if ($period+1 > 63)
			return $this->error($metaData);

		//is the TLD valid? If not, error out.
        $tld = substr($id, $period+1, $len-$period-1);
        $count = SqlUtility::getCount("SELECT COUNT(*) FROM product_type WHERE type = 'domainRegistration' AND name = '$tld' LIMIT 1");
        if ($count <= 0)
			return $this->error($metaData);

        if ($numPeriods < 0)
			return $this->error($metaData);
		if (!$retval)
			return $this->error($metaData);
		return true;
    }


    public function checkDouble($id, $isEmpty = false, ErrorMetaData $metaData = null)
    {
        if (!is_double($id) && !is_numeric($id))
			return $this->error($metaData);
        return true;
    }

    public function checkIp(&$ip, ErrorMetaData $metaData = null)
    {
        $data = explode('.', $ip);
		$size = sizeof($data);
        if (empty($ip) || $size != 4)
			return $this->error($metaData);
        for ($i = 0; $i < $size; $i++)
        {
            if (!is_numeric($data[$i]))
				return $this->error($metaData);
        }
        return true;
    }

    public function checkUsername(&$username, ErrorMetaData $metaData = null)
    {
		$len = strlen($username);
        if ($len < 4)
			return $this->error($metaData);

        $id = strtolower($username);

        for ($i = 0; $i < $len; $i++)
        {
            if (!((strcmp($id{$i}, 'a') >= 0 && strcmp($id{$i}, 'z') <= 0) ||
                (strcmp($id{$i}, '0') >= 0 && strcmp($id{$i}, '9') <= 0) ||
                strcmp($id{$i}, '_') == 0 ||
                strcmp($id{$i}, '-') == 0
                ))
				return $this->error($metaData);

        }
		return true;
	}

    public function checkStr(&$id, $allowEmpty = false, ErrorMetaData $metaData = null)
    {
        $id = str_replace(":", "&#58;", $id);
        $id = str_replace("?", "&#63;", $id);
        $id = str_replace("'", "&#39;", $id);
        $id = str_replace("\"", "&quot;", $id);
        $id = str_replace("<", "&lt;", $id);
        $id = str_replace(">", "&gt;", $id);

        if ($id == "0")
            return true;
        if (!$allowEmpty && empty($id))
			return $this->error($metaData);
        return true;
    }

	public function assert($val, ErrorMetaData $metaData = null)
	{
		if (!$val)
			$this->error($metaData);
		return true;
	}

	/**
	 Returns true if this user is allowed to modify the userid taken in.

	NOTE: NOT SQL INJECTION FRIENDLY! MAKE SURE INPUT TAKEN IN IS VALIDATED!
	*/
	public function checkUserModifyPermissions($user, ErrorMetaData $metaData = null)
	{
		if (User::$isAdmin)
			return true;

		$out = $user->resellerid == User::$userinfo->resellerid;
		if (!$out)
			$this->error($metaData);
		return true;
	}
}
CheckType::$inst = new CheckType();
