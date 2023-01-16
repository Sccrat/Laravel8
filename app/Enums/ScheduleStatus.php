<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
abstract class ScheduleStatus extends Enum {
  const Process = "process";
  const Closed = "closed";
  const TheoricClosed = "theoric_closed";
  const ClosedError= "closed_error";
  const Paused= "paused";
  const Pendding= "pendding";
}
