<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

class Toggle extends Component{
	private $text;
	private $default;

	public function __construct(Suteki $plugin, string $text, bool $default){
		parent::__construct($plugin);

		$this->text = $text;
		$this->default = $default;
	}

	public function getFormData(Player $player): array {
		return [
			'type' => 'toggle',
			'text' => $this->getPlugin()->replaceText($player, $this->text),
			'default' => $this->default
		];
	}
}
