<?php

namespace onebone\suteki\components;

use pocketmine\Player;

class TextButton extends Button{
	public function onClick(Player $player, $data){}

	public function getFormData(): array{
		return [
			"type" => "button",
			"text" => $this->getText()
		];
	}
}
