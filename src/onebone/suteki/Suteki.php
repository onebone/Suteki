<?php

namespace onebone\suteki;

use onebone\suteki\components\Button;
use onebone\suteki\components\CommandButton;
use onebone\suteki\components\Label;
use onebone\suteki\container\CustomForm;
use onebone\suteki\container\SimpleForm;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Suteki extends PluginBase implements Listener{
	/** @var CustomForm[] */
	private $forms = [];

	private $showEvents = [];

	const EVENT_JOIN = "JOIN";
	const EVENT_INTERACT = "INTERACT";

	private $formId = 0;

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

		foreach($this->getConfig()->get("showOnEvent", []) as $event => $form){
			if($this->getForm($form) === null){
				$this->getLogger()->warning("You are trying to show non-existing form '$form' on event '$event'");
			}else{
				$this->showEvents[strtoupper($event)] = $form;
			}
		}

		$this->getLogger()->info("There are ".count($this->forms)." forms loaded.");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function sendForm(Player $player, string $formId): bool {
		$form = $this->getForm($formId);
		if($form === null) return false;

		$pk = new ModalFormRequestPacket();
		$pk->formId = $form->getId();
		$pk->formData = $form->generateFormData();

		var_dump($pk->formData);
		$player->dataPacket($pk);
		return true;
	}

	public function getForm(string $formId){
		if(isset($this->forms[$formId])){
			return $this->forms[$formId];
		}

		return null;
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		if(isset($this->showEvents[self::EVENT_JOIN])){
			$this->sendForm($event->getPlayer(), $this->showEvents[self::EVENT_JOIN]);
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if(isset($this->showEvents[self::EVENT_INTERACT])){
			$this->sendForm($event->getPlayer(), $this->showEvents[self::EVENT_INTERACT]);
		}
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
			if(!isset($f['type'])){
				$this->getLogger()->warning("Container '$id' did not specify its type");
				continue;
			}

			$type = $f['type'];
			$title = $f['title'] ?? 'Suteki';

			switch(strtoupper($type)){
				case 'CUSTOM_FORM':
					$content = $f['content'] ?? [];

					$components = [];
					if(is_array($content)){
						foreach($content as $c){
							if(is_string($c)){
								$components[] = new Label($this, $c);
							}else{
								if(!isset($c['type'])){
									$this->getLogger()->warning("There is component which did not specify its type in form '$id'");
								}else{
									$button = $this->parseButton($c);
									if($button === null){
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

					$forms[$id] = new CustomForm($this, $this->formId++, $title, $components);
					break;
				case 'FORM':
					$title = $f['title'] ?? '';
					$content = $f['content'] ?? '';
					$buttons = $f['buttons'] ?? [];
					if(!is_array($buttons)){
						$this->getLogger()->warning("Invalid button data was give to '$id'");
						break;
					}

					$btns = [];
					foreach($buttons as $c){
						$button = $this->parseButton($c);
						if($button === null){
							$this->getLogger()->warning("There is component which specified invalid type on form '$id'");
							$this->getLogger()->warning("Got {$c['type']}, expected: BUTTON, LABEL");
							continue;
						}

						$btns[] = $button;
					}

					$forms[$id] = new SimpleForm($this, $this->formId++, $title, $content, $btns);
					break;
			}
		}

		return $forms;
	}

	private function parseButton($button): Button {
		switch(strtoupper($button['type'])){
			case "BUTTON":
				$text = $button['text'] ?? '';

				if(isset($button['exec'])){
					$perm = $button['perm'] ?? '@player';
					$components[] = new CommandButton($this, $text, $button['exec'], $perm);
				}elseif(isset($button['jump'])){
					// TODO
				}
				break;
			case "LABEL":
				$text = $button['text'] ?? '';
				$components[] = new Label($this, $text);
				break;
			default:
				return null;
		}

		return null;
	}
}
