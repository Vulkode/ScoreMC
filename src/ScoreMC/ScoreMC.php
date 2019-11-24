<?php

namespace ScoreMC;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use ScoreMC\ScoreTask\ScoreTask;
use pocketmine\utils\TextFormat as TE;
use ScoreMC\Event\LevelChangeEvent;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;


class ScoreMC extends PluginBase{
	
	/** @var string */
	public const LIST = "list";
	public const SIDEBAR = "sidebar";
	public const BELOW_NAME = "belowname";

	/** @var string[] */
	private static $scoreboard = [];

	/** @var ScoreMC */
	private static $plugin;

	/**
	* @return void
	* @author ßenja
	* # PROHIBIDO ELIMINAR EL AUTHOR DEL PLUGIN, SI MODIFICAS AGREGA TU NOMBRE SIN BORRAR EL AUTHOR RESPECTIVO!
	*/
	public function onEnable() : void{
		static::$plugin = $this;
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents(new LevelChangeEvent($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new ScoreTask($this), $this->getConfig()->get("update-time", 20));
		$this->getLogger()->info(TE::DARK_PURPLE."Enabled!");
		$this->getLogger()->info(TE::DARK_PURPLE.base64_decode("DQogICAgIF9fX19fICAgICAgICAgICAgICAgIF9fX19fICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9fX19fICAgICAgICAgICAgIA0KICBfX3xfX18gIHxfXyBfXyAgICBfICBfX3xfX18gIHxfXyAgX19fX18gIF9fX19fICAgX19fX19fICBfX3xfX18gIHxfXyAgX19fX19fICANCiB8ICAgICAgPiAgICB8XCBcICAvLyB8ICAgX19ffCAgICB8LyAgICAgXHwgICAgIFwgfCAgIF9fX3x8ICAgICAgPiAgICB8fF9fXyAgIHwgDQogfCAgICAgPCAgICAgfCBcIFwvLyAgfCAgIHxfXyAgICAgfHwgICAgIHx8ICAgICAgXHwgICBfX198fCAgICAgPCAgICAgfHxfX18gICB8IA0KIHxfX19fX18+ICBfX3wgL19fLyAgIHxfX19fX198ICBfX3xcX19fX18vfF9fX19fXy98X19fX19ffHxfX19fX18+ICBfX3x8X19fX19ffCANCiAgICB8X19fX198ICAgICAgICAgICAgICB8X19fX198ICAgICAgICAgICAgICAgICAgICAgICAgICAgICB8X19fX198ICAgICAgICAgICAgDQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIA=="));
	}

	/**
	* @return ScoreMC
	*/
	public function getInstance() : ScoreMC{
		return static::$plugin;
	}

	/**
	* @param Player $player
	* @param string $displayName
	* @param int    $sortOrder
	* @param string $displaySlot
	* @return void
	*/
	public function createScore(Player $player, string $displayName, int $sortOrder = 0, string $displaySlot = "sidebar") : void{
		if(isset(self::$scoreboard[$player->getName()])){
			$this->removeScore($player);
		}
		$packet = new SetDisplayObjectivePacket();
		$packet->displaySlot = $displaySlot;
		$packet->objectiveName = "objective";
		$packet->displayName = $displayName;
		$packet->criteriaName = "dummy";
		$packet->sortOrder = $sortOrder;
		$player->sendDataPacket($packet);
		self::$scoreboard[$player->getName()] = $player->getName();
	}

	/**
	* @param Player $player
	* @return void
	*/
	public function removeScore(Player $player) : void{
		$packet = new RemoveObjectivePacket();
		$packet->objectiveName = "objective";
		$player->sendDataPacket($packet);
		unset(self::$scoreboard[$player->getName()]);
	}

	/**
	* @param Player $player
	* @param int    $line
	* @param string $customName
	* @return bool
	*/
	public function setScoreLine(Player $player, int $line, string $customName) : void{
		if(!isset(self::$scoreboard[$player->getName()])) {
			return;
		}

		if($line <= 0 or $line > 15) {
			return;
		}

		$pkline = new ScorePacketEntry();
		$pkline->objectiveName = "objective";
		$pkline->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$pkline->customName = $customName;
		$pkline->score = $line;
		$pkline->scoreboardId = $line;
		
		$packet = new SetScorePacket();
		$packet->type = SetScorePacket::TYPE_CHANGE;
		$packet->entries[] = $pkline;
		$player->sendDataPacket($packet);
	}

	/**
	* @param Player $player
	* @param array  $messages
	* @return void
	*/
	public function setScoreLines(Player $player, array $messages, bool $translate = false) : void{
		$line = 1;
		foreach ($messages as $message) {
			if ($translate) {
				self::setScoreLine($player, $line, $this->translate($player, $message));
			}else{
				self::setScoreLine($player, $line, $message);
			}
			$line++;
		}
	}

	/**
	* @param Player $player
	* @param string $message
	* @return string
	*/
	public function translate(Player $player, string $message) : string{
		# PLAYER INFO
		$message = str_replace('{PING}', $player->getPing(), $message);
		$message = str_replace('{PLAYER}', $player->getName(), $message);
		$message = str_replace('{PLAYER_X}', $player->getFloorX(), $message);
		$message = str_replace('{PLAYER_Y}', $player->getFloorY(), $message);
		$message = str_replace('{PLAYER_Z}', $player->getFloorZ(), $message);

		# WORLD INFO
		$level = $player->getLevel();
		$message = str_replace('{WORLD_NAME}', $level->getFolderName(), $message);
		$message = str_replace('{WORLD_PLAYERS}', count($level->getPlayers()), $message);
		
		# SERVER INFO
		$message = str_replace('{TICKS}', $this->getServer()->getTickUsage(), $message);
		$message = str_replace('{TPS}', $this->getServer()->getTicksPerSecond(), $message);
		$message = str_replace('{ONLINE_PLAYERS}', count($this->getServer()->getOnlinePlayers()), $message);

		# OTHER INFO
		$message = str_replace("{TIME}", date("H:i a"), $message);
		$message = str_replace("{RAINBOW}", $this->getColor(), $message);

		# PLUGIN INFO
		$message = $this->reviewAllPlugins($player, $message);
		return TE::colorize((string) $message);
	}

	/**
	* @return string
	*/
	public function getColor() : string{
		$colors = [TE::DARK_BLUE, TE::DARK_GREEN, TE::DARK_AQUA, TE::DARK_RED, TE::DARK_PURPLE, TE::GOLD, TE::GRAY, TE::DARK_GRAY, TE::BLUE, TE::GREEN, TE::AQUA, TE::RED, TE::LIGHT_PURPLE, TE::YELLOW, TE::WHITE];
		return $colors[rand(0,14)];
	}

	/**
	* @param Player $player
	* @param string $message
	* @return string
	*/
	private function reviewAllPlugins(Player $player, string $message) : string{
		$PurePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		if (!is_null($PurePerms)) {
			$message = str_replace('{RANK}', $PurePerms->getUserDataMgr()->getGroup($player)->getName(), $message);
			$message = str_replace('{PREFIX}', $PurePerms->getUserDataMgr()->getNode($player, "prefix"), $message);
			$message = str_replace('{SUFFIX}', $PurePerms->getUserDataMgr()->getNode($player, "suffix"), $message);
		}

		$EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		if (!is_null($EconomyAPI)) {
			$message = str_replace('{MONEY}', $EconomyAPI->myMoney($player), $message);
		}

		$FactionsPro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
		if(!is_null($FactionsPro)){
			$message = str_replace('{FACTION}', $FactionsPro->getPlayerFaction($player->getName()), $message);
		}

		$MoneySystem = $this->getServer()->getPluginManager()->getPlugin("MoneySystem");
		if (!is_null($MoneySystem)) {
			$message = str_replace('{MONEY}', $MoneySystem->getMoney($player), $message);
		}
		return $message;
	}
}