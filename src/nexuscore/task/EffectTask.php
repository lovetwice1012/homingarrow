<?php

    namespace nexuscore\task;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\entity\effect\Effect;
    use pocketmine\entity\effect\EffectInstance;
    use pocketmine\entity\effect\VanillaEffects;
    use pocketmine\Server;

class EffectTask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onRun():void
        {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $armorinventory = $player->getArmorInventory();
                if($armorinventory===null)       continue;
                if($armorinventory->getLeggings()->getCustomName()==="太古の鎧(脚)HIGHJUMP"){
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::HIGHJUMP(), 400, 2, false));
                }
                if($armorinventory->getHelmet()->getCustomName()==="太古の鎧(頭)NIGHTVISION"){
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 400, 255, false));
                }
                if($armorinventory->getBoots()->getCustomName()==="太古の鎧(靴)SPEED"){
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 400, 1, false));
                }
                if($armorinventory->getBoots()->getCustomName()==="太古の鎧(靴)SPEED-V2"){
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 400, 3, false));
                }
            }
            
        }
    }
