<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

abstract class Button extends Component{
	private $text;

	public function __construct(Suteki $plugin, string $text){
		parent::__construct($plugin);

		$this->text = $text;
	}

	public function getText(): string {
		return $this->text;
	}

	abstract public function onClick(Player $player, $data);
}
