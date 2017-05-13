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
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @copyright   Copyright (c) 2017 J!Code (http://www.jcode.nl)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Data\Form;

use \Jcode\Layout\Resource\Template;

class Container extends Template
{

    protected $template = 'Jmod_Cms::Form/Container.phtml';

    /**
     * @inject \Jcode\Data\Form
     * @var \Jcode\Data\Form
     */
    protected $form;

    protected function prepareForm()
    {

    }

    public function getForm($id, array $options = [])
    {
        $form = $this->form;

        $form->setId($id);

        if (array_key_exists('method', $options)) {
            $form->setMethod($options['method']);
        }

        if (array_key_exists('action', $options)) {
            $form->setAction($options['action']);
        }

        if (array_key_exists('enctype', $options)) {
            $form->setEncType($options['enctype']);
        }

        return $this->form;
    }

    public function render()
    {
        $this->prepareForm();

        parent::render();

        return $this;
    }

    public function getPreparedForm()
    {
        return $this->form;
    }

}