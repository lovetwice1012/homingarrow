<?php

namespace nexuscore;

/* PluginBase */
use pocketmine\plugin\PluginBase;

/* Server */
use pocketmine\Server;

/* Player */
use pocketmine\player\Player;

/* Utils */
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;

/* TaskScheduler */
use pocketmine\scheduler\ClosureTask;

/* Command */
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/* Entity */
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;

/* Entity(effect) */
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;

/* Entity(projectile) */
use pocketmine\entity\projectile\Arrow;

/* Item */
use pocketmine\item\Item;

/* Level */
use pocketmine\level\Level;
use pocketmine\level\Position;

/* Inventory */
use pocketmine\inventory\ArmorInventory;

/* Math */
use pocketmine\math\Vector3;

/* Event(entity) */
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

/* Event(player) */
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerChatEvent;

/* Event(server) */
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;

/* Network */
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;

/* nexuscore special arrows */
use nexuscore\arrow\AntiGravityArrow;
use nexuscore\arrow\DoubleGravityArrow;
use nexuscore\arrow\NewArrow;
use nexuscore\arrow\HomingArrow;
use nexuscore\arrow\HiPowerHomingArrow;

/* nexucore tasks */
use nexuscore\task\entitykilltask;
use nexuscore\task\broadcasttask;
use nexuscore\task\checktweettask;
use nexuscore\task\maxentitystask;
use nexuscore\task\EffectTask;
use nexuscore\task\effect\shieldrechargetask;

/* nexuscore FormAPI */
/* https://github.com/Yahir-AR/FormAPI-PMMP.git */
use nexuscore\FormAPI\window\SimpleWindowForm;
use nexuscore\FormAPI\window\CustomWindowForm;
use nexuscore\FormAPI\response\PlayerWindowResponse;

/* nexuscore wwarp */
/* https://github.com/lovetwice1012/worldwarp.git */
use nexuscore\worldwarp\WMAPI;
use nexuscore\worldwarp\CustomForm;

/* nexuscore PlayerInfoScoreBoard(remix) */
/* https://github.com/lovetwice1012/nexusPISB.git */
/* https://github.com/yurisi0212/PlayerInfoScoreBoard.git */
use nexuscore\PISB\Task\Sendtask;
use nexuscore\PISB\Command\johoCommand;

class nexuscore extends PluginBase implements Listener
{
    public static $defaultwipe;
    public static $nextwipe;
    public static $itemconfig;
    public static $shieldconfig;
    public $wipeconfig;
    private bool $cancelGuard = false;
    public $rewardconfig;
    public $dayconfig;
    public $tppconfig;
    public $preloginconfig;
    public $data;
    public $plugin;
    public $Main;
    private static $main;

