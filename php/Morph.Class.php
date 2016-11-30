<?php
//need to include
include('GetTitleAndBody.Class.php');
include('YahooApi.Class.php');

//sample
Morph::analyze('http://neoinspire.net/archives/61');

Class Morph {
	public static function analyze($url) {
		$Html = new GetTitleAndBody;
		$Html->getHtml($url);
		$contents = $Html->get('body');
		$res = YahooApi::analyze($contents);
	}
}
