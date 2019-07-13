# ScoreMC
Score hud para servidores de minecraft pe (bedrock edition)

## API
Para usar el score en tu plugin de minigame, hay 2 maneras de hacerlo el normal y el temporal.

Necesitas agregar el class que usaremos
```PHP
use ScoreMC\ScoreMC;
```
- Normal
```PHP
ScoreMC::createScore(Player $player, string $title, int $sortOrder, string $displaySlot);
ScoreMC::setScoreLines(Player $player, array $messages);
```
- Temporal
```PHP
$score = ScoreMC::createTempScore(Player $player, string $title, $this);
if ($score !== null) {
    $score->setTitle(string $title); // titulo del score
    $score->setLines(array $lines); // mensajes para las lineas del score en array
    $score->setSortOrder(int $value); // no es necesario
    $score->setDisplatSlot(string $value); // no es necesario
    $score->setExpireTime(float $value); // tiempo de eliminacion por defecto 2seg
    $score->sendScore(Player $player); // importante y obligatorio para enviar el score al jugador
}
```

### Ajustes y funciones preestablecidas del config.yml

```TXT
valores predeterminados:
-"{player}"
-"{item_name}"
-"{item_id}"
-"{item_meta}"
-"{item_count}"
-"{x}"
-"{y}"
-"{z}"
-"{online}"
-"{players}"
-"{tick}"
-"{tps}"
-"{world}"
-"{ping}"
-"{motd}"
-"{prefix}"
-"{suffix}"
-"{faction}"
-"{prison_rank}
-"{rank}"
-"{money}"
-"{rainbow}"
```

### Credits
Este plugin fue creado por **SharpyKurth**.
