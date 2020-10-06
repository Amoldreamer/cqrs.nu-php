<?php

declare(strict_types=1);

namespace Cafe\Domain\Tab\Events;

final class TabOpened extends DomainEvent
{
    public string $tabId;
    public int $tableNumber;
    public string $waiter;

    public function __construct($tabId, $tableNumber, $waiter)
    {
        $this->tabId = $tabId;
        $this->tableNumber = $tableNumber;
        $this->waiter = $waiter;
    }

    public static function fromPayload(array $payload) : self
    {
        return new self($payload['tabId'], $payload['tableNumber'], $payload['waiter']);
    }

    public function aggregateId() : string
    {
        return $this->tabId;
    }

    public function name() : string
    {
        return 'tab_opened';
    }
}