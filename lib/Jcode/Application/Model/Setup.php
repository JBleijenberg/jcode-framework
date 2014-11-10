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
namespace Jcode\Application\Model;

class Setup
{

    /**
     * @var \Jcode\Application\ConfigSingleton
     */
    protected $_config;

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    protected $_resourceFile;

    /**
     * @var \Jcode\Log
     */
    protected $_log;

    public function __construct(
        \Jcode\Application\ConfigSingleton $config,
        \Jcode\DependencyContainer $dc,
        \Jcode\Log $log
    ) {
        $this->_config = $config;
        $this->_log = $log;
        $this->_dc = $dc;
        $this->_resourceFile = sprintf('%s/public/var/version_config.json', BP);

        if (!file_exists($this->_resourceFile)) {
            if (!is_dir(sprintf('%s/public/var', BP))) {
                mkdir(sprintf('%s/public/var/', BP), 0755, true);
            }

            $content = [
                'application' => [
                    'title' => $config->getWeb()->getTitle(),
                    'url' => $config->getWeb()->getBaseUrl(),
                    'modules' => [

                    ],
                ],
            ];

            file_put_contents($this->_resourceFile, json_encode($content, JSON_PRETTY_PRINT));
        }
    }

    public function run()
    {
        foreach ($this->_config->getModules() as $module) {
            $this->processModule($module);
        }
    }

    public function processModule($module)
    {
        $versionJson = json_decode(file_get_contents($this->_resourceFile), true);

        if (!$module->getSetup() || !$module->getSetup()->getClass()) {
            $setupClass = $this->_dc->get(get_class($this));
        } else {
            $setupClass = $this->dc->get($module->getSetup()->getClass());
        }

        $firstRun = array_map(function ($version) use ($module) {
            return !array_key_exists($module->getName(), $version);
        }, $versionJson['application']['modules']);

        if (empty($firstRun)) {
            /**
             * Module doesn't exist yet. This means run the first install/setup script
             */
            $setupFile = current(glob(sprintf('%s/Setup/install-*.php', $module->getModulePath())));

            if (!$setupFile) {
                return false;
            }

            if (($versionAfterUpgrade = $setupClass->processSetupFile($setupFile))) {
                $moduleVersion = [$module->getName() => $versionAfterUpgrade];

                array_push($versionJson['application']['modules'], $moduleVersion);

                file_put_contents($this->_resourceFile, json_encode($versionJson, JSON_PRETTY_PRINT));

                /**
                 * Keep running the process until version is up-to-date
                 */
                $this->processModule($module);
            }
        } else {
            /**
             * Module exists. Run update scripts until the module version matches the current version
             */
            $currentVersion = current(array_map(function ($version) use ($module) {
                return (key($version) == $module->getName()) ? $version : false;
            }, $versionJson['application']['modules']));

            $setupFile = current(glob(sprintf('%s/Setup/upgrade-%s-*.php', $module->getModulePath(), current($currentVersion))));

            if ($setupFile) {
                preg_match('/(?<=-)([\d\.]*)(?=\.php)/', $setupFile, $matches);

                /**
                 * Update script updates to a version lower than or equal to the current version.
                 * We keep running the scripts until everything is fully up-to-date
                 */
                if (current($matches) <= $module->getVersion()) {
                    if (($versionAfterUpgrade = $setupClass->processSetupFile($setupFile))) {
                        array_walk($versionJson['application']['modules'], function(&$arg)use($module, $versionAfterUpgrade){
                            if (key($arg) == $module->getName()) {
                                $arg[key($arg)] = $versionAfterUpgrade;
                            }
                        });

                        file_put_contents($this->_resourceFile, json_encode($versionJson, JSON_PRETTY_PRINT));

                        /**
                         * Keep running the process until version is up-to-date
                         */
                        $this->processModule($module);
                    }
                }
            }
        }

        //file_put_contents($this->_resourceFile, json_encode($versionJson, JSON_PRETTY_PRINT));
    }

    public function processSetupFile($file)
    {
        try {
            require_once $file;

            preg_match('/(?<=-)([\d\.]*)(?=\.php)/', $file, $matches);

            if (empty($matches)) {
                throw new \Exception('Invalid versioning in setup filename');
            }

            return current($matches);
        } catch (\Exception $e) {
            $this->_log->writeException($e);

            throw new \Exception($e->getMessage());
        }
    }
}