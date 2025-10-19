<?php

namespace Kettasoft\Filterable\Foundation\Events;

use Kettasoft\Filterable\Contracts\Matchable;

/**
 * Class representing the status of a filtered event.
 * 
 * @implements \Stringable
 * @implements \Kettasoft\Filterable\Contracts\Matchable
 */
class FilterableState implements \Stringable, Matchable
{
    /**
     * The status of the filtered event.
     *
     * @var string
     */
    public string $status;

    /**
     * Create a new FilteredEventStatus instance.
     *
     * @param string $status
     * @return void
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * Check if the filtered event has the given status.
     *
     * @param mixed $status
     * @return bool
     */
    public function is(mixed $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Get the status of the filtered event.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Convert the FilteredEventStatus instance to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getStatus();
    }
}
