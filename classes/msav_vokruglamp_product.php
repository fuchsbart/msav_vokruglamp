<?php
if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_Product {

	/**
	 * The database product Id
	 * @var int
	 */
	private $db_id;

	/**
	 * Vendor Id. The field can take absolutely unexpected values. There is no logic for assigning values to the field.
	 * @var mixed
	 */
	public $id;

	/**
	 * Product Name
	 * @var string
	 */
	public $name;

	/**
	 * Brand / Manufacturer
	 * @var Msav_VokrugLamp_Manufacturer|null
	 */
	public $manufacturer;

	/**
	 * Vendor SKU
	 * @var string
	 */
	public $sku;

	/**
	 * Vendor URL
	 * @var string
	 */
	public $vendor_url;

	/**
	 * Currency Symbol
	 * @var string
	 */
	public $currency;

	/**
	 * Images list
	 * @var array
	 */
	public $images;

	/**
	 * Product Category
	 * @var Msav_VokrugLamp_Category
	 */
	public $category;

	/**
	 * Current price
	 * @var float
	 */
	public $price;

	/**
	 * Old price
	 * @var float
	 */
	public $old_price;

	/**
	 * Params list
	 * @var array
	 */
	public $params;

	/**
	 * Stock of Products
	 * @var int
	 */
	public $stock;

	/**
	 * Msav_VokrugLamp_Product constructor.
	 */
	public function __construct() {
		$this->id               = '';
		$this->db_id            = -1;
		$this->name             = '';
		$this->sku              = '';
		$this->manufacturer     = null;
		$this->vendor_url       = '';
		$this->currency         = '';
		$this->images           = array();
		$this->category         = null;
		$this->price            = 0;
		$this->old_price        = 0;
		$this->params           = array();
		$this->stock            = 0;
	}

	/**
	 * Extract product database Id
	 *
	 * @return bool
	 */
	public function get_database_id() {
		$res = false;

		if ($this->db_id == -1 && $this->id > 0 && $this->name != '') {
			$lang_id = Configuration::get('PS_LANG_DEFAULT');
			$db_products = Product::searchByName($lang_id, $this->name);
			if (is_array($db_products) && count($db_products) > 0) {
				$res = true;
				$this->db_id = $db_products[0]['id_product'];
			}
		}

		return $res;
	}

	/**
	 * Update product information
	 *
	 * @return bool
	 */
	public function update_product() {
		$res = false;

		/** @var Product $product */
		$product = null;

		// Get database ID
		if ($this->db_id == -1) {
			if ($this->get_database_id()) {
				$product = new Product($this->db_id, true);
				die(print_r($product, true));
			} else {
				$product = new Product();
				$product->checkDefaultAttributes();
				die(print_r($product, true));
			}
		}

		return $res;
	}

	/**
	 * Creating a product object from an XML element.
	 *
	 * @param SimpleXMLElement $xml_product
	 *
	 * @return Msav_VokrugLamp_Product|null
	 */
	public static function get_from_xml($xml_product) {

		$res = new Msav_VokrugLamp_Product();

		// Vendor internal product Id
		if (isset($xml_product->attributes()['id']))
			$res->id = (string)$xml_product->attributes()['id'];

		// Min stock amount
		if (isset($xml_product->attributes()['available']) && (string)$xml_product->attributes()['available'] == 'true')
			$res->stock = 1;

		// Product Name
		$res->name = (string)$xml_product->name;

		// First Vendor name (WTF: Why is there a duplicate manufacturer/vendor/brand (see below)?)
		$vendor = (string)$xml_product->vendor;
		if (!empty($vendor))
			$res->manufacturer = new Msav_VokrugLamp_Manufacturer($vendor);

		// Currency symbol code
		$res->currency = (string)$xml_product->currencyId;

		// Product Category
		$category_id = (int)$xml_product->categoryId;
		if ($category_id > 0)
			$res->category = Msav_VokrugLamp_Category::get_by_vendor_id($category_id);

		// Main Price
		$res->price = (float)$xml_product->price;

		// Vendor product URL
		$res->vendor_url = (string)$xml_product->url;

		// Read the images list
		foreach ( $xml_product->image as $value ) {
			$image_url = (string)$value;
			if (!empty($image_url))
				$res->images[] = $image_url;
		}

		// Read the properties list
		foreach ( $xml_product->param as $value ) {
			$prop_value = (string)$value;
			$prop_name = (string)$value->attributes()['name'];
			switch ($prop_name) {
				case 'Артикул':
					$res->sku = $prop_value;
					break;
				case 'Бренд':
					$res->manufacturer = new Msav_VokrugLamp_Manufacturer($prop_value);
					break;
				case 'Остаток поставщика':
					$res->stock = (int)$prop_value;
					break;
				case 'Старая цена':
					$res->old_price = (float)$prop_value;
					break;
				case 'Акция':
				case 'Автоматическая сортировка':
				case 'Раздел на сайте':
					// Ignoring these properties
					break;
				default:
					$res->params[] = array(
						'name'  => $prop_name,
						'value' => $prop_value
					);
					break;
			}
		}

		return $res;
	}

}