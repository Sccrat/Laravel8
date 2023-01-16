<?php

namespace App\Enums;

 abstract class SizeKey extends Enum {
     const DISTRIBUTION_CENTER = 'dc_size';
     const WAREHOUSE = 'warehouse_size';
     const ZONE = 'zone_size';
     const MODULE = 'module_size';
     const ROW = 'row_size';
     const LEVEL = 'level_size';
 }
