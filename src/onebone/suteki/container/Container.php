<?php

namespace onebone\suteki\container;

use onebone\suteki\Suteki;

abstract class Container{
	private $plugin;
	private $id;
	private $title;

	public function __construct(Suteki $plugin, int $id, string $title){
		$this->plugin = $plugin;
		$this->id = $id;
		$this->title = $title;
	}

	public function getPlugin(): Suteki {
		return $this->plugin;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getTitle(): string {
		return $this->title;
	}

	abstract public function generateFormData(): string;
}
