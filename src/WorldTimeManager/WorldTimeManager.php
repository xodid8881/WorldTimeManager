<?php
declare(strict_types=1);

namespace WorldTimeManager;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use WorldTimeManager\Commands\MainCommand;
use pocketmine\scheduler\Task;

class WorldTimeManager extends PluginBase
{
  protected $config;
  public $db;
  private static $instance = null;

  public static function getInstance(): WorldTimeManager
  {
    return static::$instance;
  }
  public function onLoad()
  {
    self::$instance = $this;
  }
  public function onEnable()
  {
    $this->player = new Config ($this->getDataFolder() . "players.yml", Config::YAML);
    $this->pldb = $this->player->getAll();
    $this->world = new Config ($this->getDataFolder() . "worlds.yml", Config::YAML);
    $this->worlddb = $this->world->getAll();
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    $this->getServer()->getCommandMap()->register('WorldTimeManager', new MainCommand($this));
    $this->getScheduler ()->scheduleRepeatingTask ( new WorldTimeTask ( $this ), 20 );
  }
  public function getWorldLists() : array{
    /* 월드이름 Ui 버튼으로 불러오기 */
    $arr = [];
    foreach($this->worlddb as $World => $v){
      array_push($arr, $World);
    }
    return $arr;
  }
  public function onDisable()
  {
    $this->save();
  }
  public function SetWorldTime()
  {
    /* 월드 저장파일을 확인하고 타임을 설정 */
    foreach($this->worlddb as $WorldName => $v){
      if (isset($this->getServer->getLevel()->getFolderName ($WorldName))){
        $world = $this->getServer->getLevel()->getFolderName ($WorldName);
        $worldTime = (int)$this->worlddb [$world] ["Time"];
        $world->setTime ($worldTime);
      }
    }
  }
  public function save()
  {
    /* 콘피그 파일 저장 */
    $this->player->setAll($this->pldb);
    $this->player->save();
    $this->world->setAll($this->worlddb);
    $this->world->save();
  }
}
class WorldTimeTask extends Task {
  protected $owner;
  public function __construct(WorldTimeManager $owner) {
    $this->owner = $owner;
  }
  public function onRun(int $currentTick) {
    $this->owner->SetWorldTime ();
  }
}
