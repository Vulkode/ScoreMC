# ScoreMC
Scoreboard for pocketmine - minecraft bedrock edition

## API

Add class in your plugin
```PHP
use ScoreMC\ScoreMC;
```

Add function in your plugin
```
ScoreMC::getInstance()->createScore(Player $player, string $title, int $sortOrder, string $displaySlot);
ScoreMC::getInstance()->setScoreLines(Player $player, array $messages, bool $translate);
```

### Prefix

```TXT
valores predeterminados:
- "{PING}"
- "{PLAYER}"
- "{PLAYER_X}"
- "{PLAYER_Y}"
- "{PLAYER_Z}"
- "{WORLD_NAME}"
- "{WORLD_PLAYERS}"
- "{TICKS}"
- "{TPS}"
- "{ONLINE_PLAYERS}"
- "{TIME}
- "{RAINBOW}"

- "{RANK}"
- "{PREFIX}"
- "{SUFFIX}"
- "{MONEY}"
- "{FACTION}"
- "{MONEY}"
```

### Example
```PHP

public function sendHud($player) {
	ScoreMC::createScore($player, 'My Score Hub');
	ScoreMC::setScoreLines($player, ["My line 1", "My line 2"]);
}

```

### Credits
This plugin is make by CodeB3/ßenja.
