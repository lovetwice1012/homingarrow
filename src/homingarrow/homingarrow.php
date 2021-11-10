<?php

namespace homingarrow;

use nexuscore\arrow\HomingArrow;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityShootBowEvent;

class homingarrow extends PluginBase implements Listener
{
    public $config;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if(!isset($this->config->get("name"))){
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        }
        Entity::registerEntity(HomingArrow::class, false, ['HomingArrow', 'minecraft:homingarrow']);
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
    if ($event->getBow()->getCustomName() === $this->config->get("name")) {
            $arrow = Entity::createEntity("HomingArrow", $entity->getLevelNonNull(), $nbt, $entity, $baseForce >= 1, $this->config->get("gravity"), $this->config->get("damage"), $this->config->get("punchKnockback"));
            $event->setProjectile($arrow);
        } 
    }
}
