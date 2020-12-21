<?php

namespace skh6075\customitemloader\item;

use pocketmine\item\Item;

class CustomItem extends Item{

    /** @var int */
    private $maxStackSize = 1;


    public function __construct(int $id, int $meta = 0, string $name = "Unknown", int $maxStackSize) {
        parent::__construct($id, $meta, $name);
        $this->maxStackSize = $maxStackSize;
    }

    public function getMaxStackSize(): int{
        return $this->maxStackSize;
    }
}