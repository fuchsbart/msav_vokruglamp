<?php

require_once (dirname(__FILE__) . '/msav_vokruglamp_xml.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_image.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_category.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_manufacturer.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_feature.php');
require_once (dirname(__FILE__) . '/msav_vokruglamp_product.php');

if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_Helper {

	/** Maximum rows offset. */
	const TASK_OFFSET = 10;

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
	 * Product XML feed rows offset.
	 * @var int
	 */
	public $product_offset;

	/**
	 * Msav_VokrugLamp_Helper constructor.
	 */
	public function __construct() {
		if (isset($_GET['offset'])) {
			$this->product_offset = (int)$_GET['offset'];
		} else {
			$this->product_offset = -1;
		}
	}

	/**
	 * Start the products import.
	 *
	 * @return void
	 */
	public function do_import() {
		$this->xml_file = new Msav_VokrugLamp_XML();

		if ($this->xml_file->success) {
			// Start the main task
			if ($this->product_offset == -1 && $this->import_categories()) {

				// Start the subtasks
				if ($curl = curl_init()) {
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);

					$current_offset = 0;
					$id_shop        = Configuration::get( 'PS_SHOP_DEFAULT' );
					$token          = substr( Tools::hash( 'msav_vokruglamp/cron' ), 0, 10 );
					while ( $current_offset != - 1 ) {
						$url = sprintf( '%smodules/msav_vokruglamp/msav_vokruglamp_cron.php?token=%s&id_shop=%d&offset=%d',
							Tools::getHttpHost( true ) . __PS_BASE_URI__,
							$token,
							$id_shop,
							$current_offset );
						//echo $url."\n";

						curl_setopt($curl, CURLOPT_URL, $url);

						$response = curl_exec($curl);
						if (curl_errno($curl)) {
							die(curl_error($curl));
						}

						$data = json_decode($response);
						if ($data !== null) {
							$current_offset = (int)$data->offset;
						} else {
							$current_offset = -1;
						}
						/*if ($current_offset > 50)
							die('zzz '.$current_offset);*/
					}

					curl_close($curl);
					$this->xml_file->delete_file();

				}
			} elseif ($this->fill_categories_list()) {
				$new_offset = $this->import_products();
				$result = array('offset' => $new_offset);
				echo json_encode($result);
			} else {
				$result = array('offset' => -1);
				echo json_encode($result);
			}
		}
	} // do_import

	/**
	 * Products import task.
	 *
	 * @return int
	 */
	private function import_products() {

		$updated = 0;

		$shop = $this->xml_file->get_xml();
		if ($shop == null) {
			die("XML Error");
		}

		// Prepare offsets
		$row_index = 0;
		$max_offset = $this->product_offset + self::TASK_OFFSET;

		// Process the products feed
		foreach ( $shop->shop->offers->offer as $offer ) {

			if ($row_index >= $this->product_offset && $row_index <= $max_offset) {
				/** @var Msav_VokrugLamp_Product Current product from offer node */
				$current_product = Msav_VokrugLamp_Product::get_from_xml( $offer );
				if ( $current_product != null && $current_product->update_product() ) {
					$updated ++;
					//die(print_r($current_product, true));
				}
				unset( $current_product );
			}

			if ($row_index == $max_offset) {
				break;
			} else {
				$row_index++;
			}
		}

		// Reset the row index if nothing anymore to import.
		if ($row_index < $max_offset) {
			$row_index = -1;
		}

		return $row_index;
	} // import_products

	/**
	 * Import product categories.
	 *
	 * @return bool
	 */
	private function import_categories() {

		$res = false;

		if ($this->fill_categories_list()) {
			// Update categories database records
			/** @var Msav_VokrugLamp_Category $item */
			foreach ( $this->categories_list as $item ) {
				$item->update_category();
			}
			$res = true;
		}

		return $res;

	}

	/**
	 * Fill the categories list of products.
	 *
	 * @return bool
	 */
	private function fill_categories_list() {

		$res = false;

		$shop = $this->xml_file->get_xml();
		if ($shop == null) {
			echo "XML Error";
		} else {

			// Fill the categories list
			$this->categories_list = array();
			foreach ( $shop->shop->categories->category as $category ) {
				$current_category = Msav_VokrugLamp_Category::get_from_xml( $category );
				if ( $current_category != null ) {
					$this->categories_list[] = $current_category;
				}
			}

			$res = true;
		}

		return $res;

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