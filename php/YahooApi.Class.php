<?php
Class YahooApi {
	//エンドポイントURL
	const END_POINT = 'http://jlp.yahooapis.jp/MAService/V1/parse';
	//最低文字数
	const MIN_WORD_COUNT = 2;
	//はじく文字列
	const REGEX = "/^[0-9\,\.\?\\\[\]\@\*\+\!\"\#\$\%\&\'\(\)\=\-a-z]+$/ius";
	//クエリ用コンテキスト
	private $context = null;
	private $result = null;

	public function __construct() {
	}

	/**
	 * Yahoo の api から渡された文字列の形態素解析の結果を取得する
	 * 記号、英数字のみの場合落とす　また文字数が2文字未満のものも落とす
	 * http://developer.yahoo.co.jp/webapi/jlp/
	 *
	 * @author yama
	 * @copyright 2013/02/27
	 */
	public static function analyze($apiKey,$contents) {
		$instance = new YahooApi();
		return $instance
				->createHeader($apiKey,$contents)
				->getXmlFromApi()
				->getResult();
	}


	/**
	 * ヘッダーデータの作成
	 *
	 * @author yama
	 * @copyright 2013/02/27
	 */
	private function createHeader($apiKey,$contents) {
		//クエリデータ
		$query = [
			'appid' => $apiKey,
			'sentence' => $contents,
			'results' => 'ma',
			'filter' => '9' //名詞のみ

		];
		$data = http_build_query($query,'','&');

		//ヘッダー
		$header = [
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen($data)

		];
		//ボディ部分
		$context = [
			'http' => [
				'method' => 'POST',
				'header' => implode("\r\n",$header),
				'content' => $data,
			]
		];
		$this->context = stream_context_create($context);
		return $this;

	}


	/**
	 * Yahoo Api を使用して形態素解析を行う
	 *
	 * @author yama
	 * @copyright 2013/02/27
	 */
	private function getXmlFromApi() {
		//初期化
		$this->result = null;

		//XMLデータの取得
		if ($tmp = simplexml_load_string(file_get_contents(self::END_POINT,false,$this->context))) {
			//全データを回して、既定文字数以上で、英数字記号などがないかを確認する
			foreach ($tmp->ma_result->word_list->word as $v) {
				//文字列
				$s = (string)$v->surface;
				//チェック
				if (!preg_match(self::REGEX,$s) && self::MIN_WORD_COUNT <= mb_strlen($s,'UTF-8')) {
					if (!isset($this->result[(string)$v->surface])) {
						//件数カウント
						$this->result[$s] = 0;

					}
					$this->result[$s]++;

				}
			}
			//件数の多い順に取得
			arsort($this->result);

		}
		return $this;

	}


	/**
	 * データの取得
	 *
	 * @author yama
	 * @copyright 2013/02/27
	 */
	private function getResult() {
		return $this->result;

	}
}
