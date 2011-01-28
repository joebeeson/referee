<?php

App::import('Lib', 'Referee.RefereeListener');
App::import('Lib', 'Referee.RefereeMailer');

/**
 * EmailRefereeListener
 *
 * Provides functionality for logging errors to email.
 *
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @uses    RefereeListener
 */
class EmailRefereeListener extends RefereeListener {

	/**
	 * @var RefereeMailer
	 */
	protected $_mailer;

	/**
	 * Level to log errors at
	 *
	 * @var integer
	 */
	public $errorLevels = E_FATAL;

	/**
	 * @var array
	 */
	public $mailerConfig = array(
		'to' => null,
		'cc' => array(),
		'bcc' => array(),
		'replyTo' => null,
		'return' => null,
		'from' => 'referee@localhost',
		'subject' => 'Referee Notification',
		'template' => 'default',
		'layout' => 'default',
		'lineLength' => 180,
		'sendAs' => 'text',
		'attachments' => array(),
		'delivery' => 'mail',
		'smtpOptions' => array(
			'port' => 25,
			'host' => '127.0.0.1',
			'timeout' => 30,
			'username' => null,
			'password' => null,
			'client' => null
		)
	);

	/**
	 * Constructor
	 *
	 * Overridden to instantiate custom mailer component
	 *
	 * @param   array $config
	 * @return  void
	 */
	public function __construct($config = array()) {
		$config['mailerConfig'] = array_merge($this->mailerConfig, $config['mailerConfig']);
		parent::__construct($config);
		$this->_mailer = new RefereeMailer($this->mailerConfig);
	}


	/**
	 * @param   array $error
	 * @return  void
	 */
	public function error($error) {
		$error['level'] = $this->_translateError($error['level']);
		$this->_mailer->subject .= ': '.$error['level'];
		$this->_mailer->send(print_r($error, true));
	}

}

?>