<?php
/**
 * 指定したURLからタイトルっぽいものと本文っぽいものを抜き出すクラス
 * Created:jakkrokk
 * url:http://neoinspire.net
 *
 * コンストラクタ引数：
 *	 1:$_body_len=有効にする記事の文字数の下限値（400ならマルチバイトで400文字以上じゃないと無効）
 *	 2:$_link_per=有効にするリンクの出現率の上限値（0.1ならリンクが1割以下じゃないと無効）
 *	 3:$_tag_per=有効にするHTMLタグの出現率の上限値（0.1ならタグが1割以下じゃないと無効）
 *	 4:$_html_point;=この点数を超えないと対象にならない（デフォルト8点）
 *	 5:$_weight=設定値の変更（Array）
 *		 $_array['キー']=評価点　の形式
 *			 キーリスト
 *			 max_link_weight;		//リンク率が一番小さい（デフォルト2点）
 *			 link_weight;			//リンクが規定値より低い（デフォルト3点）
 *			 tag_weight;			//タグ率が一番小さい（デフォルト1点）
 *			 max_img_weight;		//画像率が高い（デフォルト1点）
 *			 body_weight;			//文字数規定値より多い（デフォルト1点）
 *			 body_length_weight;	//文字数が全DIVの中で一番多い（デフォルト2点）
 *
 *
Example.
$Html = new GetTitleAndBody;
$Html->getHtml('http://neoinspire.net/archives/515');

print($Html->get('title'));
print("<BR><BR><BR>");
print($Html->get('body'));

 */
