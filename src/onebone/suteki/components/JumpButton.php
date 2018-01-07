<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

class JumpButton extends Button{
	private $container;

	public function __construct(Suteki $plugin, string $text, string $container){
		parent::__construct($plugin, $text);

		$this->container = $container;
	}

	public function onClick(Player $player){
		$this->getPlugin()->sendForm($player, $this->container);
	}

	public function getFormData(): array{
		return [
			"type" => "button",
			"text" => $this->getText()
		];
	}
}
