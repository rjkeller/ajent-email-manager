<?php
namespace Pixonite\BillingBundle\Helper;

use Oranges\DatabaseModel;

/**
 A method of charging the user money. Invoicing, and other details are not kept
 track in this class. It is just a straight up, charge the user, and other classes
 will handle the rest.
 
 @author R.J. Keller <rjkeller@wordgrab.com>
*/
abstract class BillingMethod extends DatabaseModel
{
    /**
     Deducts the requested amount from the user's account using this
     billing method.
     
     @param double $amount - The amount of money to deduct.
     @throws Exception if the transaction fails.
     */
    public abstract function deductFunds($amount);

    /**
     Returns the transaction ID generated from the previous deductFunds() call.
     */
    public function getTransactionId()
    {
        return "";
    }

    /**
     Returns the user-friendly name of this billing method. For example,
     "Credit Card", or "User Account".
     */
    public abstract function getBillingMethodName();

    /**
     If this billing method requires additional information before purchase,
     then return the page URL in this method, and the user will be redirected
     here prior to the purchase being completed.
     
     Return null for no redirect.
     */
    public function configureBillingUrl()
    {
        return null;
    }
}