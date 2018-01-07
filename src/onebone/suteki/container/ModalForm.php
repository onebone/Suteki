<?php

namespace onebone\suteki\container;

use onebone\suteki\components\Button;
use onebone\suteki\Suteki;

class ModalForm extends Container{
	private $content;
	/** @var Button */
	private $yes, $no;

	public function __construct(Suteki $plugin, $id, $title, string $content, Button $yes, Button$no){
		parent::__construct($plugin, $id, $title);

		$this->content = $content;
		$this->yes = $yes;
		$this->no = $no;
	}

	public function getYesButton(): Button {
		return $this->yes;
	}

	public function getNoButton(): Button {
		return $this->no;
	}

	public function generateFormData(): string {
		return json_encode([
			'type' => 'modal',
			'title' => $this->getTitle(),
			'content' => $this->content,
			'button1' => $this->yes->getText(),
			'button2' => $this->no->getText()
		]);
	}
}
