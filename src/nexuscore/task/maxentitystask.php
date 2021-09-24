<?php

    namespace nexuscore\task;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;
    use pocketmine\entity\object\ItemEntity;
    use pocketmine\Server;
    use pocketmine\entity\projectile\Arrow;
    use nexuscore\arrow\NewArrow;
    use nexuscore\arrow\AntiGravityArrow;
    use nexuscore\arrow\DoubleGravityArrow;
    use nexuscore\nexuscore;

    class maxentitystask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
            $this->sended = false;
        }

        public function onRun():void
        {
            $amount = 0;
            /** @var Level $level */
            foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level){
	            foreach($level->getEntities() as $entity){
		            if($entity instanceof ItemEntity || $entity instanceof Arrow || $entity instanceof NewArrow || $entity instanceof AntiGravityArrow || $entity instanceof DoubleGravityArrow){
                        $amount = $amount + 1;
                }
	            }
            }
             if($amount > 199 && $this->sended === false){
               Server::getInstance()->broadcastTip("§3エンティティが200個以上存在します。250個を超えると全てのエンティティがキルされます。");
               $this->sended = true;
             }
             if($amount > 249){
              Server::getInstance()->broadcastTip("§3エンティティが250個以上存在するため、全てのエンティティをkillします。");
               $this->sended = false;
               /** @var Level $level */
               foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level){
	                 foreach($level->getEntities() as $entity){
		                   if($entity instanceof ItemEntity || $entity instanceof Arrow || $entity instanceof NewArrow || $entity instanceof AntiGravityArrow || $entity instanceof DoubleGravityArrow){
			                     $entity->kill();
                       }
	                 }
               }
             }
        }  
 }
