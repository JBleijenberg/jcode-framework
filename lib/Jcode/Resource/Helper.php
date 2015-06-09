<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 3.0)
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category    docroot
 * @package     docroot
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @copyright   Copyright (c) 2015 MaxServ (http://www.maxserv.com)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Resource;

class Helper
{
	protected $isSharedInstance = true;

	/**
	 * Escape HTML characters from given string
	 *
	 * @param $string
	 * @return string
	 */
	public function sanitize($string)
	{
		return filter_var($string, FILTER_SANITIZE_STRING);
	}

	/**
	 * @todo implement translate method
	 */
	public function translate()
	{
		$args = func_get_arg(0);
		$string = array_shift($args);

		return vsprintf($string, $args);
	}
}