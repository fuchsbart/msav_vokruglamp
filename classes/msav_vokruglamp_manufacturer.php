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
	 * @var Manufacturer
	 */
	protected $db_manufacturer;

	/**
	 * Msav_VokrugLamp_Manufacturer constructor.
	 *
	 * @param string $_name
	 */
	public function __construct($_name = '') {
		$this->name = $_name;
		$this->db_manufacturer = null;
	}

	/**
	 * Returns Manufacturer object
	 *
	 * @return Manufacturer
	 */
	public function get_db_manufacturer() {

		if ($this->db_manufacturer == null && $this->name != '') {
			$id = Manufacturer::getIdByName($this->name);
			if ($id === false) {
				// Add new Manufacturer
				$this->db_manufacturer = new Manufacturer();
				$this->db_manufacturer->name = $this->name;
				try {
					$this->db_manufacturer->add();
					if ($this->db_manufacturer === false) {
						$this->db_manufacturer = null;
					}
				} catch ( Exception $e ) { $this->db_manufacturer = null; }
			} else {
				$this->db_manufacturer = new Manufacturer($id);
			}
		}

		return $this->db_manufacturer;
	} // get_db_manufacturer
}