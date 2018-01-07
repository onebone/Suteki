<?php

namespace onebone\suteki\container;

use onebone\suteki\components\Button;
use onebone\suteki\Suteki;

class SimpleForm extends Container{
	/** @var Button[] */
	private $buttons = [];
	private $content;

	public function __construct(Suteki $plugin, int $id, string $title, string $content, $buttons){
		parent::__construct($plugin, $id, $title);

		$this->content = $content;
		$this->buttons = $buttons;
	}

	public function addButton(Button $button){
		$this->buttons[] = $button;
	}

	public function getButtons(){
		return $this->buttons;
	}

	public function getContent(): string {
		return $this->content;
	}

	public function generateFormData(): string{
		$data = [
			'type' => 'form',
			'title' => $this->getTitle(),
			'content' => $this->content,
			'buttons' => []
		];

		foreach($this->buttons as $button){
			$data['buttons'][] = $button->getFormData();
		}

		return json_encode($data);
	}
}
