<?php

namespace onebone\suteki;

use onebone\suteki\components\CommandButton;
use onebone\suteki\components\Input;
use onebone\suteki\components\JumpButton;
use onebone\suteki\components\Label;
use onebone\suteki\components\Slider;
use onebone\suteki\components\StepSlider;
use onebone\suteki\components\TextButton;
use onebone\suteki\components\Toggle;
use onebone\suteki\container\CustomForm;
use onebone\suteki\container\ModalForm;
use onebone\suteki\container\SimpleForm;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Suteki extends PluginBase implements Listener{
	/** @var CustomForm[] */
	private $forms = [];

	private $showEvents = [];

	/** @var TextReplacer[] */
	private $textReplacer = [];

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

		$this->initReplacers();

		$this->getLogger()->info("There are ".count($this->forms)." forms loaded.");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private function initReplacers(){
		$this->addTextReplacer('time', new class implements TextReplacer{
			public function getText(Player $player): string {
				return date('Y/m/d h:i:s');
			}
		});

		$this->addTextReplacer('itemid', new class implements TextReplacer{
			public function getText(Player $player): string {
				return $player->getInventory()->getItemInHand()->getId();
			}
		});

		$this->addTextReplacer('itemdamage', new class implements TextReplacer{
			public function getText(Player $player): string {
				return $player->getInventory()->getItemInHand()->getId();
			}
		});

		$this->addTextReplacer('server', new class implements TextReplacer{
			public function getText(Player $player): string {
				return Server::getInstance()->getName();
			}
		});

		$this->addTextReplacer('username', new class implements TextReplacer{
			public function getText(Player $player): string {
				return $player->getName();
			}
		});

		$this->addTextReplacer('online', new class implements TextReplacer{
			public function getText(Player $player): string {
				return count(Server::getInstance()->getOnlinePlayers());
			}
		});

		$this->addTextReplacer('maxonline', new class implements TextReplacer{
			public function getText(Player $player): string {
				return Server::getInstance()->getMaxPlayers();
			}
		});
	}

	public function addTextReplacer(string $id, TextReplacer $callback){
		if(isset($this->textReplacer[$id])){
			return false;
		}

		$this->textReplacer[$id] = $callback;
		return true;
	}

	public function replaceText(Player $player, string $text): string {
		preg_match_all('{%([0-9a-zA-Zㄱ-ㅎ가-힣_\-]+)%}', $text, $out);
		foreach($out[1] as $res){
			if(isset($this->textReplacer[$res])){
				$str = $this->textReplacer[$res]->getText($player);

				$text = str_replace("{%$res%}", $str, $text);
			}
		}

		return $text;
	}

	public function sendForm(Player $player, string $formId): bool {
		$form = $this->getForm($formId);
		if($form === null) return false;

		$pk = new ModalFormRequestPacket();
		$pk->formId = $form->getId();
		$pk->formData = $form->generateFormData($player);

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

			$data = json_decode($pk->formData, true);
			foreach($this->forms as $form){
				if($form->getId() === $pk->formId){
					if($form instanceof SimpleForm){
						$components = $form->getButtons();
						if(isset($components[$data])){
							$components[$data]->onClick($player);
						}
					}elseif($form instanceof CustomForm){
						// TODO
					}elseif($form instanceof ModalForm){
						if($data){
							$form->getYesButton()->onClick($player);
						}else{
							$form->getNoButton()->onClick($player);
						}
					}
				}
			}
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
									switch(strtoupper($c['type'])){
										case 'BUTTON':
											$components[] = $this->parseButton($c);
											break;
										case 'TOGGLE':
											$text = $c['text'] ?? '';
											$default = (bool) ($c['default'] ?? true);

											$components[] = new Toggle($this, $text, $default);
											break;
										case 'LABEL':
											$text = $c['text'] ?? '';

											$components[] = new Label($this, $text);
											break;
										case 'SLIDER':
											$text = $c['text'] ?? '';
											$min = (int) ($c['min'] ?? 1);
											$max = (int) ($c['max'] ?? 5);
											$step = (int) ($c['step'] ?? 1);
											$default = (int) ($c['default'] ?? 1);

											$components[] = new Slider($this, $text, $min, $max, $step, $default);
											break;
										case 'INPUT':
											$text = $c['text'] ?? '';
											$placeholder = $c['placeholder'] ?? '';
											$default = $c['default'] ?? '';

											$components[] = new Input($this, $text, $placeholder, $default);
											break;
										default:
											$this->getLogger()->warning("There is component which specified invalid type on form '$id'");
											$this->getLogger()->warning("Got {$c['type']}, expected: BUTTON, LABEL, TOGGLE, STEP_SLIDER");
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
							$this->getLogger()->warning("Button requires property 'text");
							continue;
						}

						$btns[] = $button;
					}

					$forms[$id] = new SimpleForm($this, $this->formId++, $title, $content, $btns);
					break;
				case 'MODAL':
					$title = $f['title'] ?? '';
					$content = $f['content'] ?? '';
					$yes = $f['yes'] ?? ['text'=>''];
					$no = $f['no'] ?? ['text'=>''];

					$yb = $this->parseButton($yes);
					$nb = $this->parseButton($no);

					if($yb === null or $nb === null){
						$this->getLogger()->warning("Button requires property 'text");
						break;
					}

					$forms[$id] = new ModalForm($this, $this->formId++, $title, $content, $yb, $nb);
					break;
			}
		}

		return $forms;
	}

	private function parseButton($button) {
		$text = $button['text'] ?? '';

		if(isset($button['exec'])){
			$perm = $button['perm'] ?? '@player';
			return new CommandButton($this, $text, $button['exec'], $perm);
		}elseif(isset($button['jump'])){
			return new JumpButton($this, $text, $button['jump']);
		}

		return new TextButton($this, $text);
	}
}
