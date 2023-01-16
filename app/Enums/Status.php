<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
 abstract class Status extends Enum {
     const Active = "active";
     const Inactive = "inactive";
     const Maintenance = "maintenance";
     const Inabilited = "inabilited";
     const Vacations = "vacations";
     //inabilited, vacations
 }
