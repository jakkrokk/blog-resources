<?php
Class NgramSimilar {

	public $intersect = array(); //Intersecting ngram data.
	public $percent; //Simlar percent

	/**
	 * Start comparing similar text
	 *
	 * @param string $base
	 * @param string $check
	 * @return int
	 */
	public function __construct($base=NULL,$check=NULL,$ngram=3) {
		if (!is_null($base) && !is_null($check)) {
			$this->execute($base,$check,$ngram);

		}
		return $this;

	}


	/**
	 * Start comparing similar text
	 *
	 * @param string $base
	 * @param string $check
	 * @return int
	 */
	public function execute($base,$check,$ngram=3) {
		$this->compare($base,$check,$ngram);
		return $this;

	}


	/**
	 * Compare similar
	 *
	 * @param string $base
	 * @param string $check
	 * @return int
	 */
	private function compare($base,$check,$ngram=3) {
		//Set longer content to the base content
		if (mb_strlen($check) > mb_strlen($base)) {
			list($base,$check) = array($check,$base);

		}

		//Create ngram into array
		$nBase = $this->createNgram($base,$ngram);
		$nCheck = $this->createNgram($check,$ngram);

		//Intersect ngram arrays
		$this->intersect = array_intersect($nBase,$nCheck);
		//Percent data.
		$this->percent = round(count($this->intersect) / count($nBase),2) * 100;
		return $this;

	}


	/**
	 * Create ngram
	 *
	 * @param string $str
	 * @param int $ngram
	 * @return array
	 */
	private function createNgram($str,$ngram=3) {
		//Seperate words with ngram
		$ngramData = array();
		foreach (range(0,mb_strlen($str,'UTF-8') - $ngram) as $v) {
			$ngramData[] = mb_substr($str,$v,$ngram,'UTF-8');

		}
		return $ngramData;

	}
}