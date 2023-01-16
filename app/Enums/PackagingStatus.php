<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
 abstract class PackagingStatus extends Enum {
     const Count = "to_count";
     const Approved = "approved";
     const Stored = "stored";
     const Validate = "validate";
    }
