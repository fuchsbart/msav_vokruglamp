<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Class Msav_VokrugLamp_Manufacturer
 */
class Msav_VokrugLamp_Manufacturer {

	/**
	 * Manufacturer/Brand/Vendor name
	 * @var string
	 */
	public $name;

	/**
	 * Msav_VokrugLamp_Manufacturer constructor.
	 *
	 * @param string $_name
	 */
	public function __construct($_name = '') {
		$this->name = $_name;
	}
}