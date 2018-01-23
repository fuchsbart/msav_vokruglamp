<?php
if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_Product {

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
	 * Vendor category Id
	 * @var int
	 */
	public $category_id;

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
		$this->id = '';
		$this->name = '';
		$this->sku = '';
		$this->manufacturer = null;
		$this->vendor_url = '';
		$this->currency = '';
		$this->images = array();
		$this->category_id = -1;
		$this->price = 0;
		$this->old_price = 0;
		$this->params = array();
		$this->stock = 0;
	}

}