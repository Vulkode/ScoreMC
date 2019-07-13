<?php

namespace ScoreMC\ScoreTemp;

use pocketmine\Server;
use pocketmine\Player;
use ScoreMC\ScoreMC;

class ScoreTemp {

	/** @var string */
	private $title = "";

	/** @var array */
	private $lines = [];

	/** @var int */
	private $sortOrder = 0;
	private $timeUpdate = 0;
	private $timeExpire = 2;

	/** @var string */
	private $displaySlot = "sidebar";

	/**
	* @param mixed $owner
	*/
	public function __construct($owner = null) {
		$this->owner = $owner;
	}

	/**
	* @return mixed
	*/
	public function getOwner() {
		return $this->owner;
	}

	/**
	* @param mixed $value
	* @return void
	*/
	public function setOwner($value = null) : void {
		$this->owner = $value;
	}

	/**
	* @param string $title
	* @return void
	*/
	public function setTitle(string $title) : void {
		$this->title = $title;
	}

	/**
	* @param array $value
	* @return void
	*/
	public function setLines(array $value) : void {
		$this->lines = $value;
	}

	/**
	* @param int $value
	* @return void
	*/
	public function setSortOrder(int $value) : void {
		$this->sortOrder = $value;
	}

	/**
	* @param string $value
	* @return void
	*/
	public function setDisplatSlot(string $value) : void {
		$this->displaySlot = $value;
	}

	/**
	* @param float $value
	* @return void
	*/
	public function setExpireTime(float $value = 1) : void {
		$this->timeExpire = $value;
	}

	/**
	* @return float
	*/
	public function getExpireTime() : float {
		return $this->timeExpire;
	}

	/**
	* @return bool
	*/
	public function isExpired() : bool {
		return (microtime(true) - $this->timeUpdate) > $this->getExpireTime();
	}

	/**
	* @param Player $player
	* @return void
	*/
	public function sendScore(Player $player) : void {
		$this->timeUpdate = microtime(true);
		ScoreMC::createScore($player, $this->title, $this->sortOrder, $this->displaySlot);
		ScoreMC::setScoreLines($player, $this->lines);
		$this->timeUpdate = microtime(true);
	}
}