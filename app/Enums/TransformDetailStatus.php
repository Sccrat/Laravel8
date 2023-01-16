<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
 abstract class TransformDetailStatus extends Enum {
     const Pendding = "pendding";
     const Removed = "removed";
     const Transformed = "transformed";
     const Stored = 'stored';
     const Unjoin = 'unjoin';
 }
