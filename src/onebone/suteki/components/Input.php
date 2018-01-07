<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

class Input extends Component{
	private $text, $placeholder, $default;

	public function __construct(Suteki $plugin, string $text, string $placeholder, string $default){
		parent::__construct($plugin);

		$this->text = $text;
		$this->placeholder = $placeholder;
		$this->default = $default;
	}

	public function getFormData(Player $player): array{
		return [
			'type' => 'input',
			'text' => $this->getPlugin()->replaceText($player, $this->text),
			'placeholder' => $this->getPlugin()->replaceText($player, $this->placeholder),
			'default' => $this->getPlugin()->replaceText($player, $this->default)
		];
	}
}
