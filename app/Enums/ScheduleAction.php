<?php

namespace App\Enums;

// use App\Enums\Enum;
/**
 *
 */
abstract class ScheduleAction extends Enum
{
   const Assign = "assign";
   const Remove = 'remove';
   const Transform = 'transform';
   const Store = 'store';
   const Invalid = 'invalid';
   const Unjoin = 'unjoin';
   const Count = 'count';
   const Receipt = 'receipt';
   const Validate = 'validate';
   const Authorize = 'authorize';
   const PrintCodes = 'print_codes';
   const GeneratePallet = 'generate_pallet';
   const ToStock = 'to_stock';
   const Picking = 'count_position';
   const PrintCode = 'print_codes';
   const Samples = 'samples';
   const Edit = 'edit';
   const Reprocess = 'reprocess';
   const Deliver = 'deliver';
   const Enlist = 'enlist';
   const Generate = 'generate';
   const Picking_empaque = 'picking_empaque';
   const Packing = 'packing';
   const PackingZones = 'packing_zones';
   const Dispatch = 'dispatch';
   const Inventary = "count_inventary";
   const Dispatch_plan = "Dispatch_plan";
   const ReceiptTulas = "Receipt_tulas";
   const ReceiptSchedule = "Receipt_schedule";
   const ReceiptScheduleOp = "Receipt_schedule_op";
   const ReceiptValidateTulas = "Receipt_validate_tulas";
   const PickingAction = 'picking_action';
   const PickingMassiveAction = 'picking_massive_action';
   const PickingAllocationMassiveAction = 'picking_allocation_massive_action';
   const PrintWaves = 'print_waves';
   const ReubicateProductsWaves = 'reubicate_products_waves';
   const ReubicateAllocationMassive = 'reubicate_allocation_massive';
   const PrintAllocationMassive = 'print_allocation_massive';
   const ManagePackingMassive = 'manage_packing_massive';
   const ManagePackingAllocationMassive = 'manage_packing_allocation_massive';
   const PackingMassiveAction = "packing_massive_action";
   const PackingAllocationMassiveAction = "packing_allocation_massive_action";
   const ValidateFacturation = "Validate_Facturation";
   const ValidateTransport = "Validate_Transport";
   const CancelDocument = "Cancel_Document";
   const SuspendDocumento = "Suspend_Document";
   const OrderReturn = "OrderReturn";
   const PackingAction = "PackingAction";
   const ReubicarPackingAction = "ReubicarPackingAction";
   const DocumentoPedido = "DocumentoPedido";
   const CitaDespacho = "CitaDespacho";
   const Traslados = "Traslados";
   const Recogida = "Recogida";
   const Comex = "Comex";
   const Documentos = "Documentos";
   const Inventario = "Inventario";
   const ScheduleTransport = "Schedule_Transport";
   const CollectTulas = "Collect_Tulas";
   const DispatchTulas = "Dispatch_Tulas";
   const ManagementReceipt = "Management_Receipt";
   const DispatchTulasDriver = "Dispatch_Tulas_Driver";
   const ReceiveTulas = "Receive_Tulas";
   const TulasWeight = "tulas_weight";
}
