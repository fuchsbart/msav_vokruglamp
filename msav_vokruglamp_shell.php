<?php
/**
 * The cron task shell command.
 *
 * @author Andrey Mishchenko
 */

require_once (dirname(__FILE__) . '/classes/msav_vokruglamp_shell.php');

@set_time_limit(0);
Msav_VokrugLamp_Shell::get_instance()->start();