    public function onEnable()
    {
        self::$main=$this;
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->wipeconfig = new Config($this->getDataFolder() . "wipe.yml", Config::YAML);
        $this->preloginconfig = new Config($this->getDataFolder() . "prelogin.yml", Config::YAML);
        /*$this->rewardconfig = new Config($this->getDataFolder() . "Reward.json", Config::JSON, [
            "can_duplicate" => true,
            "Reward_economy" => [
                1000,
                10000,
            ],
            "Reward_items" => [
                [
                    [
                        "id" => 1,
                        "damage" => 0,
                        "count" => 64
                    ],
                    [
                        "id" => 2,
                        "damage" => 0,
                        "count" => 64
                    ]
                ],
                [
                    [
                        "id" => 1,
                        "damage" => 0,
                        "count" => 64
                    ],
                    [
                        "id" => 2,
                        "damage" => 0,
                        "count" => 64
                    ]
                ]
            ],
            "Reward_table" => [
                "日" => 0,
                "月" => -1,
                "火" => 1,
                "水" => 1,
                "木" => 1,
                "金" => 1,
                "土" => -1
            ]
        ]);*/

        $this->saveResource("Reward.json");
        $this->rewardconfig = new Config($this->getDataFolder() . "Reward.json", Config::JSON);
        $this->rewardconfig->enableJsonOption(JSON_UNESCAPED_UNICODE);
        //$this->rewardconfig->save();

        $this->dayconfig = new Config($this->getDataFolder() . "lastday.yml", Config::YAML);
        $this->tppconfig = new Config($this->getDataFolder() . "tpp.yml", Config::YAML);
        self::$itemconfig = new Config($this->getDataFolder() . "ItemReward.json", Config::JSON);
        self::$itemconfig->enableJsonOption(JSON_UNESCAPED_UNICODE);

        if (!$this->wipeconfig->exists("wipe")) {
            $this->wipeconfig->set("wipe", 30);
            $this->wipeconfig->save();
        }
        nexuscore::$nextwipe = $this->wipeconfig->get("wipe");
        nexuscore::$defaultwipe = $this->wipeconfig->get("wipe");
        self::$shieldconfig = new Config($this->getDataFolder() . "shield.json", Config::JSON);
        Entity::registerEntity(NewArrow::class, false, ['NewArrow', 'minecraft:newarrow']);
        Entity::registerEntity(AntiGravityArrow::class, false, ['AntiGravityArrow', 'minecraft:antigravityarrow']);
        Entity::registerEntity(DoubleGravityArrow::class, false, ['DoubleGravityArrow', 'minecraft:doublegravityarrow']);
        Entity::registerEntity(HomingArrow::class, false, ['HomingArrow', 'minecraft:homingarrow']);
        Entity::registerEntity(HiPowerHomingArrow::class, false, ['HiPowerHomingArrow', 'minecraft:hipowerhomingarrow']); 
        $this->getScheduler()->scheduleRepeatingTask(new entitykilltask($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new EffectTask($this), 10);
	    $this->getScheduler()->scheduleRepeatingTask(new maxentitystask($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new shieldrechargetask($this), 10);
        $this->getScheduler()->scheduleRepeatingTask(new broadcasttask($this), 20 * 60 * 5);
        $this->getScheduler()->scheduleRepeatingTask(new checktweettask($this, $this->rewardconfig, $this->dayconfig), 36000);//30分*20(1800*20)
        $this->getScheduler()->scheduleRepeatingTask(new Sendtask(), 5);
        $this->getServer()->getCommandMap()->register($this->getName(), new johoCommand());
    }

    public function onArmorChange(EntityArmorChangeEvent $event){
        $player = $event->getEntity();
        if(!$player instanceof Player) return;
	/*
        self::$shieldconfig->set($player->getName(),"リチャージ");
        self::$shieldconfig->save();
	*/
    }

    function onEntityShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;
        $nbt = Entity::createBaseNBT(
            $entity->add(0, $entity->getEyeHeight(), 0),
            $entity->getDirectionVector(),
            ($entity->yaw > 180 ? 360 : 0) - $entity->yaw,
            -$entity->pitch
        );

        $diff = $entity->getItemUseDuration();
        $p = $diff / 20;
        $baseForce = min((($p ** 2) + $p * 2) / 3, 1);
        if ($event->getBow()->getCustomName() === 'POWER-BOW') {
            $diff = $entity->getItemUseDuration();
            $p = $diff / 20;
            $baseForce = min((($p ** 2) + $p * 2) ** 3, 1);
            $arrow = Entity::createEntity("NewArrow", $entity->getLevelNonNull(), $nbt, $entity, $baseForce >= 1);
            $event->setProjectile($arrow);
        } else if ($event->getBow()->getCustomName() === 'ANTIGRAVITY-BOW') {
            $arrow = Entity::createEntity("AntiGravityArrow", $entity->getLevelNonNull(), $nbt, $entity, $baseForce >= 1);
            $event->setProjectile($arrow);
        } else if ($event->getBow()->getCustomName() === 'DOUBLEGRAVITY-BOW') {
            $arrow = Entity::createEntity("DoubleGravityArrow", $entity->getLevelNonNull(), $nbt, $entity, $baseForce >= 1);
            $event->setProjectile($arrow);
        } else if ($event->getBow()->getCustomName() === 'HOMING-BOW') {
            $arrow = Entity::createEntity("HomingArrow", $entity->getLevelNonNull(), $nbt, $entity, $baseForce >= 1);
            $event->setProjectile($arrow);
        } else if ($event->getBow()->getCustomName() === 'HI-POWER-HOMING-BOW') {
            $arrow = Entity::createEntity("HiPowerHomingArrow", $entity->getLevelNonNull(), $nbt, $entity, $baseForce >= 1);
            $event->setProjectile($arrow);
        }
    
    }


  public function onShootBow(\pocketmine\event\entity\EntityShootBowEvent $event) : void{
    $entity = $event->getEntity();
    $bow = $event->getBow();
    $projectile = $event->getProjectile();

    if($entity instanceof \pocketmine\Player){
      if($bow->getCustomName() === "TP-BOW"){
        $projectile->namedtag->setByte("TeleportBow", 1);
      }
    }
  }

  public function onHitBlock(\pocketmine\event\entity\ProjectileHitBlockEvent $event) : void{
    $projectile = $event->getEntity();

    
if($projectile->namedtag->offsetExists("TeleportBow")){
  if($projectile->namedtag->getByte("TeleportBow") === 1){
    $owner = $projectile->getLevelNonNull()->getEntity($projectile->getOwningEntityId());
     if($owner instanceof Player) $owner->teleport($event->getRayTraceResult()->getHitVector());
  }
}
  }

  public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch (strtolower($command->getName())) {

            case "rand":
                if ($sender instanceof Player) {
                    $player = $sender->getPlayer();
                    $user = $player->getName();
                    if (!isset($args[1]) || !isset($args[1])) return false;
                    $max = $args[1];
                    $min = $args[0];
                    $a = random_int($min, $max);
                    if (isset($args[2])) {
                        if ($args[2] == true) {
                            $this->getServer()->broadcastTip("§e[rand]§a " . $user . " が" . $a . "を引きました！");
                        } else {
                            $sender->sendTip("§e[rand]§a 結果§f : §l§b" . $a . "§r");
                        }
                    } else {
                        $sender->sendTip("§e[rand]§a 結果§f : §l§b" . $a . "§r");
                    }
                } else {
                    $this->getLogger()->info("§e[rand] §cコンソールからの実行はできません");
                }
                break;
            case "lay":
                if ($sender instanceof Player) {
                    $player = $sender->getPlayer();
                    /** @var \pocketmine\Player $player */
                    $addActorPacket = new AddActorPacket();
                    $addActorPacket->entityRuntimeId = $id = Entity::$entityCount++;
                    $addActorPacket->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::ENDER_DRAGON];
                    $addActorPacket->position = $player;
                    $actorEventPacket = new ActorEventPacket();
                    $actorEventPacket->entityRuntimeId = $id;
                    $actorEventPacket->event = ActorEventPacket::ENDER_DRAGON_DEATH;
                    $pk = new BatchPacket();
                    $pk->addPacket($addActorPacket);
                    $pk->addPacket($actorEventPacket);
                    $player->sendDataPacket($pk);

                    /** @var \pocketmine\scheduler\TaskScheduler $scheduler */
                    $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player, $id): void {
                        $pk = new RemoveActorPacket();
                        $pk->entityUniqueId = $id;
                        $player->sendDataPacket($pk);
                    }), 20 * 10);
                }
                break;
            case "verify":
                if ($sender instanceof Player) {
                    $player = $sender->getPlayer();
                    $gamertag = $player->getName();
                    if (!isset($args[0])) return false;
                    $code = $args[0];
                    $url = 'https://www.playnexus.online/twitter/oauth/api/v0.1-beta/verify.php?token=gkzyaun9ctp355d64rp9r4fkewk66kyrw9xgp62ph45jkrxpx9t25utac7zesiji88muieyzbmj8yxfucy6iizin3r37rpsjrm2d';

                    $data = array(
                        "gamertag" => $gamertag,
                        "code" => $code
                    );
                    $data = http_build_query($data, "", "&");

                    // header
                    $header = array(
                        "Content-Type: application/x-www-form-urlencoded",
                        "Content-Length: " . strlen($data)
                    );

                    $context = array(
                        "http" => array(
                            "method" => "POST",
                            "header" => implode("\r\n", $header),
                            "content" => $data
                        )
                    );

                    $html = @file_get_contents($url, false, stream_context_create($context));
                    $sender->sendMessage($html);

                }
                break;
            case "verify-ad":
                if ($sender instanceof Player) {
                    $player = $sender->getPlayer();
                    $gamertag = $player->getName();
                    if (!isset($args[0])) return false;
                    $code = $args[0];
                    $url = 'https://www.playnexus.online/twitter/oauth/api/v0.1-beta/verify-ad.php?token=gkzyaun9ctp355d64rp9r4fkewk66kyrw9xgp62ph45jkrxpx9t25utac7zesiji88muieyzbmj8yxfucy6iizin3r37rpsjrm2d';

                    $data = array(
                        "gamertag" => $gamertag,
                        "code" => $code
                    );
                    $data = http_build_query($data, "", "&");

                    // header
                    $header = array(
                        "Content-Type: application/x-www-form-urlencoded",
                        "Content-Length: " . strlen($data)
                    );

                    $context = array(
                        "http" => array(
                            "method" => "POST",
                            "header" => implode("\r\n", $header),
                            "content" => $data
                        )
                    );

                    $html = @file_get_contents($url, false, stream_context_create($context));
                    $sender->sendMessage($html);

                }
                break;
                case "mywarp":
                case "myw":
                if ($sender instanceof Player) {
                    $player = $sender->getPlayer();
                    $window = new SimpleWindowForm("mywarp menu", "§5Mywarp menu", "希望の動作を選択してください");
                    $window->addButton("warp", "ワープする");
                    $window->addButton("add", "ワープ地点を追加する");
                    $window->addButton("delete", "ワープ地点を削除する");            
                    $window->showTo($player);
                }
                break;
                case "tpp":
                if ($sender instanceof Player) {
                    $name = $sender->getName();
                    //var_dump($args);
                    if(empty($args)){
                    $sender->sendMessage("転送リクエストを送りたい相手の名前を指定してください。");
                    return true;
                    }
                    $teleportplayer = Server::getInstance()->getPlayer($teleportplayername);
                    $teleportplayername = $teleportplayer->getName();

                    if($teleportplayer instanceof Player){
                        if($this->tppconfig->exists($name)){
                            $sender->sendMessage("転送リクエストを送信する前にあなたに届いているリクエストを処理してください。");
                            return true;
                        }
                        if($this->tppconfig->exists($teleportplayername)){
                            $sender->sendMessage("そのユーザーには保留中のリクエストがあります。そのユーザーが保留中のリクエストを処理するまで新たなリクエストは送れません。");
                            return true;
                        }
                        $getall = $this->tppconfig->getAll();
                        $alreadysend = false;
                        foreach($getall as $key => $value){
                            //var_dump($value);
                            if($value == $name){
                                $alreadysend = true;
                                
                                if(!(Server::getInstance()->getPlayer($key) instanceof Player)) {
                                    $this->tppconfig->remove($key);
                                    $this->tppconfig->save();
                                }

                            }
                        }
                        if($alreadysend){
                            $sender->sendMessage("あなたは既にリクエストを".$key."さんに送信しているようです。そのリクエストが処理されるまでお待ちください。");
                            return true;
                        }
                        $this->tppconfig->set($teleportplayer->getName(), $name);
                        $this->tppconfig->save();
                        $sender->sendMessage("転送リクエストを送信しました。相手の応答をお待ち下さい。");
                        $teleportplayer->sendMessage("§5".$name."さんがあなたのいる場所にテレポートしたいようです。リクエストを許可する場合は「accept-tpp」、拒否する場合は「deny-tpp」を入力してください。");
                        return true;
                    }
                    $sender->sendMessage("相手がオフラインのようです。");
                    return true;
                }
                break;
                case "wwarp":
                if(!$sender instanceof Player) {
                    $sender->sendMessage("ワールド内から実行してください。");
                    return true;
                }
                $customForm = new CustomForm("world warp");
                $customForm->addLabel("[ワールド間転送]行きたいワールドを選択してください。");
                $customForm->addDropdown("Level", WMAPI::getAllLevels());
                $sender->getServer()->getPlayer($sender->getName())->sendForm($customForm); 
                return true;
        }
        return true;
    }
    public static function handleCustomFormResponse(Player $player, $data, CustomForm $form) {
        if($data === null){
            return;
	    }
	
	    $levelsname = WMAPI::getAllLevels()[$data[1]];
	    if(!Server::getInstance()->isLevelGenerated($levelsname)) {
            $player->sendMessage("このワールドは存在しません！");
            return;
        }

        if(!Server::getInstance()->isLevelLoaded($levelsname)) {
            Server::getInstance()->loadLevel($levelsname);
        }

        $level = Server::getInstance()->getLevelByName($levelsname);

        $player->teleport($level->getSafeSpawn());
        $player->sendMessage($levelsname."に転送しました。");
        return;
        }

    public function onChat(PlayerChatEvent $event){
        $message = $event->getMessage();
        $player = $event->getPlayer();
        $name = $player->getName();
        if($message == "accept-tpp"){
            if($this->tppconfig->exists($name)){
                //var_dump($this->tppconfig->exists($name));
                //var_dump($this->tppconfig->get($name));
                $teleportplayername = $this->tppconfig->get($name);
                $teleportplayer = Server::getInstance()->getPlayer($teleportplayername);
                if($teleportplayer instanceof Player){
                    $world = $player->getLevel();
                    if($world->getFolderName() === "creative"){
                    $this->tppconfig->remove($name);
                    $this->tppconfig->save();
                    $player->sendMessage($teleportplayername."さんの転送リクエストを許可しようとしましたが、あなたがクリエイティブワールドにいるためできませんでした。リクエストは破棄されました。");
                    $teleportplayer->sendMessage($name."さんがあなたの転送リクエストを許可しようとしましたが、転送禁止ワールドにいるためできませんでした。リクエストは破棄されました。");
                    return;
                    }
                    $teleportplayer->teleport(new Position($player->getX(), $player->getY(), $player->getZ(), $world));
                    $this->tppconfig->remove($name);
                    $this->tppconfig->save();
                    $player->sendMessage($teleportplayername."さんの転送リクエストを許可しました。");
                    $teleportplayer->sendMessage($name."さんがあなたの転送リクエストを許可したためあなたを転送しました。");
                    $event->setCancelled(true);
                }
            }
        }
        if($message == "deny-tpp"){
            if($this->tppconfig->exists($name)){
                $teleportplayername = $this->tppconfig->get($name);
                $teleportplayer = Server::getInstance()->getPlayer($teleportplayername);

                    $this->tppconfig->remove($name);
                    $this->tppconfig->save();
                    $player->sendMessage($teleportplayername."さんの転送リクエストを拒否しました。");
                if($teleportplayer instanceof Player){
                    $teleportplayer->sendMessage($name."さんがあなたの転送リクエストを拒否しました。");
                }
                $event->setCancelled(true);
            }
        }
    }

    public function onResponse(PlayerWindowResponse $event){
        $player = $event->getPlayer();
        $form = $event->getForm();

        
        if($form->isClosed()) {
            return;
        }

        if($form->getName() === "mywarp menu"){
            switch($form->getClickedButton()->getText()){
                case "ワープする":
                $mywarpconfig = new Config($this->getDataFolder() . "mywarp/".$player->getName().".yml", Config::YAML);
                $window = new SimpleWindowForm("mywarp warp", "§5Mywarp menu", "ワープしたい場所を選んでください");
                $datas = $mywarpconfig->getAll(true);
                foreach($datas as $data){
                    if($data !== null || $data !== undefined){
                        $window->addButton($data, $data);
                    }
                }
                $window->showTo($player);
                break;
                case "ワープ地点を追加する":
                $window = new CustomWindowForm("mywarp add", "§5Mywarp menu", "項目を記入してください");
                $window->addInput("warpname", "ワープ地点名を記入してください");
                $window->showTo($player);
                break;
                case "ワープ地点を削除する":
                $mywarpconfig = new Config($this->getDataFolder() . "mywarp/".$player->getName().".yml", Config::YAML);
                $window = new SimpleWindowForm("mywarp delete", "§5Mywarp menu", "削除したい地点を選んでください");
                $datas = $mywarpconfig->getAll(true);
                foreach($datas as $data){
                    if($data !== null || $data !== undefined){
                        $window->addButton($data, $data);
                    }
                }
                $window->showTo($player);
                break;
                default:
                $player->sendMessage($form->getClickedButton()->getText());
                break;
            }
        }else if($form->getName() === "mywarp add"){
            $mywarpconfig = new Config($this->getDataFolder() . "mywarp/".$player->getName().".yml", Config::YAML);
            $warpname = $form->getElement("warpname")->getFinalValue();
            $mywarpconfig->set($warpname, $player->getX().",".$player->getY().",".$player->getZ().",".$player->getLevel()->getFolderName());
            $mywarpconfig->save();
            $player->sendMessage("記録しました！");
        }else if($form->getName() === "mywarp delete"){
            $mywarpconfig = new Config($this->getDataFolder() . "mywarp/".$player->getName().".yml", Config::YAML);
            $warpname = $form->getClickedButton()->getText();
            $mywarpconfig->remove($warpname);
            $mywarpconfig->save();
            $player->sendMessage("削除しました！");
        }else if($form->getName() === "mywarp warp"){
            $mywarpconfig = new Config($this->getDataFolder() . "mywarp/".$player->getName().".yml", Config::YAML);
            $warpname = $form->getClickedButton()->getText();
            $data = $mywarpconfig->get($warpname);
            $value = explode(",", $data);
            $world = Server::getInstance()->getLevelByName($value[3]);
            $player->teleport(new Position(floatval($value[0]), floatval($value[1]), floatval($value[2]), $world));
            $player->sendMessage("指定した地点に転送しました！");
        }
    }
