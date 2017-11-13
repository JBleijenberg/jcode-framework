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
 * @author      Jeroen Bleijenberg
 *
 * @copyright   Copyright (c) 2017
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode;

class Helper
{

    protected $isSharedInstance = true;

    protected static $encryptionMethod = 'AES-256-CBC';

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
        $args = (array) func_get_arg(0);
        $string = array_shift($args);

        return vsprintf($string, $args);
    }

    /**
     * Get application url
     *
     * @param $path
     * @param array $parameters
     * @return string
     */
    public function getUrl($path, array $parameters = []) :string
    {
        return Application::getUrl($path, $parameters);
    }

    /**
     * Encrypt value
     *
     * @param $value
     * @return string
     */
    public static function encrypt($value)
    {
        $secret = Application::getConfig('encryption_key');
        $iv     = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$encryptionMethod));

        $data = openssl_encrypt($value, self::$encryptionMethod, $secret, 0, $iv);

        $encryptedValue = sprintf('%s:%s', $data, $iv);

        return $encryptedValue;
    }

    /**
     * Decrypt value
     *
     * @param $value
     * @param $iv
     * @return string
     */
    public static function decrypt($value, $iv)
    {
        $secret = Application::getConfig('encryption_key');

        return openssl_decrypt($value, self::$encryptionMethod, $secret, 0, $iv);
    }
}