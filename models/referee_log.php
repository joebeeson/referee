<?php

/**
 * @package     referee
 * @subpackage  referee.models
 */
class RefereeLog extends RefereeAppModel {

	/**
	 * @var string
	 */
	public $name = 'RefereeLog';

	/**
	 * @var array
	 */
	public $validate = array(
		'level' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		),
		'file' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		),
		'line' => array(
			'numeric' => array(
				'rule' => array('numeric')
			)
		)
	);

}

?>