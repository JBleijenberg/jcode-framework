<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    J!Code Framework
 * @package     J!Code Framework
 * @author      Jeroen Bleijenberg <jeroen@maxserv.nl>
 * 
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Jcode\Application\Helper;

class Encryption extends \Jcode\Application\Helper
{

    /**
     * @var \Jcode\Application\Config
     */
    protected $_config;

    /**
     * @param \Jcode\Translate\Phrase $phrase
     * @param \Jcode\Application\Config $config
     */
    public function __construct(\Jcode\Translate\Phrase $phrase, \Jcode\Application\ConfigSingleton $config) {
        parent::__construct($phrase);

        $this->_config = $config;
    }

    /**
     * Generate a salt of specified length
     *
     * @param int $length
     * @return string
     */
    public function generateSalt($length = 10)
    {
        $chars = sprintf('%s%s%s@#$^&*()~-_', implode('', range('a', 'z')), implode('', range(0,9)),implode('', range('A', 'Z')));

        $salt = '';

        for ($i = 0; $i < $length; $i++) {
            $salt .= $chars[rand(0, strlen($chars)-1)];
        }

        return (string)$salt;
    }

    /**
     * Hash the given data with the supplied algorithm
     *
     * @param $data
     * @param string $algorithm
     * @return string
     */
    public function hash($data, $algorithm = 'sha256')
    {
        return hash($algorithm, $data);
    }

    /**
     * Encrypt the given data
     *
     * @param $data
     * @return string
     * @throws Exception
     */
    public function encrypt($data)
    {
        if (!extension_loaded('mcrypt')) {
            throw new Exception($this->translate('Mcrypt module is not loaded. Cannot encrypt data.'));
        }

        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);

        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->getEncryptionKey(), $data, MCRYPT_MODE_ECB, $iv);
    }

    /**
     * Decrypt data generated with encrypt()
     *
     * @param $data
     * @return string
     * @throws Exception
     */
    public function decrypt($data)
    {
        if (!extension_loaded('mcrypt')) {
            throw new Exception($this->translate('Mcrypt module is not loaded. Cannot encrypt data.'));
        }

        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);

        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->getEncryptionKey(), $data, MCRYPT_MODE_ECB, $iv);
    }

    /**
     * Get encryption key from application config
     *
     * @return mixed
     */
    public function getEncryptionKey()
    {
        return $this->_config->getEncryption()->getKey();
    }
}