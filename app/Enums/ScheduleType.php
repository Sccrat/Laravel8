<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
 abstract class ScheduleType extends Enum {
     const Receipt = "receipt";
     const Deliver = "deliver";
     const Task = "task";
     const Stock = "stock";
     const Transform = "transform";
     const Unjoin = "unjoin";
     const Restock = "restock";
     const Store = "store";
     const Count = "count_detail";
     const Validate = "validate_adjust";
     const Resupply = "resupply_picking_zone";
     const EnlistPlan = "enlist_plan";
     const Pallet = "pallet";
     const Dispatch = "dispatch";
 }
