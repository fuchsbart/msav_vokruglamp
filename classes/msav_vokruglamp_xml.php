<?php

if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_XML {

	/**
	 * Local path of the XML file
	 * @var string
	 */
	public $local_path;

	/**
	 * Vendor URL of the products XML feed
	 * @var string
	 */
	public $remote_url;

	/**
	 * File is available
	 * @var bool
	 */
	public $success;

	/**
	 * Msav_VokrugLamp_XML constructor.
	 */
	public function __construct() {

		$this->reset();

		// Set the URL of the vendor products XML feed
		$this->remote_url = Configuration::get('MSAV_VOKRUGLAMP_VENDOR_XML');

		// Check the local copy
		if (file_exists($this->local_path)) {
			$this->success = true;
		} elseif ($this->remote_url != '') {
			// Download the remote file
			$this->success = copy($this->remote_url, $this->local_path) && file_exists($this->local_path);
		}

	}

	/**
	 * Returns a simple XML object
	 * @return null|SimpleXMLElement
	 */
	public function get_xml() {
		$res = null;

		if ($this->success && file_exists($this->local_path)) {
			$res = simplexml_load_file($this->local_path);
			if ($res === false)
				$res = null;
		}

		return $res;
	}

	/**
	 * Reset Properties
	 */
	public function reset() {
		$this->success = false;
		$this->local_path = _PS_UPLOAD_DIR_ . 'vokruglamp.xml';
		$this->remote_url = '';
	}

	/**
	 * Delete the XML file.
	 *
	 * @return bool
	 */
	public function delete_file() {
		$res = false;
		if ($this->success && file_exists($this->local_path) && unlink($this->local_path)) {
			$res = true;
		}
		return $res;
	} // delete_file

}