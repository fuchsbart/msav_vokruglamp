<?php
if (!defined('_PS_VERSION_'))
	exit;

class Msav_VokrugLamp_Image {

	/**
	 * Vendor image URL
	 * @var string
	 */
	public $url;

	/**
	 * Image type
	 * @var string
	 */
	public $image_type;

	/**
	 * Msav_VokrugLamp_Image constructor.
	 *
	 * @param string $link
	 */
	public function __construct($link) {
		$pattern = '/\.(?<ext>(jpeg|jpg|png))$/';
		$match = null;
		if (preg_match($pattern, $link, $match)) {
			$this->image_type = $match['ext'];
			$this->url = str_replace(' ', '%20', $link);
		}
	}

	public function copy_image($product_id, $legend) {

		$copy_res = false;

		$lang_id = Configuration::get('PS_LANG_DEFAULT');
		$product_has_images = (bool)Image::getImages((int)$lang_id, (int)$product_id);

		$entity = 'products';

		$image_obj = new Image();
		$image_obj->id_product = (int)$product_id;
		$image_obj->position = Image::getHighestPosition((int)$product_id) + 1;
		$image_obj->cover = !$product_has_images;
		$image_obj->legend = $legend;
		try { $image_obj->add(); }
		catch (Exception $ex) { $image_obj = null; }

		if ($image_obj != null) {
			$tmp_file = tempnam(_PS_TMP_IMG_DIR_, 'msav_vokruglamp');
			$path = $image_obj->getPathForCreation();

			$url = urldecode(trim($this->url));
			$parsed_url = parse_url($url);

			if (isset($parsed_url['path'])) {
				$uri = ltrim($parsed_url['path'], '/');
				$parts = explode('/', $uri);
				foreach ($parts as &$part) {
					$part = rawurlencode($part);
				}
				unset($part);
				$parsed_url['path'] = '/'.implode('/', $parts);
			}

			if (isset($parsed_url['query'])) {
				$query_parts = array();
				parse_str($parsed_url['query'], $query_parts);
				$parsed_url['query'] = http_build_query($query_parts);
			}

			if (!function_exists('http_build_url')) {
				require_once(_PS_TOOL_DIR_.'http_build_url/http_build_url.php');
			}

			$url = http_build_url('', $parsed_url);

			$orig_tmpfile = $tmp_file;

			if (Tools::copy($url, $tmp_file)) {
				// Evaluate the memory required to resize the image: if it's too much, you can't resize it.
				if (!ImageManager::checkImageMemoryLimit($tmp_file)) {
					@unlink($tmp_file);
				} else {

					$tgt_width = $tgt_height = 0;
					$src_width = $src_height = 0;
					$error = 0;
					ImageManager::resize($tmp_file, $path.'.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
					try { $images_types = ImageType::getImagesTypes($entity, true); }
					catch (Exception $ex) { $images_types = null; }

					if ($images_types != null) {
						$previous_path = null;
						$path_infos = array();
						$path_infos[] = array($tgt_width, $tgt_height, $path.'.jpg');
						foreach ($images_types as $image_type) {
							$tmp_file = self::get_best_path($image_type['width'], $image_type['height'], $path_infos);

							if (ImageManager::resize(
								$tmp_file,
								$path.'-'.stripslashes($image_type['name']).'.jpg',
								$image_type['width'],
								$image_type['height'],
								'jpg',
								false,
								$error,
								$tgt_width,
								$tgt_height,
								5,
								$src_width,
								$src_height
							)) {
								// the last image should not be added in the candidate list if it's bigger than the original image
								if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
									$path_infos[] = array($tgt_width, $tgt_height, $path.'-'.stripslashes($image_type['name']).'.jpg');
								}
								if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$product_id.'.jpg')) {
									unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$product_id.'.jpg');
								}
								if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$product_id.'_'.(int)Context::getContext()->shop->id.'.jpg')) {
									unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$product_id.'_'.(int)Context::getContext()->shop->id.'.jpg');
								}
							}
						}

						$copy_res = true;
					}

				}
			}

			@unlink($orig_tmpfile);

		}

		if (!$copy_res && $image_obj != null && (int)$image_obj->id > 0) {
			try { $image_obj->delete(); }
			catch (Exception $ex) { }
		}

		return $copy_res;

	}

	protected static function get_best_path($tgt_width, $tgt_height, $path_infos)
	{
		$path_infos = array_reverse($path_infos);
		$path = '';
		foreach ($path_infos as $path_info) {
			list($width, $height, $path) = $path_info;
			if ($width >= $tgt_width && $height >= $tgt_height) {
				return $path;
			}
		}
		return $path;
	}

	/**
	 * Returns an Image Object or null.
	 *
	 * @param $link
	 *
	 * @return Msav_VokrugLamp_Image|null
	 */
	public static function create_image($link) {

		$res = new Msav_VokrugLamp_Image($link);
		if ($res->url == '' || $res->image_type == '') {
			$res = null;
		}

		return $res;
	} // create_image

}