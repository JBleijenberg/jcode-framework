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
define('BP', dirname(dirname(dirname(__FILE__))));


if (!file_exists(BP.'/application/application.json')) {
    $content = [
        'application' => [
            'layout' => 'default',
            'base_url' => sprintf('//%s', $_POST['application_url']),
            'default_router' => '',
            'encryption_key' => generateKey(),
            'title' => $_POST['application_name'],
            'database' => $_POST['database'],
            'cache' => ['enabled' => false],
        ],
    ];

    if (is_writable(BP.'/application')) {
        $umask = umask(0);

        file_put_contents(BP.'/application/application.json', json_encode($content, JSON_PRETTY_PRINT));

        umask($umask);

        echo '<div data-alert class="alert-box success">
              Your application.json has been created with the following information.
              <a href="#" class="close">&times;</a>
            </div>';
    } else {
        echo '<div data-alert class="alert-box warning">
              Your application folder is not writeable. Please create a application.json file with the following content.
              <a href="#" class="close">&times;</a>
            </div>';
    }

    require_once 'after-create.phtml';
} else {
    header('Location: /');
}

function generateKey()
{
    $chars = sprintf('%s%s%s', implode('', range('a', 'z')), implode('', range(0,9)),implode('', range('A', 'Z')));

    $salt = '';

    for ($i = 0; $i < 32; $i++) {
        $salt .= $chars[rand(0, strlen($chars)-1)];
    }

    return (string)$salt;
}