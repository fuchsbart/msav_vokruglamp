<?php

class Msav_VokrugLamp_Shell {

	/**
	 * Represents the current class global instance.
	 *
	 * @var Msav_VokrugLamp_Shell
	 */
	private static $INSTANCE = null;

	/**
	 * Product XML feed rows offset.
	 *
	 * @var int
	 */
	public $offset;

	/**
	 * Log file name.
	 *
	 * @var string
	 */
	public $log_file_name;

	/**
	 * Returns the current class global instance.
	 *
	 * @return Msav_VokrugLamp_Shell
	 */
	public static function get_instance() {
		if (self::$INSTANCE == null) {
			self::$INSTANCE = new Msav_VokrugLamp_Shell();
		}
		return self::$INSTANCE;
	}

	/**
	 * Msav_VokrugLamp_Shell constructor.
	 */
	public function __construct() {
		$this->offset = 0;
		$this->log_file_name = dirname(__FILE__) . '/import.log';
		$this->init_log();
	}

	/**
	 * Start the requests queue.
	 *
	 * @return void
	 */
	public function start() {
		if ($curl = curl_init()) {
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 0 );
			while ( $this->offset != - 1 ) {
				// Preparing the current Url request.
				$url = sprintf(
					'https://decorisvet.ru/modules/msav_vokruglamp/msav_vokruglamp_cron.php?token=21fd4614c8&id_shop=1&offset=%d',
					$this->offset);

				curl_setopt($curl, CURLOPT_URL, $url);

				$response = curl_exec($curl);
				if (curl_errno($curl)) {
					$this->log(sprintf("Error: %s", curl_error($curl)));
					die(curl_error($curl));
				}
				echo $url."\n";
				$this->log($url);

				$data = json_decode($response);
				if ($data !== null) {
					$this->offset = (int)$data->offset;
				} else {
					$this->offset++;
				}
				/*if ($this->offset > 10)
					die('zzz '.$this->offset);*/
			}
			$this->log('Finish');
		}
	}

	/**
	 * Initialize the log file.
	 *
	 * @return void
	 */
	private function init_log() {

		$handler = @fopen($this->log_file_name, 'w');
		if ($handler !== false) {
			$date = new DateTime();
			fwrite($handler, sprintf("%s - Init\n", $date->format('d.m.Y H:i:s')));
			fclose($handler);
		}

	}

	/**
	 * Save the log message.
	 *
	 * @param $message string
	 * @return void
	 */
	private function log($message) {

		$handler = @fopen($this->log_file_name, 'a');
		if ($handler !== false) {
			$date = new DateTime();
			fwrite($handler, sprintf("%s - %s\n", $date->format('d.m.Y H:i:s'), $message));
			fclose($handler);
		}

	}

}
