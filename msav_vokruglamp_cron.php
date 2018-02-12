<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

if (!Module::isInstalled('msav_vokruglamp'))
	die("Module isn`t installed.");
elseif (substr(Tools::hash('msav_vokruglamp/cron'), 0, 10) != Tools::getValue('token'))
	die('Bad token');

/** @var Msav_VokrugLamp $vkr */
$vkr = Module::getInstanceByName('msav_vokruglamp');
if ($vkr->active) {
	@set_time_limit(0);
	Msav_VokrugLamp_Helper::get_instance()->do_import();
}
