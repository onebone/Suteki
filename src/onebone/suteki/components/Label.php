<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

class Label extends Component{
	private $text;

	public function __construct(Suteki $plugin, string $text){
		parent::__construct($plugin);

		$this->text = $text;
	}

	public function getFormData(Player $player): array {
		return [
			"type" => "label",
			"text" => $this->getPlugin()->replaceText($player, $this->text)
		];
	}
}
