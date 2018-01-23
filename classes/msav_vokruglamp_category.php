<?php
if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_Category {

	/**
	 * Vendor Category Id
	 * @var int
	 */
	public $id;

	/**
	 * Vendor parent Category Id
	 * @var int
	 */
	public $parent_id;

	/**
	 * Category Name
	 * @var string
	 */
	public $name;

	/**
	 * The database category Id
	 * @var int
	 */
	private $db_id;

	public function get_database_id() {
		// Extract database Id or add new category
		if ($this->db_id == -1 && $this->id > 0 && $this->name != '') {
			$lang_id = Configuration::get('PS_LANG_DEFAULT');
			if ($this->parent_id == -1)
				$db_parent = Configuration::get('PS_HOME_CATEGORY');
			else
				$db_parent = $this->get_parent_category()->get_database_id();
			$db_categories = Category::searchByNameAndParentCategoryId($lang_id, $this->name, $db_parent);
			if (is_array($db_categories) && count($db_categories) > 0) {
				$this->db_id = (int)$db_categories['id_category'];
			}
		}

		return $this->db_id;
	}

	/**
	 * Msav_VokrugLamp_Category constructor.
	 *
	 * @param int $_id
	 * @param string $_name
	 * @param int $_parent_id
	 */
	public function __construct($_id = -1, $_name = '', $_parent_id = -1) {
		$this->id = $_id;
		$this->parent_id = $_parent_id;
		$this->name = $_name;
		$this->db_id = -1;
	}

	/**
	 * Creating a category object from an XML element.
	 *
	 * @param SimpleXMLElement $element
	 *
	 * @return null|Msav_VokrugLamp_Category
	 */
	public static function get_from_xml($element) {
		if (@isset($element->attributes()['id']))
			$id = (int)$element->attributes()['id'];
		else
			$id = -1;

		if (@isset($element->attributes()['parentId']))
			$parent_id = (int)$element->attributes()['parentId'];
		else
			$parent_id = -1;

		$name = (string)$element;

		if ($id > 0 && $name != "")
			$res = new Msav_VokrugLamp_Category($id, $name, $parent_id);
		else
			$res = null;

		return $res;
	}

	/**
	 * Search for a category by vendor ID.
	 *
	 * @param int $_id
	 *
	 * @return Msav_VokrugLamp_Category|null
	 */
	public static function get_by_vendor_id($_id) {
		$res = null;

		/** @var Msav_VokrugLamp_Category $item */
		foreach ( Msav_VokrugLamp_Helper::get_instance()->categories_list as $item ) {
			if ($item->id == $_id) {
				$res = $item;
				break;
			}
		}

		return $res;
	}

	/**
	 * Returns the parent category object
	 *
	 * @return Msav_VokrugLamp_Category|null
	 */
	public function get_parent_category() {
		$res = null;

		if ($this->parent_id > -1) {
			/** @var Msav_VokrugLamp_Category $item */
			foreach ( Msav_VokrugLamp_Helper::get_instance()->categories_list as $item ) {
				if ($item->id == $this->parent_id) {
					$res = $item;
					break;
				}
			}
		}

		return $res;
	}

	/**
	 * Update category information
	 *
	 * @return bool
	 */
	public function update_category() {
		$res = false;

		if ($this->id > 0 && $this->name != '') {
			$ps_id = $this->get_database_id();
			if ($ps_id == -1 && $this->name != '' && $this->id > -1) {
				$cat = new Category();

				if ($this->parent_id == -1)
					$cat->id_parent = Configuration::get('PS_HOME_CATEGORY');
				else
					$cat->id_parent = $this->get_parent_category()->get_database_id();

				$lang_id = (int)Configuration::get('PS_LANG_DEFAULT');
				$link = Tools::link_rewrite($this->name);
				$cat->link_rewrite = array($lang_id => $link);

				$cat->name = array($lang_id => $this->name);

				try {
					$cat->save();
					$res = true;
				} catch ( PrestaShopException $e ) {
				}
			}
		}

		return $res;
	}
}