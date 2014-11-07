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

class Email extends \Jcode\Application\Helper
{

    protected $_sender = [];

    protected $_receiver = [];

    protected $_bcc = [];

    protected $_cc = [];

    protected $_attachments = [];

    protected $_subject;

    protected $_separator;

    protected $_template;

    protected $_vars;

    protected $_renderedEmail;

    protected $_log;

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    /**
     * @var \Jcode\Validator
     */
    protected $_validator;

    /**
     * @param \Jcode\Translate\Phrase $phrase
     */
    public function __construct(
        \Jcode\Translate\Phrase $phrase,
        \Jcode\Validator $validator,
        \Jcode\Log $log,
        \Jcode\Application\ConfigSingleton $config,
        \Jcode\DependencyContainer $dc
    ) {
        parent::__construct($phrase);

        $this->_validator = $validator;
        $this->_separator = md5(time());
        $this->_log = $log;
        $this->_config = $config;
        $this->_dc = $dc;
    }

    /**
     * Set emailsubject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;

        return $this;
    }

    /**
     * Set email sender
     *
     * @param array $sender
     * @return $this
     * @throws \Exception
     */
    public function setSender(array $sender)
    {
        if (array_key_exists('name', $sender) && array_key_exists('email', $sender)) {
            if ($this->_validator->validate('email', $sender['email'])) {
                $this->_sender = $sender;
            }
        }

        return $this;
    }

    /**
     * Set receiver information
     *
     * @param array $receiver
     * @return $this
     * @throws \Exception
     */
    public function setReceiver(array $receiver)
    {
        if (array_key_exists('name', $receiver) && array_key_exists('email', $receiver)) {
            if ($this->_validator->validate('email', $receiver['email'])) {
                $this->_receiver = $receiver;
            }
        }

        return $this;
    }

    /**
     * Set (HTML) email template
     *
     * @param $template
     * @param array $vars
     * @return $this
     */
    public function setTemplate($template, $vars = array())
    {
        $this->_template = $template;
        $this->_vars = $vars;

        return $this;
    }

    /**
     * Add BCC to email
     *
     * @param array $bcc
     * @return $this
     */
    public function addBcc(array $bcc)
    {
        if (array_key_exists('name', $bcc) && array_key_exists('email', $bcc)) {
            if ($this->_validator->validate('email', $bcc['email'])) {
                array_push($this->_bcc, $bcc);
            }
        }

        return $this;
    }

    /**
     * Add CC to email
     *
     * @param array $cc
     * @return $this
     */
    public function addCc(array $cc)
    {
        if (array_key_exists('name', $cc) && array_key_exists('email', $cc)) {
            if ($this->_validator->validate('email', $cc['email'])) {
                array_push($this->_cc, $cc);
            }
        }

        return $this;
    }

    /**
     * Add attachment to email
     *
     * @param $file
     * @param $filename
     * @return $this
     */
    public function addAttachment($file, $filename)
    {
        if (file_exist($file)) {
            array_push($this->_attachments, ['file' => $file, 'filename' => $filename]);
        }

        return $this;
    }

    public function send()
    {
        if ($this->isValid()) {
            $headers = sprintf("From: %s <%s>%s", $this->_sender['name'], $this->_sender['email'], PHP_EOL);
            $headers .= sprintf("Reply-To: %s <%s>%s", $this->_sender['name'], $this->_sender['email'], PHP_EOL);
            $headers .= sprintf("X-Mailer: PHP%s%s", phpversion(), PHP_EOL);

            if (!empty($this->_bcc)) {
                $bccs = array_map(function ($bcc) {
                    return sprintf('%s <%s>', $bcc['name'], $bcc['email']);
                }, $this->_bcc);

                $headers .= sprintf('Bcc: %s', implode(';', $bccs));
            }

            if (!empty($this->_cc)) {
                $ccs = array_map(function ($cc) {
                    return sprintf('%s <%s>', $cc['name'], $cc['email']);
                }, $this->_cc);

                $headers .= sprintf('Bcc: %s', implode(';', $ccs));
            }

            $headers .= sprintf("Content-Type: multipart/mixed; boundary=\"PHP-mixed-%s\"%s", $this->_separator, PHP_EOL);
            $headers .= sprintf("Content-Type: multipart/alternative; boundary=\"PHP-alt-%s\"%s", $this->_separator,PHP_EOL);

            $body = sprintf("--PHP-mixed-%s%s", $this->_separator, PHP_EOL);

            $body .= sprintf("--PHP-alt-%s%s", $this->_separator, PHP_EOL);
            $body .= sprintf("Content-Type: text/html; charset=\"iso-8859-1\"%s", PHP_EOL);
            $body .= sprintf("Content-Transfer-Encoding: 7bit%s", PHP_EOL);

            $template = $this->_getFlow()->load($this->_template);

            ob_start();

            $template->display($this->_vars);

            $html = ob_get_contents();

            ob_end_clean();

            $body .= $html.PHP_EOL.PHP_EOL;

            $body .= sprintf("--PHP-alt-%s--%s", $this->_separator, PHP_EOL);

            $body .= sprintf("--PHP-mixed-%s--%s", $this->_separator, PHP_EOL);

            return mail(sprintf('%s <%s>', $this->_receiver['name'], $this->_receiver['email']), $this->_subject, $body, $headers);
        }
    }

    protected function _getFlow()
    {
        $sourceDir = sprintf('%s/public/design/%s/template', BP, $this->_config->getDesign()->getLayout());
        $targetDir = sprintf('%s/public/var/cache', BP);

        $flow = $this->_dc->get('Flow\Loader',[
            'source' => $sourceDir,
            'target' => $targetDir,
            'mode' => \Flow\Loader::RECOMPILE_ALWAYS
        ]);

        return $flow;
    }

    /**
     * Preview a rendered version of the email
     *
     * @return mixed
     */
    public function getRenderedTemplate()
    {
        $template = $this->_getFlow()->load($this->_template);

        return $template->display($this->_vars);
    }

    /**
     * Validate emailobject for the necesary elements
     *
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->_sender)) {
            $this->_log->write('Invalid emailobject: No sender set', \Jcode\Log::ERR);

            return false;
        }

        if (empty($this->_receiver)) {
            $this->_log->write('Invalid emailobject: No receiver set', \Jcode\Log::ERR);

            return false;
        }

        if (!$this->_subject) {
            $this->_log->write('Invalid emailobject: No subject set', \Jcode\Log::ERR);

            return false;
        }

        if (!$this->_template) {
            $this->_log->write('Invalid emailobject: No template set', \Jcode\Log::ERR);
        } else {
            $design = $this->_config->getDesign()->getLayout();
            $template = sprintf('%s/public/design/%s/template/%s', BP, $design, $this->_template);

            if (!file_exists($template)) {
                $this->_log->write($this->translate('Invalid emailobject: Template is set, but does not exists: %s'),
                    $template);

                return false;
            }
        }

        return true;
    }
}