<?php

App::import('Lib', 'Referee.RefereeListener');

/**
 * SyslogRefereeListener
 *
 * Provides functionality for logging errors to the system logger
 *
 * @author  Joe Beeson <jbeeson@gmail.com>
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @see     http://blog.joebeeson.com/monitoring-your-applications-health/
 * @uses    RefereeListener
 */
class SyslogRefereeListener extends RefereeListener {

	/**
	 * @var string
	 */
	public $ident = 'CakePHP Application';

	/**
	 * @var string
	 */
	public $format = 'Caught an %s error, "%s" in %s at line %s';

	/**
	 * Triggered when we're passed an error from the `WhistleComponent`
	 *
	 * @param   array $error
	 * @return  void
	 */
	public function error($error) {
		syslog(
			LOG_INFO,
			$this->ident . ': ' . sprintf(
				$this->format,
				$this->_translateError($error['level']),
				$error['message'],
				$error['file'],
				$error['line']
			)
		);
	}

}

?>