<?php
namespace nexuscore\arrow;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\{
	Entity,
	Living,
    Location
};
use pocketmine\entity\Human;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\math\RayTraceResult;

final class HiPowerHomingArrow extends Arrow{
        protected $gravity = 0;
	protected $damage = 5000.0;
	protected $punchKnockback = 50.0;
	private $shooter;

	public function __construct(Location $level, ?CompoundTag $nbt = null, ?Entity $entity = null, bool $critical = false){
		parent::__construct(
			$level,
			$entity,
			$critical,
			$nbt
		);
		if($entity === null) return;
		//$this->setMotion($entity->getDirectionVector()->normalize()->multiply(0.5));
		$this->shooter = $entity;
	}

	public function entityBaseTick(int $tick = 1):bool{
 	  $newTarget = $this->level->getNearestEntity($this->getLocation()->getX(),$this->getLocation()->getY(),$this->getLocation()->getZ(), 500.0, Living::class);
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
		$vector = $currentTarget->getPosition()->add(0, $currentTarget->getEyeHeight() / 2, 0)->subtract($this->getLocation()->getX(),$this->getLocation()->getY(),$this->getLocation()->getZ())->divide(500.0);

		$distance = $vector->lengthSquared();
		if($distance < 1){
		  $diff = $vector->normalize()->multiply(20 * (1 - sqrt($distance)) ** 2);
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
				$this->getLocation()->distance($entity->getLocation()) > 10
					or
				($bb = $entity->getBoundingBox()) === null
			) continue;

			$this->onHitEntity(
				$entity,
				new RayTraceResult(
					$bb,
					1,
					$entity
				)
			);
        break;
		}
		return parent::entityBaseTick($tick);
	}
}
