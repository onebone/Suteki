<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

class CommandButton extends Button{
	private $command, $perm;

	public function __construct(Suteki $plugin, string $text, string $command, string $perm){
		parent::__construct($plugin, $text);

		$this->command = $command;
		$this->perm = $perm;
	}

	public function onClick(Player $player, $data){
		if(strtoupper($this->perm) === "CONSOLE"){

		}
	}

	public function getFormData(): array{
	}
}
