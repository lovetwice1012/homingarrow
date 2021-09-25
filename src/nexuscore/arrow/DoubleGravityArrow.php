<?php

namespace nexuscore\arrow;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\{
	Entity,
	Living,
    Location
};
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\math\RayTraceResult;

final class DoubleGravityArrow extends Arrow{
    protected $gravity = 1;
	protected $damage = 4.0;
	protected $punchKnockback = 10.0;
	private $shooter;

	public function __construct(Location $location, ?CompoundTag $nbt = null, ?Entity $entity = null, bool $critical = false ,?World $level = null){
		parent::__construct(
			$location,
			$entity,
			$critical,
			$nbt
		);
		if($entity === null) return;
		$this->setMotion($entity->getDirectionVector()->normalize()->multiply(3)); //速度 この値を基本速度に掛け算してます
		$this->shooter = $entity;
	}

	public function entityBaseTick(int $tick = 1):bool{
		foreach($this->level->getEntities() as $entity){
			if(
				!$entity instanceof Living
					or
				$this->shooter === null
					or
				$this->shooter->getId() === $entity->getId() //当たり判定を大きくしすぎると打った人に当たるのでそれの防止
					or
				$this->getLocation()->distance($entity->getLocation()) > 10 //当たり判定
					or
				($bb = $entity->getBoundingBox()) === null //エンティティが当たり判定を持っているか
			) continue;

			$this->onHitEntity(
				$entity,
				new RayTraceResult(
					$bb,
					1, //ここ間違ってるかも... ごめん...
					$entity
				)
			);
        break;
		}
		return parent::entityBaseTick($tick);
	}
}