public function onJoin(PlayerJoinEvent $event)
    {
    $player_name  = $event->getPlayer()->getName();
    if($this->preloginconfig->exists($player_name)){
    $this->preloginconfig->remove($player_name);
    $this->preloginconfig->save();
    }
        $player = $event->getPlayer();
        if(!self::$shieldconfig->exists($player->getName())){
        self::$shieldconfig->set($player->getName(),"リチャージ");
        self::$shieldconfig->save();
        }
        $items = self::$itemconfig->get($player->getName(), null);
        if ($items === null||count($items) === 0) {
            return;
        }
        $error = false;
        foreach ($items as $id => $itemdata) {
            $item = Item::jsonDeserialize($itemdata);
            if (!$player->getInventory()->canAddItem($item)) {
                $error = true;
                self::$itemconfig->set($player->getName(), $items);
                self::$itemconfig->save();
                $player->sendMessage("あなたのインベントリがいっぱいなため追加不能な報酬が存在します。アイテム整理後、再ログインをお願いします。");
                break;
            }
            unset($items[$id]);
            $player->getInventory()->addItem($item);
        }
        if (!$error) {
            $player->sendMessage("SNSパートナー報酬を受け取りました！");
            self::$itemconfig->set($player->getName(), null);
            self::$itemconfig->save();
        }
    }

