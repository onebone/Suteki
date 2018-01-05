<?php

namespace onebone\suteki\components;

use onebone\suteki\Suteki;

class Label extends Component{
	private $text;

	public function __construct(Suteki $plugin, string $text){
		parent::__construct($plugin);

		$this->text = $text;
	}

	public function getFormData(): array {
		return [
			"type" => "label",
			"text" => $this->text
		];
	}
}
