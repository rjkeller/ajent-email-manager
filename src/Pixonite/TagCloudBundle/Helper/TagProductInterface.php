<?php
namespace Pixonite\TagCloudBundle\Helper;

/**
 Returns various information about the item you wish to tag. You need to
 implement one of this class and set it in the Build Options to use the
 Tag Bundle.

  @author R.J. Keller <rjkeller@pixonite.com>
*/
interface TagProductInterface
{
	/**
	 Returns all of the posts in the SQL database. Should return an
	 SqlIterator.
	*/
	public function getIterator($user);

	/**
	 Returns the text of the specified blog post that you would like to tag.
	*/
	public function getText($blogPost);

	/**
	 Returns the product ID of the specified blog post.
	*/
	public function getProductId($blogPost);
}
