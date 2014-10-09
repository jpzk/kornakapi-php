<?php

require_once('../../../autoload.php');

use Org\Plista\Kornakapi\Kornakapi;

class KornakapiLDATest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		error_reporting(E_ALL);
		$this->baseUrl = 'http://localhost:8080/kornakapi/';
		$this->domainId = 1;

		$this->items = array(
			array(
				'itemid' => 1,
				'domainid' => $this->domainId,
				'fulltext' => 'damit herumspielen. Oder seinen Alltag aufzeichnen. So wie Lifelogger Dirk Haun. Im Sekundentakt macht die Kamera Bilder seines Lebens.'
			),
			array(
				'itemid' => 2,
				'domainid' => $this->domainId,
				'fulltext' => 'So wie Lifelogger Dirk Haun. Im Sekundentakt macht die Kamera Bilder seines Lebens.'
			),
			array(
				'itemid' => 3,
				'domainid' => $this->domainId,
				'fulltext' => 'Wenn sich ein Geek eine ansteckbare Miniaturkamera kauft, will er damit herumspielen. Oder seinen Alltag aufzeichnen. So wie Lifelogger Dirk Haun. Im Sekundentakt macht die Kamera Bilder seines Lebens.'
			)
		);
	}

	protected function tearDown() {
		$this->deleteArticles();
	}

	protected function deleteArticles() {
		$kornakapi = new Kornakapi($this->baseUrl);
		foreach($this->items as $item) {
			$label = $item['domainid'];
			$itemId = $item['itemid'];
			$kornakapi->deleteArticle($label, $itemId);
		}
	}

	protected function addArticles() {
		$kornakapi = new Kornakapi($this->baseUrl);
		foreach($this->items as $item) {
			$label = $item['domainid'];
			$itemId = $item['itemid'];
			$text = $item['fulltext'];
			$kornakapi->addArticle($label, $itemId, $text);
		}
	}

	protected function train() {
		$kornakapi = new Kornakapi($this->baseUrl);
		$kornakapi->train('lda', $this->domainId);
	}


	public function testAddArticles() {
		$this->addArticles();
	}

	protected function recommend() {
		$kornakapi = new Kornakapi($this->baseUrl);
		$recommendation = $kornakapi->recommend('lda', 'itemIDs', [$this->items[0]['itemid']]);
		var_dump($recommendation);
	}

	public function testRecommend() {
		var_dump($this->recommend());
	}

	public function testTrain() {
		return;
		$this->addArticles();
		$this->train();
	}
}