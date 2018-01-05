<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;

class Form{
	private $plugin;

	/** @var string */
	private $title;

	/** @var Component[] */
	private $components = [];

	public function __construct(Suteki $plugin, string $title, $components){
		$this->plugin = $plugin;
		$this->title = $title;

		if(is_string($components)){
			$components = [new Label($plugin, $components)];
		}

		$this->components = $components;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function addComponent(Component $component){
		$this->components[] = $component;
	}

	/**
	 * @return Component[]
	 */
	public function getComponents(): array {
		return $this->components;
	}

	public function generateFormData(): string {
		$data = [
			'type' => 'custom_form',
			'title' => $this->title,
			'content' => []
		];

		foreach($this->components as $component){
			$data['content'][] = $component->getFormData();
		}

		return json_encode($data);
	}
}
