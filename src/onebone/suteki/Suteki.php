<?php

namespace onebone\suteki;

use onebone\suteki\components\CommandButton;
use onebone\suteki\components\Dialog;
use onebone\suteki\components\Form;
use onebone\suteki\components\Label;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Suteki extends PluginBase implements Listener{
	/** @var Form[] */
	private $forms = [];

	public function onLoad(){
		$this->saveDefaultConfig();
		$this->saveResource("default-form.json");
	}

	public function onEnable(){
		foreach($this->getConfig()->get("load", []) as $f){
			$data = json_decode(file_get_contents($this->getDataFolder() . $f), true);

			foreach($this->parse($data) as $id=>$form){
				$this->forms[$id] = $form;
			}
		}

		$this->getLogger()->info("There are ".count($this->forms)." forms loaded.");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function sendForm(Player $player, string $formId){
		$pk = new ModalFormRequestPacket();
	}

	public function onPacketReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();

		if($pk instanceof ModalFormResponsePacket){
			$player = $event->getPlayer();
		}
	}

	private function parse($data){
		$forms = [];

		foreach($data as $id => $f){
			$title = $f['title'] ?? 'Suteki';
			$content = $f['content'] ?? [];

			$components = [];
			if(is_array($content)){
				foreach($content as $c){
					if(is_string($c)){
						$components[] = [new Label($this, $c)];
					}else{
						if(!isset($c['type'])){
							$this->getLogger()->warning("There is component which did not specify its type in form '$id'");
						}else{
							switch(strtoupper($c['type'])){
								case "BUTTON":
									$text = $c['text'] ?? '';

									if(isset($c['exec'])){
										$perm = $c['perm'] ?? '@player';
										$components[] = new CommandButton($this, $text, $c['exec'], $perm);
									}elseif(isset($c['jump'])){
										// TODO
									}
									break;
								case "LABEL":
									$text = $c['text'] ?? '';
									$components[] = new Label($this, $text);
									break;
								default:
									$this->getLogger()->warning("There is component which specified invalid type on form '$id'");
									$this->getLogger()->warning("Got {$c['type']}, expected: BUTTON, LABEL");
							}
						}
					}
				}
			}else{
				/** @var $content string */
				$components = [new Label($this, $content)];
			}

			$forms[$id] = new Form($this, $title, $components);
		}

		return $forms;
	}
}
