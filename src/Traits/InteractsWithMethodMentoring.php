<?php

namespace Kettasoft\Filterable\Traits;

trait InteractsWithMethodMentoring
{
  /**
   * Mentors of filter methods.
   * @var array
   */
  protected $mentors = [];

  /**
   * Get mentors.
   * @return array
   */
  public function getMentors(): array
  {
    return $this->mentors;
  }

  /**
   * Set method mentors.
   * @param array $mentors
   * @param mixed $override
   * @return static
   */
  public function setMentors(array $mentors, $override = false): static
  {
    $this->mentors = $override ? $mentors : array_merge($this->mentors, $mentors);
    return $this;
  }
}
