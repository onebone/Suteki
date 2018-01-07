<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

abstract class Component{
	private $plugin;

	public function __construct(Suteki $plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin(): Suteki {
		return $this->plugin;
	}

	abstract public function getFormData(Player $player): array;
}
