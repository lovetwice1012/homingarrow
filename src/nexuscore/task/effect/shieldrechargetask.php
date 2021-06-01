<?php

    namespace nexuscore\task\effect;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\entity\Effect;
    use pocketmine\entity\EffectInstance;
    use nexuscore\nexuscore;

    class shieldrechargetask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onRun(int $currentTick)
        {
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                $armorinventory = $player->getArmorInventory();
                if($armorinventory===null)       continue;
                if(nexuscore::$shieldconfig->get($player->getName()) === "リチャージ"){
                $shield = 0;
                if($armorinventory->getChestplate()->getCustomName()==="シールドスーツ(胴)") $shield = $shield + 10;
                if($armorinventory->getChestplate()->getCustomName()==="シールドスーツ進化型(胴)") $shield = $shield + 15;
                if($armorinventory->getChestplate()->getCustomName()==="シールドスーツPRO(胴)") $shield = $shield + 30;
                nexuscore::$shieldconfig->set($player->getname(),$shield);
                nexuscore::$shieldconfig->save();
                $player->sendTip("あなたのシールド値を".$shield."に設定しました。");
                }
            }
            
        }
    }