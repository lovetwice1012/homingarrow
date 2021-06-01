<?php

    namespace nexuscore\task\effect;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\entity\Effect;
    use pocketmine\entity\EffectInstance;

    class speedtask extends Task
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
                if($armorinventory->getBoots()->getCustomName()==="太古の鎧(靴)SPEED"){
                    $player->addEffect(new EffectInstance(Effect::getEffect(1), 400, 1, false));
                }
            }
            
        }
    }