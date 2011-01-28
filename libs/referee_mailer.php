<?php

App::import('Component', 'Email');
App::import('View', 'View');
App::import('Core', 'Controller');

/**
 * "Fake" controller class to make EmailComponent happy
 *
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @uses    Controller
 */
class RefereeMailerController extends Controller {}

/**
 * RefereeMailer
 *
 * Overridden to support plugin templates/layouts for mail messages.
 *
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @uses    EmailComponent
 */
class RefereeMailer extends EmailComponent {

	/**
	 * @var View
	 */
	private $_view;

	/**
	 * Constructor
	 *
	 * @param   array $config
	 * @return  void
	 */
	public function __construct($config = array()) {
		if (Configure::read('App.encoding') !== null) {
			$this->charset = Configure::read('App.encoding');
		}
		$this->_set($config);
		$this->_view = new View(new RefereeMailerController(), false);
		$this->_view->layout = $this->layout;
		$this->_view->plugin = 'Referee';
	}

	/**
	 * Render the contents using the current layout and template.
	 * Overridden to support plugin templates
	 *
	 * @param   string  $content Content to render
	 * @return  array   Email ready to be sent
	 */
	public function _render($content) {
		$msg = array();
		$content = implode("\n", $content);
		if ($this->sendAs === 'both') {
			$htmlContent = $content;
			if (!empty($this->attachments)) {
				$msg[] = '--' . $this->__boundary;
				$msg[] = 'Content-Type: multipart/alternative; boundary="alt-' . $this->__boundary . '"';
				$msg[] = '';
			}
			$msg[] = '--alt-' . $this->__boundary;
			$msg[] = 'Content-Type: text/plain; charset=' . $this->charset;
			$msg[] = 'Content-Transfer-Encoding: 7bit';
			$msg[] = '';
			$content = $this->_view->element('email' . DS . 'text' . DS . $this->template, array(
				'content' => $content,
				'plugin' => 'Referee'
			), true);
			$this->_view->layoutPath = 'email' . DS . 'text';
			$content = explode("\n", $this->textMessage = str_replace(array("\r\n", "\r"), "\n", $this->_view->renderLayout($content)));
			$msg = array_merge($msg, $content);
			$msg[] = '';
			$msg[] = '--alt-' . $this->__boundary;
			$msg[] = 'Content-Type: text/html; charset=' . $this->charset;
			$msg[] = 'Content-Transfer-Encoding: 7bit';
			$msg[] = '';
			$htmlContent = $this->_view->element('email' . DS . 'html' . DS . $this->template, array(
				'content' => $htmlContent,
				'plugin' => 'Referee'
			), true);
			$this->_view->layoutPath = 'email' . DS . 'html';
			$htmlContent = explode("\n", $this->htmlMessage = str_replace(array("\r\n", "\r"), "\n", $this->_view->renderLayout($htmlContent)));
			$msg = array_merge($msg, $htmlContent);
			$msg[] = '';
			$msg[] = '--alt-' . $this->__boundary . '--';
			$msg[] = '';
			return $msg;
		}
		if (!empty($this->attachments)) {
			if ($this->sendAs === 'html') {
				$msg[] = '';
				$msg[] = '--' . $this->__boundary;
				$msg[] = 'Content-Type: text/html; charset=' . $this->charset;
				$msg[] = 'Content-Transfer-Encoding: 7bit';
				$msg[] = '';
			} else {
				$msg[] = '--' . $this->__boundary;
				$msg[] = 'Content-Type: text/plain; charset=' . $this->charset;
				$msg[] = 'Content-Transfer-Encoding: 7bit';
				$msg[] = '';
			}
		}
		$content = $this->_view->element('email' . DS . $this->sendAs . DS . $this->template, array(
			'content' => $content,
			'plugin' => 'Referee'
		), true);
		$this->_view->layoutPath = 'email' . DS . $this->sendAs;
		$content = explode("\n", $rendered = str_replace(array("\r\n", "\r"), "\n", $this->_view->renderLayout($content)));
		if ($this->sendAs === 'html') {
			$this->htmlMessage = $rendered;
		} else {
			$this->textMessage = $rendered;
		}
		$msg = array_merge($msg, $content);
		return $msg;
	}

}

?>