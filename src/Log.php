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
 * @category    J!Code: Framework
 * @package     J!Code: Framework
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @copyright   Copyright (c) 2017 J!Code (http://www.jcode.nl)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode;

use \DateTime;
use \Exception;

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

    protected $logFile = 'jcode.log';

    protected $message;

    protected $level = 3;

    protected $logDir;

    public function __construct()
    {
        $this->logDir = BP . DS . 'var' . DS . 'log';
    }

    /**
     * Set a different log file to write to
     *
     * @param string $logfile
     *
     * @return $this
     */
    public function setLogfile($logfile = 'jcode.log')
    {
        $this->logFile = $logfile;

        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function setLevel($level = 3)
    {
        if (intval($level)) {
            $this->level = $level;
        }

        return $this;
    }

    /**
     * Write to logfile
     *
     * @return $this
     * @throws \Exception
     */
    public function write()
    {
        if ($this->message) {
            $umask = umask(0);

            /* @var \DateTime $date */
            $date = new DateTime('now');

            switch ($this->level) {
                case self::ALERT:
                    $level = sprintf('ALERT (%s)', $this->level);
                    break;
                case self::CRIT:
                    $level = sprintf('CRIT (%s)', $this->level);
                    break;
                case self::ERR:
                    $level = sprintf('ERR (%s)', $this->level);
                    break;
                case self::WARN:
                    $level = sprintf('WARN (%s)', $this->level);
                    break;
                case self::NOTICE:
                    $level = sprintf('NOTICE (%s)', $this->level);
                    break;
                case self::INFO:
                    $level = sprintf('INFO (%s)', $this->level);
                    break;
                case self::DEBUG:
                    $level = sprintf('DEBUG (%s)', $this->level);
                    break;
            }

            $message = sprintf("%s %s: %s\r\n", $date->format('Y-m-d\TH:i:s'), $level, $this->message);

            if (!is_dir($this->logDir)) {
                mkdir($this->logDir, 0777, true);
            }

            if (!is_writeable($this->logDir)) {
                throw new Exception($this->logDir . ' is not writeable!');
            }

            file_put_contents($this->logDir . DS . $this->logFile, $message, FILE_APPEND);

            if (!is_writeable($this->logDir . DS . $this->logFile)) {
                chmod($this->logDir . DS . $this->logFile,
                    fileperms($this->logDir . DS . $this->logFile) | 128 + 16 + 2);
            }

            umask($umask);
        }

        return $this;
    }

    /**
     * Write exception to logfile
     *
     * @param \Exception $e
     *
     * @return $this
     * @throws \Exception
     */
    public function writeException(Exception $e)
    {
        $msg = sprintf("%s\r\n", $e->getMessage());

        foreach ($e->getTrace() as $trace) {
            if (array_key_exists('file', $trace)) {
                $msg .= sprintf("%s:%s %s\r\n", $trace['file'], $trace['line'], $trace['function']);
            }
        }

        $this->setLevel(self::CRIT);
        $this->setMessage($msg);
        $this->setLogfile('exception.log');

        $this->write();

        return $this;
    }
}