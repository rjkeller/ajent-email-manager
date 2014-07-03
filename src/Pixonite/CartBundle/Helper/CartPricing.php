<?php
namespace Pixonite\CartBundle\Helper;

use Oranges\sql\SqlUtility;
use Oranges\sql\SqlTable;
use Oranges\sql\WgDbh;

use Oranges\UserBundle\Helper\User;

class CartPricing
{
	public static function getProductTypePrice($product_type)
	{
		//does user have a package?
		if (User::$extras->package != "")
		{
			$package = SqlUtility::getObject("package", User::$extras->package);
			$q = WgDbh::query("SELECT * FROM package_element WHERE packageid = '$package->id' AND productid = '$product_type->id' LIMIT 1");
			$product_type = $q->fetch_object();
			$q->close();
		}
		if (!isset($product_type->price))
			return array("price" => -1,
				"term" => "");
		return array("price" => $product_type->price,
			"term" => $product_type->term);
	}

	public static function getPriceArray($productName, $productType)
	{
		$q = WgDbh::query("SELECT * FROM product_type WHERE name = '$productName' AND type = '$productType' LIMIT 1");
		$product_type = $q->fetch_object();
		$q->close();

		return self::getProductTypePrice($product_type);
	}

	public static function getPriceAsString($productName, $productType)
	{
		$data = self::getPriceArray($productName, $productType);

		$str = "$". number_format($data['price'], 2, '.', ',');

		if ($data['price'] == -1)
			$str = "N/A";

		switch ($data['term'])
		{
		case 'yearly': $str .= "/yr";break;
		case 'monthly': $str .= "/mo";break;
		}
		return $str;
	}
}