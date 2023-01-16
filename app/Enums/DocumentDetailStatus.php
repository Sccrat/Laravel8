<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
 abstract class DocumentDetailStatus extends Enum {
     const Count = "count";
     const Adjustment = "adjustment";
     const Validate = "validate";
     const Closed = "closed";
 }