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
namespace Jcode;

class Log
{
    const EMERG = 0;

    const ALERT = 1;

    const CRIT = 2;

    const ERR = 3;

    const WARN = 4;

    const NOTICE = 5;

    const INFO = 6;

    const DEBUG = 7;

    /**
     * @var DependencyContainer
     */
    protected $_dc;
    /**
     * @param DependencyContainer $dc
     */
    public function __construct(\Jcode\DependencyContainer $dc)
    {
        $this->_dc = $dc;
    }

    public function write($message, $level = 3, $file = 'jcode.log')
    {
        /* @var DateTime $date */
        $date = new \DateTime('now');

        switch ($level){
            case self::ALERT:
                $level = sprintf('ALERT (%s)', $level);
                break;
            case self::CRIT:
                $level = sprintf('CRIT (%s)', $level);
                break;
            case self::ERR:
                $level = sprintf('ERR (%s)', $level);
                break;
            case self::WARN:
                $level = sprintf('WARN (%s)', $level);
                break;
            case self::NOTICE:
                $level = sprintf('NOTICE (%s)', $level);
                break;
            case self::INFO:
                $level = sprintf('INFO (%s)', $level);
                break;
            case self::DEBUG:
                $level = sprintf('DEBUG (%s)', $level);
                break;
        }

        $msg = sprintf("%s %s: %s\r\n", $date->format('Y-m-d\TH:i:s'), $level, $message);

        $logDir = BP . DS . 'public' . DS . 'var' . DS . 'log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logDir . DS . $file, $msg, FILE_APPEND);

        return $this;

    }

    public function writeException(\Exception $e)
    {
        $msg = sprintf("%s\r\n", $e->getMessage());

        foreach ($e->getTrace() as $trace) {
            if (!array_key_exists('file', $trace)) {
                continue;
            }

            $msg .= sprintf("%s:%s %s\r\n", $trace['file'], $trace['line'], $trace['function']);
        }

        $this->write($msg, self::CRIT, 'exception.log');

        return $this;
    }
}