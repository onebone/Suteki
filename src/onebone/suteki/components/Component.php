<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;

abstract class Component{
	private $plugin;

	public function __construct(Suteki $plugin){
		$this->plugin = $plugin;
	}

	public function getPlugin(): Suteki {
		return $this->plugin;
	}

	abstract public function getFormData(): array;
}
