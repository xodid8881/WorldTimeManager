<?php
declare(strict_types=1);

namespace WorldTimeManager\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use WorldTimeManager\WorldTimeManager;

class MainCommand extends Command
{

  protected $plugin;

  public function __construct(WorldTimeManager $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('월드설정', '월드설정 명령어.', '/월드설정');
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args)
  {
    $encode = [
      'type' => 'form',
      'title' => '§l§6[ §f월드설정 §6]',
      'content' => '§r§7버튼을 눌러주세요',
      'buttons' => [
        [
          'text' => '§l§6[ §f월드추가 §6]'
        ],
        [
          'text' => '§l§6[ §f시간설정 §6]'
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 1321;
    $packet->formData = json_encode($encode);
    $sender->sendDataPacket($packet);
  }
}
