<?php
Class AccessTrade {
	const LOG_DIR = '../log/'; //Pv,Click logs dir.
	const PRJ_DIR = 'projects/'; //Project files dir.
	const TEMPLATE_NAME = 'template'; //Template Name.
	const TAG_IMAGE = '__IMAGE__'; //Replacer
	const TAG_LINK = '__LINK__'; //Replacer

	private $today; //For log file.
	private $month; //For log file/
	private $cwd; //Current path.
	private $logFormat; //Log format (array).

	private $template; //Template html data.
	private $target; //Target project name.
	private $targetData; //Target project data.
	private $tag; //Created tag from template.


	/**
	 * Constructer.
	 *
	 */
	public function __construct() {
		$this->month = date('Ym');
		$this->today = date('Y-m-d');
		$this->cwd = dirname(__FILE__);
		$this->logFormat = array('view'=>0,'click'=>0);
	}


	/**
	 * Show target projects tag. And count 1 click.
	 *
	 */
	public function show($target) {
		$this->target = $target;

		$this
			->getTemplate()
			->getTargetData()
			->createTag()
			->printTag();

		$this->countClick($this->target);
	}


	/**
	 * Increments target projects pv counter.
	 *
	 */
	public function countView($target) {
		$this->target = $target;

		$this
			->openLog()
			->setViewLog()
			->saveLog();
	}


	/**
	 * Increments target projects click counter.
	 *
	 */
	public function countClick($target) {
		$this->target = $target;

		$this
			->openLog()
			->setClickLog()
			->saveLog();
	}


	/**
	 * Dump log.
	 *
	 */
	public function showLog() {
		$logName = self::LOG_DIR.$this->month;
		$tmp = array();
		if (file_exists($logName)) {
			$tmp = unserialize(file_get_contents($logName));
		}

		foreach ($tmp as $date=>$logs) {
			echo "{$date}\n";
			foreach ($logs as $k=>$v) {
				$per = ($v['view'] !== 0) ? round($v['click'] / $v['view'],1) : 0;
				echo "{$k} => PV: {$v['view']}  CLICK: {$v['click']}  PER:{$per}%\n";
			}
		}
		return $this;
	}


	/**
	 * Get html template.
	 *
	 */
	private function getTemplate() {
		$this->template = file_get_contents("{$this->cwd}/".self::TEMPLATE_NAME);
		return $this;
	}


	/**
	 * Get target projects data.
	 *
	 */
	private function getTargetData() {
		$tmp = file_get_contents("{$this->cwd}/".self::PRJ_DIR."{$this->target}");
		$tmp = explode("\n",$tmp);
		$this->targetData = array($tmp[0],$tmp[1]);
		return $this;
	}


	/**
	 * Replace template with project data.
	 *
	 */
	private function createTag() {
		$this->tag = str_replace(array(self::TAG_IMAGE,self::TAG_LINK),$this->targetData,$this->template);
		return $this;
	}


	/**
	 * Print tag.
	 *
	 */
	private function printTag() {
		echo $this->tag;
	}


	/**
	 * Open log file and unserialize.
	 *
	 */
	private function openLog() {
		$logName = self::LOG_DIR.$this->month;
		$tmp = array();
		if (file_exists($logName)) {
			$tmp = unserialize(file_get_contents($logName));
		}
		if (!isset($tmp[$this->today][$this->target])) {
			$tmp[$this->today][$this->target] = $this->logFormat;
		}
		$this->log = $tmp;
		return $this;
	}


	/**
	 * Increment click counter.
	 *
	 */
	private function setClickLog() {
		$this->log[$this->today][$this->target]['click'] = $this->log[$this->today][$this->target]['click'] + 1;
		return $this;
	}


	/**
	 * Increment view counter.
	 *
	 */
	private function setViewLog() {
		$this->log[$this->today][$this->target]['view'] = $this->log[$this->today][$this->target]['view'] + 1;
		return $this;
	}

	private function saveLog() {
		$tmp = serialize($this->log);
		file_put_contents(self::LOG_DIR.$this->month,$tmp);
		return $this;
	}
}
