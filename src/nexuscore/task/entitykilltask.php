<?php

    namespace nexuscore\task;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\entity\object\ItemEntity;
    use pocketmine\entity\projectile\Arrow;
    use nexuscore\arrow\NewArrow;
    use nexuscore\arrow\AntiGravityArrow;
    use nexuscore\arrow\DoubleGravityArrow;
    use pocketmine\Server;
    use nexuscore\nexuscore;

    class entitykilltask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onRun():void
        {
            $amount = 0;
            if(nexuscore::$nextwipe===1){
            /** @var Level $level */
            foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level){
	            foreach($level->getEntities() as $entity){
		            if($entity instanceof ItemEntity || $entity instanceof Arrow || $entity instanceof NewArrow || $entity instanceof AntiGravityArrow || $entity instanceof DoubleGravityArrow){
                        $amount = $amount + 1;
			            $entity->kill();
                    }
	            }
            }
            Server::getInstance()->broadcastTip("§3全部で".$amount."個のエンティティをkillしました");
             nexuscore::$nextwipe = nexuscore::$defaultwipe;

        }else{
        nexuscore::$nextwipe = nexuscore::$nextwipe - 1;
        if(nexuscore::$nextwipe===3600) Server::getInstance()->broadcastTip("§41時間後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===900) Server::getInstance()->broadcastTip("§415分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===600) Server::getInstance()->broadcastTip("§410分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===300) Server::getInstance()->broadcastTip("§45分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===180) Server::getInstance()->broadcastTip("§43分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===120) Server::getInstance()->broadcastTip("§42分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===60) Server::getInstance()->broadcastTip("§41分後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===45) Server::getInstance()->broadcastTip("§445秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===30) Server::getInstance()->broadcastTip("§430秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===10) Server::getInstance()->broadcastTip("§410秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===5) Server::getInstance()->broadcastTip("§45秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===4) Server::getInstance()->broadcastTip("§44秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===3) Server::getInstance()->broadcastTip("§43秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===2) Server::getInstance()->broadcastTip("§42秒後に全ての落ちているアイテムを掃除します。");
        if(nexuscore::$nextwipe===1) Server::getInstance()->broadcastTip("§41秒後に全ての落ちているアイテムを掃除します。");
        }
    }
    }