<?php
declare(strict_types=1);

namespace WorldTimeManager;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

class EventListener implements Listener
{

  protected $plugin;

  public function __construct(WorldTimeManager $plugin)
  {
    $this->plugin = $plugin;
  }
  public function onJoin (PlayerJoinEvent $event)
  {
    $player = $event->getPlayer();
    $name = $player->getName ();
    if (!isset ($this->plugin->pldb [strtolower ($name)])) {
      $this->plugin->pldb [strtolower ($name)] ["Event"] = "Null";
      $this->plugin->save ();
      return true;
    }
  }
  public function onPacket(DataPacketReceiveEvent $event)
  {
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    $name = $player->getName();
    $tag = "§l§6[ §f월드설정 §6] §r§7";
    if ($packet instanceof ModalFormResponsePacket) {
      $id = $packet->formId;
      $data = json_decode($packet->formData, true);
      if ($id === 1321) {
        if ($data === 0) {
          if ($player->isOp()) {
            $this->NewWorldUI($player);
            return true;
          } else {
            $player->sendMessage($tag . "권한이 없습니다.");
            return true;
          }
        }
        if ($data === 1) {
          if ($player->isOp()) {
            $this->SetWorldUI($player);
            return true;
          } else {
            $player->sendMessage($tag . "권한이 없습니다.");
            return true;
          }
        }
      }
      if ($id === 1322) {
        if (!isset($data[0])) {
          $player->sendMessage( $tag . '해당 월드 이름을 적어주세요.');
          return;
        }
        if (! file_exists ( Server::getInstance()->getDataPath () . "worlds/" . $data[0] )) {
          $player->sendMessage ( $tag . "해당 월드를 찾을 수 없습니다." );
          return;
        }
        $this->plugin->worlddb ["levels"] [$data[0]] ["Time"] = 0;
        $this->plugin->save();
        $player->sendMessage($tag . "정상적으로 생성했습니다.");
        return true;
      }
      if ($id === 1323) {
        if($data !== null){
          $arr = [];
          foreach($this->plugin->getWorldLists() as $world){
            array_push($arr, $world);
          }
          $this->plugin->pldb [strtolower ($name)] ["Event"] = (string)$arr[$data];
          $this->plugin->save ();
          $this->SetWorldTimeUI ($player);
          return true;
        }
      }
      if ($id === 1324) {
        if (!isset($data[0])) {
          $player->sendMessage( $tag . '설정 할 시간을 적어주세요.');
          return;
        }
        if(!is_numeric($data[0])){
          $player->sendMessage($tag . "시간은 숫자로만 가능합니다.");
          return true;
        }
        $worldname = (string)$this->plugin->pldb [strtolower ($name)] ["Event"];
        $this->plugin->worlddb ["levels"] [$worldname] ["Time"] = (int)$data[0];
        $this->plugin->save();
        $player->sendMessage($tag . "정상적으로 설정했습니다.");
        return true;
      }
    }
  }
  public function NewWorldUI(Player $player)
  {
    $encode = [
      'type' => 'custom_form',
      'title' => '§l§6[ §f월드설정 §6]',
      'content' => [
        [
          'type' => 'input',
          'text' => '§r§7추가 할 월드이름을 적어주세요.'
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 1322;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
  public function SetWorldUI(Player $player)
  {
    $arr = [];
    foreach($this->plugin->getWorldLists() as $list){
      array_push($arr, array('text' => '- ' . $list));
    }
    $encode = [
      'type' => 'form',
      'title' => '§l§6[ §f월드설정 §6]',
      'content' => '§r§7시간을 설정 할 월드를 선택해주세요.',
      'buttons' => $arr
    ];
    $packet = new ModalFormRequestPacket();
    $packet->formId = 1323;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
  public function SetWorldTimeUI(Player $player)
  {
    $name = $player->getName ();
    $worldname = (string)$this->plugin->pldb [strtolower ($name)] ["Event"];
    $encode = [
      'type' => 'custom_form',
      'title' => '§l§6[ §f월드설정 §6]',
      'content' => [
        [
          'type' => 'input',
          'text' => "§r§7설정 할 {$worldname} 월드 시간을 적어주세요."
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 1324;
    $packet->formData = json_encode($encode);
    $player->sendDataPacket($packet);
    return true;
  }
}