public function onDamage(EntityDamageByEntityEvent $event)
    {
             if ($event->getEntity() instanceof Player) {
                    $player = $event->getEntity();
                    $killer = $event->getDamager();
                    $playerN = $player->getName();
                    $shield = self::$shieldconfig->get($playerN);
                    if($shield>0){
                    $shielddamage = $shield - floor($event->getFinalDamage());
                    if($shielddamage < 0) $shielddamage = 0;
                    self::$shieldconfig->set($player->getName(),$shielddamage);
                    self::$shieldconfig->save();
                    $player->sendTip("§5あなたはシールドに".floor($event->getFinalDamage())."ダメージを受けました...!(残シールド値:".$shielddamage.")");
                    $event->setCancelled();
                    return;
                    }
                    //$killerN = $killer->getName();
                    $armorinventory = $player->getArmorInventory();
                    if ($event->getFinalDamage() >= $event->getEntity()->getHealth()) {
                        $event->setCancelled();
                        $player->setHealth(20);
                        $player->setFood(20);
                        if($armorinventory->getChestplate()->getCustomName()!=="太古の鎧(胴)Resurrection"){
                        $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                        foreach ($this->getServer()->getOnlinePlayers() as $players) {
                            $killer_msg = /*"§4".$killerN."§5が*/"§5".$playerN."§4は死亡しました…";
                            $players->sendMessage($killer_msg);
			            }
                        if($armorinventory->getChestplate()->getCustomName()==="太古の鎧(胴)Resurrection-USED"){
                            $armor = $armorinventory->getChestplate();
                            $armor->setCustomName("太古の鎧(胴)Resurrection");
                            $armorinventory->setChestPlate($armor);
                        }
                        self::$shieldconfig->set($player->getName(),"リチャージ");
                        self::$shieldconfig->save();
                        }else{
                            $armor = $armorinventory->getChestplate();
                            $armor->setCustomName("太古の鎧(胴)Resurrection-USED");
                            $armorinventory->setChestPlate($armor);
                        foreach ($this->getServer()->getOnlinePlayers() as $players) {
                            $killer_msg = "§4".$playerN."§5は特殊効果[Resurrection]が発動し、復活しました！";
                            $players->sendMessage($killer_msg);
			            }
                        
                        }
		            }      
               }
        $entity = $event->getEntity();
        if(!$entity instanceof Player) return;
        $armorinventory = $entity->getArmorInventory();
        $armorpower = 0;
        //var_dump($armorinventory->getHelmet()->getCustomName());
        if($armorinventory->getHelmet()->getCustomName()==="太古の鎧(頭)" || $armorinventory->getHelmet()->getCustomName()==="太古の鎧(頭)NIGHTVISION") $armorpower = $armorpower + 1;
        if($armorinventory->getChestplate()->getCustomName()==="太古の鎧(胴)" || $armorinventory->getChestplate()->getCustomName()==="太古の鎧(胴)Resurrection" || $armorinventory->getChestplate()->getCustomName()==="太古の鎧(胴)Resurrection-USED") $armorpower = $armorpower + 1;
        if($armorinventory->getLeggings()->getCustomName()==="太古の鎧(脚)" || $armorinventory->getLeggings()->getCustomName()==="太古の鎧(脚)HIGHJUMP") $armorpower = $armorpower + 1;
        if($armorinventory->getBoots()->getCustomName()==="太古の鎧(靴)" || $armorinventory->getBoots()->getCustomName()==="太古の鎧(靴)SPEED" || $armorinventory->getBoots()->getCustomName()==="太古の鎧(靴)SPEED-V2") $armorpower = $armorpower + 1;

        if($armorpower >= 1){
            $entity->addEffect(new EffectInstance(Effect::getEffect(1), 100, $armorpower, false));
            $entity->addEffect(new EffectInstance(Effect::getEffect(5), 100, $armorpower*2, false));
        }
        if($armorpower >= 2){
            $entity->addEffect(new EffectInstance(Effect::getEffect(14), 100, 255, false));
            $entity->hidePlayer($entity);
            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($entity): void {
                $entity->showPlayer($entity);
            }), 20 * 5);
        }
        if($armorpower == 4){
            $entity->addEffect(new EffectInstance(Effect::getEffect(10), 100, $armorpower*2, false));
            $entity->addEffect(new EffectInstance(Effect::getEffect(11), 100, $armorpower, false));
            $entity->addEffect(new EffectInstance(Effect::getEffect(12), 100, $armorpower, false));
            $entity->addEffect(new EffectInstance(Effect::getEffect(22), 100, $armorpower, false));
            $entity->addEffect(new EffectInstance(Effect::getEffect(21), 100, $armorpower*3, false));
        }


        $armordefencekb = $armorpower * 20;
        if($armordefencekb == 0) $armordefencekb = 1;
        if($armordefencekb == 80) $armordefencekb = 160;
        //var_dump($armordefencekb);
        $kb = $event->getKnockBack()/$armordefencekb;
        //var_dump($event->getKnockBack());
        //var_dump($kb);
        $event->setKnockBack($kb);
    }

  

  public function onEffectAdd(\pocketmine\event\entity\EntityEffectAddEvent $event) : void{
    $effect = $event->getEffect();
    $entity = $event->getEffect();

    if($entity instanceof \pocketmine\Player){
      if($effect->getId() === \pocketmine\entity\Effect::INVISIBILITY){
        $pk = new \pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket();
        $pk->entityRuntimeId = $entity->getId();
        $pk->head = $pk->chest = $pk->legs = $pk->feet = \pocketmine\item\ItemFactory::get(\pocketmine\item\ItemIds::AIR);
        foreach($entity->getArmorInventory()->getViewers() as $player){
          $player->sendDataPacket($pk);
        }
      }
    }
  }

  public function onEffectRemove(\pocketmine\event\entity\EntityEffectRemoveEvent $event) : void{
    $effect = $event->getEffect();
    $entity = $event->getEntity();

    if($entity instanceof Player){
      if($effect->getId() === Effect::INVISIBILITY){
        $armorInventory = $entity->getArmorInventory();
        $this->cancelGuard = true;
        $armorInventory->sendContents($armorInventory->getViewers());
        $this->cancelGuard = false;
      }
    }
  }

  public function onPacketSend(\pocketmine\event\server\DataPacketSendEvent $event) : void{
    $pk = $event->getPacket();

    if($pk instanceof \pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket){
    $level = array_filter(Server::getInstance()->getLevels(), function(Level $level) use($pk) : bool{ return $level->getEntity($pk->entityRuntimeId) !== null; })[0] ?? null;
      if($level === null){
        return;
      }

      $entity = $level->getEntity($pk->entityRuntimeId);
      if($entity instanceof Player){
        if($entity->getEffect(Effect::INVISIBILITY) !== null and !$this->cancelGuard){
          $event->setCancelled(true);
        }
      }
    }
  }

    public function onDisable()
    {
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $p) {
            $p->transfer("server.playnexus.online", 7777);
        }
    }

    public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        $playerN = $player->getName();
        if ($event->getPlayer()->getY() < -5) {
            $event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSafeSpawn());
            $player->setHealth(20);
            $player->setFood(20);
            foreach ($this->getServer()->getOnlinePlayers() as $players) {
                $void_msg = "§4".$playerN."§5が奈落におちました！足元には気をつけてください…";
                $players->sendMessage($void_msg);
            }
        }
    }
  public function onPacketReceive(DataPacketReceiveEvent $event):void{
    $packet = $event->getPacket();
    if($packet instanceof LoginPacket){
   // var_dump($packet->clientData['PlayFabId']);
    }
  }
  public function onPacketSends(DataPacketSendEvent $event):void{
    $packet = $event->getPacket();
    if(!$packet instanceof DisconnectPacket) return;
    $player_name  = $event->getPlayer()->getName();
    if($this->preloginconfig->exists($player_name)){
    $message =  $player_name.'さんがログインをキャンセルしました。';
    $this->preloginconfig->remove($player_name);
    $this->preloginconfig->save();
    }
    if(!isset($message)) return; //$messageが定義されているか
    Server::getInstance()->broadcastMessage("§a".$message);
  }
    public function onPreLogin(PlayerPreLoginEvent $event){
    $player = $event->getPlayer();
    $name   = $player->getName();
    $this->preloginconfig->set($name,true);
    $this->preloginconfig->save();
    Server::getInstance()->broadcastMessage("§a".$name."さんがサーバーに接続中です");
    }
    public static function getInstance():self {
        return self::$main;
    }
 
    public function isOn(Player $player) {
        $tag = $player->namedtag;
        if ($tag->offsetExists($this->getName())) if (!$tag->getInt($this->getName()) == 0) return false;
        return true;
    }
}
