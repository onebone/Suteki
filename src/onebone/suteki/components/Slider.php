<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;
use pocketmine\Player;

class Slider extends Component{
	private $text;
	private $min, $max, $step, $default;

	public function __construct(Suteki $plugin, string $text, int $min, int $max, int $step, int $default){
		parent::__construct($plugin);

		$this->text = $text;
		$this->min = $min;
		$this->max = $max;
		$this->step = $step;
		$this->default = $default;
	}

	public function getFormData(Player $player): array{
		return [
			'type' => 'slider',
			'text' => $this->getPlugin()->replaceText($player, $this->text),
			'min' => $this->min,
			'max' => $this->max,
			'step' => $this->step,
			'default' => $this->default
		];
	}
}
