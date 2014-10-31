<?php
namespace Org\Plista\Kornakapi\Http;

use Org\Plista\Kornakapi\Exception;

/**
 * kornakapi http interface using curl
 */
class Http {

	/**
	 * @var string
	 */
	private $baseurl = '';

	/**
	 * @param string $baseurl
	 * @param float $timeout_default
	 * @param array $timeout_config
	 */
	public function __construct($baseurl, $timeout_default, $timeout_config) {
		$this->baseurl = $baseurl;
		$this->timeout = new Timeout($timeout_default, $timeout_config);
	}

	/**
	 * @return string
	 */
	private function getTemporaryFilename() {
		return tempnam(sys_get_temp_dir(), 'Kornakapi');
	}

	/**
	 * @deprecated, useful anymore?
	 * @param string $url
	 * @param array $query
	 */
	public function void($url, array $query = array()) {
		$timeout = $this->timeout->get($url);

		$curl = curl_init();

		$url = $this->baseurl . $url . '?' . http_build_query($query);

		curl_setopt($curl, CURLOPT_URL, $url);

		if ($timeout) {
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
			// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		}
		curl_exec($curl);
		curl_close($curl);
	}

	/**
	 * Sending GET request to Kornakapi without response
	 *
	 * @param $url
	 * @param array $data
	 * @throws Exception
	 */
	public function get($url, array $data) {
		$timeout = $this->timeout->get($url);
		$url = $this->baseurl . $url . '?' . http_build_query($data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!$result = curl_exec($ch)) {
			throw new Exception(curl_error($ch));
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($code != 200) {
			throw new Exception('Not successful: ' . $code);
		}
		curl_close($ch);

		return $result;
	}

	/**
	 * Sending a POST request to Kornakapi
	 *
	 * @param $url
	 * @param array $data
	 * @throws Exception
	 */
	public function post($url, array $data) {
		$timeout = $this->timeout->get($url);
		$url = $this->baseurl . $url;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($data));
		curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
		if(!$result = curl_exec($ch)) {
			throw new Exception(curl_error($ch));
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($code != 200) {
			throw new Exception('Not successful: ' . $code);
		}
		curl_close($ch);
	}

	/**
	 * Sending GET request to Kornakapi with response
	 *
	 * @param string $url
	 * @param array $query
	 * @return string
	 */
	public function fetch($url, array $query = array()) {
		$timeout = $this->timeout->get($url);
		$url = $this->baseurl . $url . '?' . http_build_query($query);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		if ($timeout) {
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
		}

		$result = curl_exec($curl);
		curl_close($curl);

		return $result;
	}

	/**
	 * Executing the batch-command in a php curl. The data with which we want to make something will be saved in a file on which we will be make a POST.
	 * @param string $url
	 * @param array $data
	 * @param int $batchsize
	 * @throws Exception
	 */
	public function batch($url, array $data, $batchsize) {
		$timeout = $this->timeout->get($url);

		if (empty($batchsize)) {
			throw new Exception('empty batchsize given');
		}

		$query = array(
			'batchSize' => intval($batchsize)
		);
		$url = $this->baseurl . $url . '?' . http_build_query($query);

		$content = '';
		foreach ($data as $val) {
			$content .= implode(',', $val) . "\n";
		}

		//write data in the file
		$filePath = $this->getTemporaryFilename();
		file_put_contents($filePath, $content);

		$post['file'] = '@' . $filePath;
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

		if ($timeout) {
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
		}
		curl_exec($curl);

		unlink($filePath);
	}

}