Class GetTitleAndBody {

	private $bodyLen;	  //有効になる記事の文字数の下限値（400ならマルチバイトで400文字以上じゃないと無効）（デフォルト　250）
	private $linkPer;	  //有効になるリンクの出現率の上限値（0.1ならリンクが1割以下じゃないと無効）（デフォルト　0.1）
	private $tagPer;	   //有効になるHTMLタグの出現率の上限値（0.1ならHTMLタグが1割以下じゃないと無効）（デフォルト　0.1）
	private $htmlPoint;	//この点数を超えないと対象にならない（デフォルト8点）

	//以下評価ポイント
	private $weight = array(
			"max_link_weight"=>"1",	 //リンク率が一番小さい（デフォルト1点）
			"link_weight"=>"2",		 //リンクが規定値より低い（デフォルト2点）
			"tag_weight"=>"1",		  //タグ率が一番小さい（デフォルト1点）
			"body_weight"=>"2",		 //文字数規定値より多い（デフォルト2点）
			"body_length_weight"=>"3"   //文字数が全DIVの中で一番多い（デフォルト3点）
		);

	private $url;		   //対象のURL
	private $title;		 //Htmlのタイトルが入る
	private $body;		  //Htmlの本文が入る
	private $bodyLength;   //本文の文字数が入る

	private $error;		 //エラーの場合メッセージが入る

	private $getHead;	  //対象URLからとってきた<head></head>（処理用）
	private $getBody;	  //対象URLからとってきた<body></body>（処理用）

	/**
	  * Logging:コンストラクタ
	  *
	  * @author jakkrokk
	  * @copyright 2008/04/24
	  * @param  int $bodyLen　有効になる記事の文字数の下限値
	  * @param  int $linkPer　有効になるリンクの出現率の上限値
	  * @param  int $tagPer　有効になるHTMLタグの出現率の上限値
	  * @param  int $htmlPoint　この点数を超えないと対象にならない
	  * @param  array $weight　評価ポイント
	  */
	function GetTitleAndBody($bodyLen=200,$linkPer=0.1,$tagPer=0.1,$htmlPoint=6,$weight=null) {
		$this->bodyLen = $bodyLen;
		$this->linkPer = $linkPer;
		$this->tagPer = $tagPer;
		$this->htmlPoint = $htmlPoint;

		if (is_array($weight)) {
			if (array_key_exists("max_link_weight",$weight)) $this->weight['max_link_weight'] = $weight['max_link_weight'];
			if (array_key_exists("link_weight",$weight)) $this->weight['link_weight'] = $weight['link_weight'];
			if (array_key_exists("tag_weight",$weight)) $this->weight['tag_weight'] = $weight['tag_weight'];
			if (array_key_exists("body_weight",$weight)) $this->weight['body_weight'] = $weight['body_weight'];
			if (array_key_exists("body_length_weight",$weight)) $this->weight['body_length_weight'] = $weight['body_length_weight'];
		}
	}

	/**
	  * getHtml:URLから本文とタイトルを取得する
	  *
	  * @author jakkrokk
	  * @copyright 2008/04/24
	  * @param  string $url　取得するURL
	  * @return bool
	  */
	function getHtml($url) {
		$this->url = $url;
		//リンク率の比較用
		$maxLinkPer = 1;
		//画像出現率の比較用
		$maxImgPer = 1;
		//ポイントの初期化
		$maxHtmlPoint = 0;

		//URLからHTMLが取得できるかのチェック
		if ($this->_openUrl() && $this->getBody) {
			//HTMLからCSSレイアウトのDIV か テーブルレイアウトのTDの中身を抜き出す
			$matchedBody = preg_split("/(<div.*>)|(<\/div>)|(<td.*>)|(<\/td>)/i",$this->getBody,-1,PREG_SPLIT_NO_EMPTY);

			//抜き出した中身を全部チェック
			foreach ($matchedBody as $key=>$value) {
				//リンクの数を取得
				$linkCount = preg_match_all("/<a.*>/",$value,$linkMatch);
				//タグの数を取得
				$tagCount = preg_match_all("/<[^(img)|(<br.*?)].*?>/",$value, $tagMatch);
				//本文っぽいものの文字数を確認
				$bodyLength = mb_strlen(trim(strip_tags($value)),'UTF-8');
				//本文でほしい所を抜き取る（h1～h5以外は無くてもよい）
				$tempBody = trim(strip_tags($value));

				//本文がないやつは無視
				if ($bodyLength > 0) {
					//リンクの出現率を確認
					$linkPer = $linkCount / $bodyLength;
					//タグの出現率を確認
					$tagPer = $tagCount / $bodyLength;

					//評価値
					$htmlPoint = 0;
					//一番リンク率が低い、設定値よりリンク率が低い、設定値よりタグ率が低い 画像率が一番高い、文字数が規定以上 一番大きいパラグラフ
					if ($linkPer < $maxLinkPer) $htmlPoint += $this->weight['max_link_weight'];
					if ($linkPer < $this->linkPer) $htmlPoint += $this->weight['link_weight'];
					if ($tagPer < $this->tagPer) $htmlPoint += $this->weight['tag_weight'];
					if ($bodyLength > $this->bodyLen) $htmlPoint += $this->weight['body_weight'];
					if ($this->bodyLength < $bodyLength) $htmlPoint += $this->weight['body_length_weight'];

					//評価値が規定以上でかつ一番評価が高ければ格納
					if ($htmlPoint > $this->htmlPoint && $htmlPoint > $maxHtmlPoint) {
						$maxHtmlPoint = $htmlPoint;
						$this->bodyLength = $bodyLength;
						$maxLinkPer = $linkPer;
						$this->body = strip_tags($tempBody);
					}
				}
			}
		}
	}


	/**
	  * _openUrl:URLからHTMLを取得する
	  *
	  * @author jakkrokk
	  * @copyright 2008/04/24
	  * @return bool
	  */
	function _openUrl(){
		if ($htmls = file_get_contents($this->url)) {
			$html = mb_convert_encoding($htmls,'UTF-8','ASCII,JIS,UTF-8,EUC-JP,SJIS');

			//<head>部分だけ抜き出す＆タイトルタグを取得
			$heads = preg_match_all("/<head.+?\/head>/ius",$html,$head);
			$isTitle = preg_match("/<title>(.*)<\/title>/ius",$head[0][0],$headTitle);

			//取得したデータをきれいに（style script head の除去）
			$html = preg_replace("/<head.+?\/head>/ius","",$html);
			$html = preg_replace("/<\!\-\-.+?\-\->/","",$html);
			$html = preg_replace("/<style.+?\/style>/ius","",$html);
			$html = preg_replace("/<script.+?\/script>/ius","",$html);

			//取得した部分を保存
			$this->getHead = $head[0][0];
			$this->getBody = $html;
			if ($isTitle>0) $this->title=$headTitle[1];
			return true;
		} else {
			$this->error = "対象URLにアクセスできませんでした。";
			return false;
		}
	}


	/**
	  * get:プロパティへのアクセサメソッド
	  *
	  * @author jakkrokk
	  * @copyright 2008/04/21
	  * @param  string $method　プロパティ名
	  * @return プロパティがある場合,プロパティデータ ない場合はfalse
	  */
	function get($method) {
		if ($this->$method) {
			return $this->$method;
		} else {
			return false;
		}
	}
}
