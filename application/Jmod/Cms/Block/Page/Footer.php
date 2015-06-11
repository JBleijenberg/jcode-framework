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
namespace Jmod\Cms\Block\Page;

use Jcode\Application;
use Jcode\Application\Environment;
use Jcode\Layout\Resource\Template;

class Footer extends Template
{

    protected $jsFiles = [];

    /**
     * Return all added JS files
     * @return array
     */
    public function getJsFiles()
    {
        $html = '';

        foreach ($this->jsFiles as $file) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . Application::getBaseUrl(Environment::URL_TYPE_JS) . '/' . $file . '">';
        }

        return $html;
    }

    /**
     * @param $file
     *
     * @return $this
     */
    public function addJs($file)
    {
        $this->jsFiles[] = $file;

        return $this;
    }
}