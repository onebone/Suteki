<?php

namespace onebone\suteki;

use pocketmine\Player;

interface TextReplacer{
	public function getText(Player $player): string;
}
