<?php

require_once (dirname(__FILE__) . '/msav_vokruglamp_xml.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_category.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_manufacturer.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_feature.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_product.php');

if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_Helper {

	/**
	 * Represents the current class global instance.
	 *
	 * @var Msav_VokrugLamp_Helper
	 */
	private static $INSTANCE = null;

	/**
	 * Current XML file
	 * @var Msav_VokrugLamp_XML
	 */
	public $xml_file;

	/**
	 * Vendor categories list
	 * @var array
	 */
	public $categories_list;

	/**
	 * Start the products import.
	 *
	 * @return void
	 */
	public function do_import() {
		$this->xml_file = new Msav_VokrugLamp_XML();

		if ($this->xml_file->success) {
			$this->import_categories();
			$this->import_products();
		}
	}

	private function import_products() {
		$shop = $this->xml_file->get_xml();
		if ($shop == null) {
			die("XML Error");
		}

		// Process the products feed
		foreach ( $shop->shop->offers->offer as $offer ) {

		}
	}

	private function import_categories() {
		$shop = $this->xml_file->get_xml();
		if ($shop == null) {
			die("XML Error");
		}

		// Fill the categories list
		$this->categories_list = array();
		foreach ( $shop->shop->categories->category as $category ) {
			$current_category = Msav_VokrugLamp_Category::get_from_xml($category);
			if ($current_category != null)
				$this->categories_list[] = $current_category;
		}

		// Update categories database records
		/** @var Msav_VokrugLamp_Category $item */
		foreach ( $this->categories_list as $item ) {
			$item->update_category();
		}

	}

	/**
	 * Returns the current class global instance.
	 *
	 * @return Msav_VokrugLamp_Helper
	 */
	public static function get_instance() {
		if (self::$INSTANCE == null) {
			self::$INSTANCE = new Msav_VokrugLamp_Helper();
		}
		return self::$INSTANCE;
		// _PS_UPLOAD_DIR_
	}
}