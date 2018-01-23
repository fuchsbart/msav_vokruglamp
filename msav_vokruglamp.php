<?php

require_once (dirname(__FILE__) . '/classes/msav_vokruglamp_helper.php');

if (!defined('_PS_VERSION_'))
	exit;

/**
 * Class Msav_VokrugLamp
 *
 * @author Andrey Mishchenko (info@msav.ru)
 */
class Msav_VokrugLamp extends Module {

	/**
	 * Module Name Id
	 *
	 * @var string
	 */
	public static $MODULE_NAME = 'msav_vokruglamp';

	/**
	 * Module translation text domain
	 *
	 * @var string
	 */
	public static $TEXT_DOMAIN = 'Modules.Msav_VokrugLamp.Admin';

	/**
	 * Msav_VokrugLamp constructor.
	 *
	 * @param string|null $name
	 * @param Context|null $context
	 */
	public function __construct( string $name = null, Context $context = null ) {

		$this->name = self::$MODULE_NAME;
		$this->author = 'Andrey Mishchenko';
		$this->version = '1.0.0';
		$this->need_instance = 0;

		$this->ps_versions_compliancy = array(
			'min' => '1.7.0.0',
			'max' => _PS_VERSION_
		);

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Product importer from the VokrugLamp.ru XML feed' );
		$this->description = $this->trans('Imports products and prices from the VokrugLamp.ru XML Product feed.',
			array(),
			'Modules.Msav_VokrugLamp.Admin');

	}

	public function install() {
		$this->_clearCache('*');

		Configuration::updateValue('MSAV_VOKRUGLAMP_VENDOR_XML', '');

		return parent::install();
	}

	public function uninstall() {
		$this->_clearCache('*');
		return parent::uninstall();
	}

	public function getContent()
	{
		$output = '';

		if (Tools::isSubmit('submitVokrugLamp')) {
			Configuration::updateValue('MSAV_VOKRUGLAMP_VENDOR_XML', Tools::getValue('MSAV_VOKRUGLAMP_VENDOR_XML'));

			$this->_clearCache('*');

			$output .= $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
		}

		$this->context->smarty->assign(
			array(

			)
		);

		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$cron_url = _PS_BASE_URL_SSL_ . _MODULE_DIR_
		            . 'msav_vokruglamp/msav_vokruglamp_cron.php?token='
		            . substr(Tools::hash('msav_vokruglamp/cron'), 0, 10)
		            . '&id_shop='.$this->context->shop->id;

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->trans('Settings', array(), 'Admin.Global'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->trans('Products XML Feed', array(), 'Modules.Specials.Admin'),
						'name' => 'MSAV_VOKRUGLAMP_VENDOR_XML',
						'class' => '',
						'desc' => $this->trans('Define the Products XML feed URL. Cron: ' . $cron_url, array(), 'Modules.Specials.Admin'),
					),
				),
				'submit' => array(
					'title' => $this->trans('Save', array(), 'Admin.Actions'),
				),
			),
		);

		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitVokrugLamp';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
		                        '&configure=' . $this->name .
		                        '&tab_module=' . $this->tab .
		                        '&module_name=' . $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'MSAV_VOKRUGLAMP_VENDOR_XML' => Tools::getValue('MSAV_VOKRUGLAMP_VENDOR_XML', Configuration::get('MSAV_VOKRUGLAMP_VENDOR_XML')),
		);
	}
}