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
	 * @return int|bool
	 */
	public function get_database_id() {
		$res = false;

		if ($this->db_id == -1 && $this->sku != '') {

			$lang_id = Configuration::get('PS_LANG_DEFAULT');
			$db_products = Product::searchByName($lang_id, $this->name);
			if (is_array($db_products) && count($db_products) > 0) {
				$this->db_id = $db_products[0]['id_product'];
				$res = $this->db_id;
			}

		} elseif ($this->db_id > 0) {
			$res = $this->db_id;
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

		$lang_id = Configuration::get('PS_LANG_DEFAULT');

		/** @var Product $product */
		$product = null;

		// Get database ID
		if ($this->db_id == -1) {
			if ($this->get_database_id()) {
				$product = new Product($this->db_id, true);
				$modified = false;

				// Set the price
				if ($product->price != (float)$this->price) {
					$product->price = $this->price;
					$modified = true;
				}

				// Try to update the product
				if ($modified) {
					try { $product->save(); }
					catch (Exception $exception) { }
				}

				// Set product stock
				if ($product->quantity != $this->stock) {
					StockAvailable::setQuantity($product->id, null, $this->stock);
				}

				$res = true;

				//die(print_r($product, true));
			} else {
				$product = new Product();

				$product->name = array((int)$lang_id => $this->name);
				$product->meta_description = array((int)$lang_id => $this->name);
				$product->meta_title = array((int)$lang_id => $this->name);
				$product->reference = $this->sku;
				$product->description = array((int)$lang_id => $this->get_description_html());
				$product->description_short = array((int)$lang_id => '<p>'.$this->name.'</p>');

				$product->tax_rate = 0;
				$product->tax_name = 'deprecated';

				$product->id_supplier = 1;
				$product->supplier_name = 'VKL';

				if ($this->manufacturer != null && $this->manufacturer->name != '') {
					$product->manufacturer_name = $this->manufacturer->name;
					$product->id_manufacturer = $this->manufacturer->get_db_manufacturer()->id;
				}

				// Set default product category
				if ($this->category != null && $this->category->name != '') {
					$category_id = $this->category->get_database_id();
					if ($category_id !== false) {
						$product->id_category_default = $category_id;
					}
				}

				$product->on_sale = 0;
				$product->show_price = true;
				$product->indexed = true;
				$product->visibility = 'both';
				$product->price = $this->price;

				$product->quantity = $this->stock;
				$product->available_for_order = true;
				$product->condition = 'new';
				$product->available_now = array((int)$lang_id => 'В Наличии');
				$product->available_later = array((int)$lang_id => 'Под заказ');

				$product->checkDefaultAttributes();

				try { $prod_id = $product->save(); }
				catch (Exception $ex) { $prod_id = false; }

				if ($prod_id !== false) {
					// Set product stock
					StockAvailable::setQuantity($product->id, null, $this->stock);

					// Set product images
					/** @var Msav_VokrugLamp_Image $image */
					foreach ( $this->images as $image ) {
						$image->copy_image($product->id, $product->name);
					}

					$res = true;
				}

				//die(print_r($product, true));
			}
			unset($product);
		}

		return $res;
	}

	/**
	 * Returns the HTML description text.
	 *
	 * @return string
	 */
	public function get_description_html() {
		$res = '';

		if (count($this->params) > 0) {
			foreach ( $this->params as $param ) {
				$res .= sprintf('<li><b>%s:</b> %s</li>', $param['name'], $param['value']);
			}

			if ($res != '') {
				$res = sprintf('<h3>Характеристики</h3><ul>%s</ul>', $res);
			}
		}

		return $res;
	} // get_description_html

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
			if (!empty($image_url)) {
				$cur_image = Msav_VokrugLamp_Image::create_image($image_url);
				if ($cur_image != null) {
					$res->images[] = $cur_image;
				}
			}
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