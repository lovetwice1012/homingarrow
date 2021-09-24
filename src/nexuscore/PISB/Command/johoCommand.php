<?php
declare(strict_types=1);

namespace nexuscore\PISB\Command;

use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use nexuscore\nexuscore;

class johoCommand extends Command {

	public function __construct() {
		parent::__construct("joho", "情報欄のon/off", "/joho");
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (!$sender instanceof Player) return false;
		$tag = $sender->namedtag;
		$msg=["ON","OFF"];
		nexuscore::getInstance()->isOn($sender)?$flag=1:$flag=0;
		$tag->setInt(nexuscore::getInstance()->getName(), $flag);
		$sender->sendMessage("[" . nexuscore::getInstance()->getName() . "]§a{$msg[$flag]}にしました。");
		return true;
	}

}