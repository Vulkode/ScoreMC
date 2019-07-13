<?php

namespace ScoreMC;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TE;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use ScoreMC\ScoreHud\ScoreHud;
use ScoreMC\ScoreTemp\ScoreTemp;

class ScoreMC extends PluginBase{
	
	/** @var string */
	public const LIST = "list";
	public const SIDEBAR = "sidebar";
	public const BELOW_NAME = "belowname";

	/** @var string[] */
	private static $scoreboard = [];
	private static $scoretemps = [];

	/**
	* @return void
	* @author SharpyKurth
	* # PROHIBIDO ELIMINAR EL AUTHOR DEL PLUGIN, SI MODIFICAS AGREGA TU NOMBRE SIN BORRAR EL AUTHOR RESPECTIVO!
	*/
	public function onEnable() : void {
		$this->getScheduler()->scheduleRepeatingTask(new ScoreHud($this), $this->getConfig()->get("update-time", 20));
		$this->getLogger()->info(TE::GREEN."Enabled Plugin! Make by @DarkByx");
	}

	/**
	* @param Player $player
	* @param string $title
	* @param mixed  $plugin
	* @return null|ScoreTemp
	*/
	public static function createTempScore(Player $player, string $title, $plugin = null) : ?ScoreTemp {
		if (isset(self::$scoretemps[$player->getName()])) {
			$score = self::$scoretemps[$player->getName()];
			if ($score instanceof ScoreTemp) {
				if ($score->getOwner() === $plugin and $score->isExpired() == false) {
					return $score;
				}
			}
			return null;
		}
		$score = new ScoreTemp($plugin);
		self::$scoretemps[$player->getName()] = $score;
		return $score;
	}

	/**
	* @param Player $player
	* @return null|ScoreTemp
	*/
	public static function getTempScore(Player $player) : ?ScoreTemp {
		return isset(self::$scoretemps[$player->getName()]) ? self::$scoretemps[$player->getName()] : null;
	}

	/**
	* @param Player $player
	* @return void
	*/
	public static function removeTempScore(Player $player) : void {
		unset(self::$scoretemps[$player->getName()]);
	}

	/**
	* @param Player $player
	* @param string $title
	* @param int    $sortorder
	* @param string $displayslot
	* @return null|ScoreTemp
	*/
	public static function createScore(Player $player, string $title, int $sortorder = 0, string $displayslot = "sidebar") : void {
		if(isset(self::$scoreboard[$player->getName()])){
			self::removeScore($player);
		}
		$packet = new SetDisplayObjectivePacket();
		$packet->displaySlot = $displayslot;
		$packet->objectiveName = "objective";
		$packet->displayName = $title;
		$packet->criteriaName = "dummy";
		$packet->sortOrder = $sortorder;
		$player->sendDataPacket($packet);
		self::$scoreboard[$player->getName()] = $player->getName();
	}

	/**
	* @param Player $player
	* @return void
	*/
	public static function removeScore(Player $player) : void {
		$objectiveName = "objective";
		$packet = new RemoveObjectivePacket();
		$packet->objectiveName = $objectiveName;
		$player->sendDataPacket($packet);
		unset(self::$scoreboard[$player->getName()]);
	}

	/**
	* @param Player $player
	* @param int    $line
	* @param string $message
	* @return void
	*/
	public static function setScoreLine(Player $player, int $line, string $message) : void {
		if(!isset(self::$scoreboard[$player->getName()])) return;
		if($line <= 0 or $line > 15) return;
		$pk = new ScorePacketEntry();
		$pk->objectiveName = "objective";
		$pk->type = $pk::TYPE_FAKE_PLAYER;
		$pk->customName = $message;
		$pk->score = $line;
		$pk->scoreboardId = $line;
		
		$packet = new SetScorePacket();
		$packet->type = $packet::TYPE_CHANGE;
		$packet->entries[] = $pk;
		$player->sendDataPacket($packet);
	}

	/**
	* @param Player $player
	* @param array  $messages
	* @return void
	*/
	public static function setScoreLines(Player $player, array $messages) : void {
		$line = 1;
		foreach ($messages as $message) {
			self::setScoreLine($player, $line, $message);
			$line++;
		}
	}
}