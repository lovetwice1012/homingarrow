<?php
namespace nexuscore\arrow;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\{
	Entity,
	Living
};
use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\math\RayTraceResult;

final class HomingArrow extends Arrow{
        protected $gravity = 0;
	protected $damage = 3.0;
	protected $punchKnockback = 2.0;
	private $shooter;

	public function __construct(Level $level, CompoundTag $nbt, ?Entity $entity = null, bool $critical = false){
		parent::__construct(
			$level,
			$nbt,
			$entity,
			$critical
		);
		if($entity === null) return;
		$this->setMotion($entity->getDirectionVector()->normalize()->multiply(0.30)); //速度 この値を基本速度に掛け算してます
		$this->shooter = $entity;
	}

	public function entityBaseTick(int $tick = 1):bool{
 	  $newTarget = $this->level->getNearestEntity($this->getLocation(), 100.0, Living::class);
          if($newTarget instanceof Living){
            if($this->shooter === null){
	      $currentTarget = null;
	    }else{
              if($this->shooter->getId() !== $newTarget->getId()){
	        $currentTarget = $newTarget;
	      }else{
	        $currentTarget = null;
	      }
	    }
	  }else{
            $currentTarget = null;
          }

	  if($currentTarget !== null){
		$vector = $currentTarget->getPosition()->add(0, $currentTarget->getEyeHeight() / 2, 0)->subtract($this->getLocation())->divide(100.0);

		$distance = $vector->lengthSquared();
	  }
		foreach($this->level->getEntities() as $entity){
			if(
				!$entity instanceof Living
					or
				$this->shooter === null
					or
				$this->shooter->getId() === $entity->getId() //当たり判定を大きくしすぎると打った人に当たるのでそれの防止
					or
				$this->distance($entity) > 5 //当たり判定
					or
				($bb = $entity->getBoundingBox()) === null //エンティティが当たり判定を持っているか
			) continue;

			$this->onHitEntity(
				$entity,
				new RayTraceResult(
					$bb,
					$this->getDirection(), //ここ間違ってるかも... ごめん...
					$entity
				)
			);
        break;
		}
		return parent::entityBaseTick($tick);
	}
}
