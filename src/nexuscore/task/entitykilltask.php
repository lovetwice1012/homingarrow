<?php

    namespace nexuscore\task;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\entity\object\ItemEntity;
    use pocketmine\entity\Human;
    use pocketmine\entity\projectile\Arrow;
    use nexuscore\arrow\NewArrow;
    use nexuscore\arrow\AntiGravityArrow;
    use nexuscore\arrow\DoubleGravityArrow;
    use nexuscore\nexuscore;

    class entitykilltask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onRun(int $currentTick)
        {
            $amount = 0;
            if(nexuscore::$nextwipe===1){
            /** @var Level $level */
            foreach($this->plugin->getServer()->getLevels() as $level){
	            foreach($level->getEntities() as $entity){
		            if($entity instanceof ItemEntity || $entity instanceof Arrow || $entity instanceof NewArrow || $entity instanceof AntiGravityArrow || $entity instanceof DoubleGravityArrow){
                        $amount = $amount + 1;
			            $entity->kill();
                    }
	            }
            }
             $this->plugin->getServer()->broadcastTip("§3全部で".$amount."個のエンティティをkillしました");
             nexuscore::$nextwipe = nexuscore::$defaultwipe;

        }else{
        nexuscore::$nextwipe = nexuscore::$nextwipe - 1;
        if(nexuscore::$nextwipe===3600) $this->plugin->getServer()->broadcastTip("§41時間後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===900) $this->plugin->getServer()->broadcastTip("§415分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===600) $this->plugin->getServer()->broadcastTip("§410分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===300) $this->plugin->getServer()->broadcastTip("§45分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===180) $this->plugin->getServer()->broadcastTip("§43分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===120) $this->plugin->getServer()->broadcastTip("§42分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===60) $this->plugin->getServer()->broadcastTip("§41分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===45) $this->plugin->getServer()->broadcastTip("§445秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===30) $this->plugin->getServer()->broadcastTip("§430秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===10) $this->plugin->getServer()->broadcastTip("§410秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===5) $this->plugin->getServer()->broadcastTip("§45秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===4) $this->plugin->getServer()->broadcastTip("§44秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===3) $this->plugin->getServer()->broadcastTip("§43秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===2) $this->plugin->getServer()->broadcastTip("§42秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===1) $this->plugin->getServer()->broadcastTip("§41秒後に全ての落ちているアイテムを掃除します。");
        }
    }
    }