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
		$this->setMotion($entity->getDirectionVector()->normalize()->multiply(0.5));
		$this->shooter = $entity;
	}

	public function entityBaseTick(int $tick = 1):bool{
 	  $newTarget = $this->level->getNearestEntity($this->getLocation(), 150.0, Living::class);
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
		$vector = $currentTarget->getPosition()->add(0, $currentTarget->getEyeHeight() / 2, 0)->subtract($this->getLocation())->divide(150.0);

		$distance = $vector->lengthSquared();
		if($distance < 1){
		  $diff = $vector->normalize()->multiply(0.2 * (1 - sqrt($distance)) ** 2);
		  $this->motion->x += $diff->x;
		  $this->motion->y += $diff->y;
		  $this->motion->z += $diff->z;
		}
	  }
		foreach($this->level->getEntities() as $entity){
			if(
				!$entity instanceof Living
					or
				$this->shooter === null
					or
				$this->shooter->getId() === $entity->getId()
					or
				$this->distance($entity) > 3
					or
				($bb = $entity->getBoundingBox()) === null
			) continue;

			$this->onHitEntity(
				$entity,
				new RayTraceResult(
					$bb,
					$this->getDirection(),
					$entity
				)
			);
        break;
		}
		return parent::entityBaseTick($tick);
	}
}
