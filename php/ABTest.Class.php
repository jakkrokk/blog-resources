<?php
Class AB {
	const LOG_DIR = '/_log/'; //Pv,Click logs dir. Permission 757.

	private $today; //For log file.
	private $month; //For log file/
	private $cwd; //Current path.
	private $logFormat; //Log format (array).
	private $group; //Ads group.
	private $adId; // Ads id.


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
	 * Click event
	 *
	 */
	public function evtClick($group,$adId,$target) {
		$this->group = $group;
		$this->adId = $adId;
		$this->target = $target;

		$this
			->openLog()
			->setClickLog()
			->saveLog();

		$this->header();
	}


	/**
	 * Impression event.
	 *
	 */
	public function evtImp($group,$adId,$target) {
		$this->group = $group;
		$this->adId = $adId;
		$this->target = $target;

		$this
			->openLog()
			->setViewLog()
			->saveLog();
	}


	/**
	 * Dump log.
	 *
	 */
	public function showLog($group) {
		$this->group = $group;
		$logName = "{$this->cwd}".self::LOG_DIR."{$this->group}_{$this->month}";
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
	 * Open log file and unserialize.
	 *
	 */
	private function openLog() {
		$logName = "{$this->cwd}".self::LOG_DIR."{$this->group}_{$this->month}";
		$tmp = array();
		if (file_exists($logName)) {
			$tmp = unserialize(file_get_contents($logName));
		}
		if (!isset($tmp[$this->today][$this->adId])) {
			$tmp[$this->today][$this->adId] = $this->logFormat;
		}
		$this->log = $tmp;
		return $this;
	}


	/**
	 * Increment click counter.
	 *
	 */
	private function setClickLog() {
		$this->log[$this->today][$this->adId]['click'] = $this->log[$this->today][$this->adId]['click'] + 1;
		return $this;
	}


	/**
	 * Increment view counter.
	 *
	 */
	private function setViewLog() {
		$this->log[$this->today][$this->adId]['view'] = $this->log[$this->today][$this->adId]['view'] + 1;
		return $this;
	}


	/**
	 * Save log.
	 *
	 */
	private function saveLog() {
		$logName = "{$this->cwd}".self::LOG_DIR."{$this->group}_{$this->month}";
		$tmp = serialize($this->log);
		file_put_contents($logName,$tmp);
		return $this;
	}


	/**
	 * Heading to target.
	 *
	 */
	private function header() {
		header("Location: {$this->target}");
		exit;
	}
}
