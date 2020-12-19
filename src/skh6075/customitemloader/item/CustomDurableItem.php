<?php

namespace skh6075\customitemloader\item;

use pocketmine\item\Durable;

class CustomDurableItem extends Durable{

    /** @var int */
    private $maxDurability = 0;


    public function __construct(int $id, int $meta, string $name, int $maxDurability) {
        parent::__construct($id, $meta, $name);
        $this->maxDurability = $maxDurability;
    }

    public function getMaxDurability(): int{
        return $this->maxDurability;
    }
}