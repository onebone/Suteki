<?php

namespace onebone\suteki\container;

use onebone\suteki\components\Button;
use onebone\suteki\Suteki;
use pocketmine\Player;

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

	public function generateFormData(Player $player): string {
		return json_encode([
			'type' => 'modal',
			'title' => $this->getPlugin()->replaceText($player, $this->getTitle()),
			'content' => $this->getPlugin()->replaceText($player, $this->content),
			'button1' => $this->getPlugin()->replaceText($player, $this->yes->getText()),
			'button2' => $this->getPlugin()->replaceText($player, $this->no->getText())
		]);
	}
}
