<?php

namespace onebone\suteki\container;

use onebone\suteki\components\Component;
use onebone\suteki\components\Label;
use onebone\suteki\Suteki;

class CustomForm extends Container{
	/** @var Component[] */
	private $components = [];

	public function __construct(Suteki $plugin, int $id, string $title, $components){
		parent::__construct($plugin, $id, $title);

		if(is_string($components)){
			$components = [new Label($plugin, $components)];
		}

		$this->components = $components;
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
			'title' => $this->getTitle(),
			'content' => []
		];

		foreach($this->components as $component){
			$data['content'][] = $component->getFormData();
		}

		return json_encode($data);
	}
}
