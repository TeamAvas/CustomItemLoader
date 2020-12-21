<?php

namespace skh6075\customitemloader\item;

use pocketmine\item\Durable;

class CustomDurableItem extends Durable{

    /** @var int */
    private $maxDurability = 0;
    /** @var int */
    private $maxStackSize = 1;


    public function __construct(int $id, int $meta, string $name, int $maxDurability, int $maxStackSize) {
        parent::__construct($id, $meta, $name);
        $this->maxDurability = $maxDurability;
        $this->maxStackSize = $maxStackSize;
    }

    public function getMaxStackSize(): int{
        return $this->maxStackSize;
    }

    public function getMaxDurability(): int{
        return $this->maxDurability;
    }
}