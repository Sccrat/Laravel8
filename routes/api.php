<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');



$api->version('v1', ['namespace' => 'App\Http\Controllers'], function ($api) {
    $api->group(['middleware' => ['jwt.verify']], function ($api) {


        //Get user profile
        $api->resource('me', 'ProfileController');

        //Structures types
        $api->resource('structure-types', 'StructureTypeController');

        //Get the whole tree of a structure
        $api->resource('structures-children', 'StructureController@children');

        //StructureAreaLevelController
        $api->resource('structure-area-levels', 'StructureAreaLevelController');

        //Structures
        $api->resource('structures', 'StructureController');
        $api->get('structure-parent/{id}', 'StructureController@getParentName');
        //Structure_Aera
        $api->resource('structure-areas', 'StructureAreaController');
        //Machines

        $api->resource('machine-types', 'MachineTypeController');
        $api->get('machine-types-all', 'MachineTypeController@getAllTypeMachines');
        //Work Area
        $api->resource('work-areas', 'WorkAreaController');
        $api->get('work-areas-structure/{id}', 'WorkAreaController@getByStructure');
        //Grouops
        $api->resource('groups', 'GroupController');
        $api->get('groups-list', 'GroupController@getAllGroups');

        $api->get('storage-areas/{id}', 'StructureAreaController@GetStorageAreas');

        //get the structurerea by structure
        $api->get('structure-areas-structure/{id}', 'StructureAreaController@getByStructure');

        //Users


        //AreaSeeder
        $api->resource('areas', 'AreaController');
        // CONTAINER
        $api->resource('containers', 'ContainerController');
        $api->resource('container-types', 'ContainerTypeController');
        $api->resource('container-clasifications', 'ContainerClasificationController');

        // personal
        $api->get('distribution-center-personal/{id}', 'PersonController@getPersonalByDistributionCenter');
        $api->get('warehouse-personal/{id}', 'PersonController@getPersonalByWarehouse');
        $api->get('warehouse-all-personal/{id}', 'PersonController@getAllPersonalByWarehouse');


        $api->resource('distribution-centers', 'DistributionCenterController');
        $api->get('distribution-center-structure/{id}', 'DistributionCenterController@getStructure');
        $api->get('distribution-center-structure', 'DistributionCenterController@getFullStructure');

        $api->resource('zones', 'ZoneController');
        $api->get('zones-warehouse/{id}', 'ZoneController@getZonesByWarehouse');
        $api->get('zones-positions-warehouse/{warehouseId}/{zoneId}', 'ZoneController@getPositionsByWarehouse');
        $api->resource('zone-types', 'ZoneTypeController');
        $api->resource('charges', 'ChargeController');
        // $api->resource('charges-all', 'ChargeController@getAllCharges');
        $api->get('charges-all', 'ChargeController@getAllCharges');

        $api->get('personal-group/{id}', 'PersonController@getByGroup');
        $api->get('personal-zone/{id}', 'PersonController@getPersonalByZone');
        $api->get('machine-zone/{id}', 'MachineController@getMachinesByZone');
        $api->get('machine-by-type/{id}', 'MachineController@getMachinesGroupCharge');
        $api->get('machine-warehouse/{id}', 'MachineController@getMachinesByWarehouse');
        $api->get('machine-all-warehouse/{id}', 'MachineController@getAllMachinesByWarehouse');
        $api->get('machine-cedi/{id}', 'MachineController@getMachinesByDistributionCenter');
        $api->get('personal-charge/{id}', 'PersonController@getPersonalGroupCharge');
        $api->resource('zone-positions', 'ZonePositionController');
        $api->put('delete-positions/{id}', 'ZonePositionController@deleteRange');
        $api->resource('zone-concepts', 'ZoneConceptController');
        $api->put('personal-leader/{id}', 'PersonController@setLeader');
        $api->put('positions-inactivate/{id}', 'ZonePositionController@inactivateRange');
        $api->put('positions-volume/{id}', 'ZonePositionController@volumeRange');

        $api->resource('schedule-comments', 'ScheduleCommentController');
        $api->resource('schedule-orders', 'ScheduleOrderController');
        $api->post('schedule-receipts-additional', 'ScheduleController@saveReceiptAdditionalReferences');
        $api->get('schedule-receipts-additional/{id}', 'ScheduleController@getAdditionalReferencesBySchedule');
        $api->delete('schedule-receipts-additional/{id}', 'ScheduleController@deleteReceiptAdditionalReferences');
        $api->resource('orders', 'OrderController');
        $api->resource('documents', 'DocumentController');
        $api->resource('document-details', 'DocumentDetailController');
        $api->put('document-details-by-document/{id}', 'DocumentDetailController@updateAllDetailByDocument');

        $api->resource('receipt-types', 'ReceiptTypeController');


        $api->post('get-document-warehouse', 'DocumentController@getDocumentsByWarehouse');


        $api->post('update-detail-pallet', 'DocumentController@updateDetailPallet');



        $api->get('transform/{id}', 'ScheduleController@getTransformData');
        $api->put('notify-schedule/{id}', 'ScheduleController@sendMail');
        $api->put('transform-detail/{id}', 'ScheduleController@saveTransformDetail');

        $api->resource('iacodes', 'IaCodeController');

        $api->resource('products-types', 'ProductTypeController');
        $api->resource('products-sub-types', 'ProductSubTypeController');
        $api->resource('content-indicators', 'ContentIndicatorController');
        $api->resource('packing-types', 'PackingTypeController');
        $api->resource('structure-codes', 'StructureCodeController');
        $api->get('structure-codes-packing/{id}', 'StructureCodeController@getStructureByPackingId');

        // $api->resource('products', 'ProductController');
        $api->get('products-code/{code}', 'ProductController@getProcutByCode');
        $api->get('products-attributes/', 'ProductController@getProductByAttributes');
        $api->resource('features', 'FeatureController');
        $api->resource('reason-codes', 'ReasonCodeController');
        $api->get('reason-codes-all', 'ReasonCodeController@getAllReasonCodes');
        $api->get('reason-codes-picking', 'ReasonCodeController@getAllReasonCodesPicking');
        $api->resource('ean-codes14', 'EanCode14Controller');

        $api->get('ean-codes14-cancel', 'EanCode14Controller@cancelCodes');
        $api->post('ean-codes14-generate', 'EanCode14Controller@generateCode');

        $api->get('position/{code}', 'ZonePositionController@getPositionByCode');

        $api->post('reserve/{id}', 'ZonePositionController@reserveClientPositions');
        $api->resource('ean-codes128', 'EanCode128Controller');
        $api->get('products-by-type/{type}', 'ProductController@getProcutsByType');

        $api->get('stock-transition', 'StockController@getTransition');
        $api->get('stock-transition-schedule', 'StockController@getTransitionBySchedule');
        $api->get('stock-product-schedule', 'StockController@getProductByIdSchedule');
        $api->post('ean-codes14-reprint', 'EanCode14Controller@reprintcode14');
        $api->post('pallet-reprint', 'PalletController@reprintcode128');
        $api->post('position-suggest', 'ZonePositionController@suggestPositions');
        $api->get('products-by-position/{positionId}', 'ProductController@getProductsByPositionId');
        $api->post('stock-relocate', 'StockController@relocate');

        $api->post('stock-transform-request', 'StockController@transformRequest');
        $api->post('stock-transform-remove', 'StockController@transformRemove');
        $api->post('stock-transform-stored', 'StockController@transformStored');
        $api->post('stock-transform-result', 'StockController@transformResult');
        $api->post('stock-transform-result-packaging', 'StockController@transformResultPackaging');
        $api->put('stock-transform-result-packaging/{id}', 'StockController@updateTransformResultPackaging');
        $api->post('stock-transform-many-result-packaging', 'StockController@updateManyTransformResultPackaging');
        $api->post('print-code-packaging/{code}', 'StockController@printCodePackaging');
        $api->post('stock-transform-count-packaging/{id}', 'StockController@saveCountPackaging');
        $api->post('stock-transform-close-action/{id}', 'StockController@closeTransformAction');
        $api->post('stock-transform-validate-adjust', 'StockController@saveValidateAdjustSchedule');
        $api->post('stock-unjoin-close-action/{id}', 'StockController@closeUnjoinAction');
        $api->post('stock-join-reference', 'StockController@joinReferences');
        $api->post('stock-unjoin-request', 'StockController@unjoinRequest');
        $api->post('stock-unjoin-remove', 'StockController@unjoinRemove');
        $api->post('stock-unjoin-stored', 'StockController@unjoinStored');
        $api->resource('suggestions', 'SuggestionController');
        $api->get('zones-storage', 'ZoneController@getAllZonesStorage');
        $api->post('positions-code', 'ZonePositionController@getPositionsByCode');
        $api->get('pallets-code14/{id}', 'PalletController@getPallesByCode14');
        $api->get('pallets-by-position/{id}', 'PalletController@getPallesByPosition');

        $api->get('unity14-by-position-product/{id}', 'StockController@getUnity14ByPositioByProduct');

        $api->get('zones-picking', 'ZoneController@getAllZonesPicking');
        $api->get('zone_features/{id}', 'ZonePositionController@getZoneFeatures');
        $api->resource('stock-picking', 'StockPickingController');
        $api->put('update-feature', 'ZonePositionController@updateFeatures');
        $api->get('stock-configuration/{id}', 'ZonePositionController@getPickingConfig');
        $api->resource('delete-picking-config', 'ZonePositionController');
        $api->get('personal-delete', 'PersonController@getAllPersonal');
        $api->post('personal-task', 'PersonController@createSchedule');

        $api->get('schedule-position/{id}', 'ZonePositionController@getPositionByScheduleId');

        $api->get('stock-position/{id}', 'ZonePositionController@getStockByPosition');

        $api->post('ean-codes14-picking', 'EanCode14Controller@generateCode14Picking');


        $api->get('get-position-id/{id}', 'ZonePositionController@getZonePositionByZoneId');


        //task branch

        $api->resource('vinculation-types', 'VinculationTypeController');

        $api->post('stock-convert', 'StockController@convert');

        $api->get('transition-code', 'StockController@getTransitionByCode');


        $api->post('document-count-child', 'DocumentDetailCountController@storeChildCount');
        $api->post('document-count-multiple', 'DocumentDetailCountController@storeMultiple');

        $api->put('document-count-multiple-ref/{id}', 'DocumentDetailCountController@updateMultiple');
        $api->put('document-count-status/{id}', 'DocumentDetailController@updateDocCountStatus');

        $api->resource('brands', 'BrandController');

        $api->resource('schemas', 'SchemaController');

        $api->get('stock-count-adjust', 'StockCountController@getStockCounts');
        $api->put('stock-count-adjust', 'StockCountController@adjustStock');


        // Colors and Sizes
        $api->get('colors', 'ProductController@getColors');
        $api->get('sizes', 'ProductController@getSizes');

        $api->resource('schedule-images', 'ScheduleImageController');
        $api->post('position-suggestion', 'SuggestionController@suggestPosition');

        $api->resource('samples', 'SampleController');
        $api->post('samples-relocate', 'SampleController@relocate');

        $api->resource('merged-positions', 'MergedPositionController');
        $api->resource('stock-transition', 'StockTransitionController');
        $api->resource('product-combo', 'ProductComboController');


        $api->get('pallet128', 'PalletController@getPalletByCode128');

        $api->post('check-code-14', 'PalletController@checkCode14');
        $api->put('check-code-14', 'PalletController@updatePallet');
        $api->get('document-detail-adjust/{id}', 'ScheduleValidateAdjustController@getDocumentDetailByScheduleId');


        $api->resource('companies', 'CompanyController');

        $api->resource('roles', 'RoleController');
        $api->resource('presentations', 'PresentationController');
        $api->get('roles/{roleId}/{companyId}', 'RoleController@getRoleTemplate');
        $api->post('company-roles', 'RoleController@storeRoleTemplate');
        $api->get('roles-company', 'RoleController@getRolesByCompany');


        $api->resource('form-clients', 'FormClientsController');


        $api->get('{company}/cities', 'Api\LocationController@getCities');
        $api->post('{company}/cities', 'Api\LocationController@saveCity');

        $api->get('{company}/containers', 'Api\ContainerController@index');
        $api->post('{company}/containers', 'Api\ContainerController@store');

        $api->get('{company}/container_types', 'Api\ContainerController@getContainerType');
        $api->post('{company}/container_types', 'Api\ContainerController@saveContainerType');

        $api->get('{company}/countries', 'Api\LocationController@getCountries');
        $api->post('{company}/countries', 'Api\LocationController@saveCountry');

        $api->get('{company}/brands', 'Api\BrandController@index');
        $api->post('{company}/brands', 'Api\BrandController@store');


        $api->get('clients/{company}/{id}', 'Api\ClientsController@index');
        $api->post('clients/{company}', 'Api\ClientsController@store');

        $api->resource('settings', 'SettingsController');


        // DRIVER
        $api->resource('driver', 'DriverController');

        // VEHICLE
        $api->resource('vehicle', 'VehicleController');
        $api->get('productsApi/{company}/{id}', 'Api\ProductController@getProducts');
        $api->post('productsApi/{company}', 'Api\ProductController@saveProduct');



        $api->get('documents/{company}/{id}', 'Api\DocumentsController@index');
        $api->post('documents/{company}', 'Api\DocumentsController@store');
        $api->post('generateTask/{documentType}/{company}/{documentId}/{factura}', 'Api\DocumentsController@generateTask');

        $api->post('{company}/remove-document', 'Api\DocumentsController@destroy');

        $api->get('receipt-codes', 'EanCode14Controller@getCodesReceipt');

        $api->get('default-soberana', 'ReceiptTypeController@defaultClientSoberana');
        $api->post('updatebatch', 'ReceiptTypeController@updatebatch');

        $api->post('receipt-type-return', 'ReceiptTypeController@getReceiptTypeReturn');
        $api->resource('document-count', 'DocumentDetailCountController');
        $api->post('document-detail-adjust', 'ScheduleController@saveValidateAdjustSchedule');
        $api->get('departure-document', 'DocumentController@getAllDepartureDocument');
        $api->post('blockDocuments-plan-acond', 'DocumentController@blockDocuments');
        $api->post('documents-plan', 'DocumentController@getDocumentPlan');
        $api->post('save-plan-acond', 'DocumentController@save');
        $api->post('delete-plan-acond', 'DocumentController@delete');
        $api->get('get-plan-without-id/{id}', 'DocumentController@getEnlistWithoutScheduleId');
        $api->post('generateG-plan-acond', 'DocumentController@generateTaskG');
        $api->get('get-plan/{id}', 'DocumentController@getEnlistByScheduleId');
        $api->get('get-plan-schedule/{id}', 'DocumentController@getScheduleById');
        $api->post('warehouse-action', 'DocumentController@getWarehouseByScheduleId');
        $api->post('create-plan-acond', 'DocumentController@createEnlist');
        $api->get('stock-config-schedule', 'StockPickingController@getScheduleById');
        $api->get('get-product-plan/{id}', 'DocumentController@getProductByIdSchedule');
        $api->post('stock-relocate-remove', 'RelocateController@relocateRemove');
        $api->post('stock-relocate-stored', 'RelocateController@relocateStored');
        $api->post('updateEnlist-plan-acond', 'DocumentController@updateEnlist');
        $api->post('saveSchedule-plan-acond', 'DocumentController@saveSchedule');
        $api->post('dropEan128-plan-acond', 'DocumentController@dropEan128');
        $api->post('get-type-code', 'StructureCodeController@getTypeCode');
        $api->resource('pallet', 'PalletController');
        $api->resource('machines', 'MachineController');
        $api->resource('personal', 'PersonController');


        $api->post('saveServiceData-plan-acond', 'DocumentController@saveServiceData');
        $api->post('saveServiceDataPlus-plan-acond', 'DocumentController@saveServiceDataPlus');
        $api->get('document/{id}', 'DocumentDetailController@getDocumentDetailById');
        $api->post('saveServiceDataReturn-plan-acond', 'DocumentController@saveServiceDataReturn');

        $api->get('get-information-document-by-id/{id}', 'DocumentController@getInformationDocumentById');
        $api->post('update-quanty_received-pallet', 'DocumentController@updateDocumentDetailQuantyReceivedPallet');
        $api->post('get-document-warehouses', 'DocumentController@getDocumentsByWarehouses');
        $api->post('saveServiceDataBatch-plan-acond', 'DocumentController@saveServiceDataBatch');
        $api->resource('schedules', 'ScheduleController');
        $api->resource('schedule-stock', 'ScheduleStockController');


        $api->get('update-Schedule-Count/{id}', 'DocumentController@updateScheduleCount');

        $api->post('get-plan-adjust', 'DocumentController@adjust');

        $api->post('Generate-Relocate-Task', 'DocumentController@GenerateRelocateTask');
        $api->post('Generate-Aprove-Task', 'DocumentController@GenerateAproveTask');

        $api->get('get-plan-getPackingListBySchedule_id/{id}', 'DocumentController@getPackingListBySchedule_id');

        $api->post('get-plan-getAll14ByPlate', 'DocumentController@getAll14ByPlate');


        //  $api->get('save-Service-Data-Sal/{id}', 'DocumentController@saveServiceDataSal');
        $api->post('save-Service-Data-Sal', 'DocumentController@saveServiceDataSal');
        $api->post('get-plan-updateTask', 'DocumentController@updateTask');
        $api->post('saveServiceDataNew-plan-acond', 'DocumentController@saveServiceDataNew');
        $api->post('create-Stock', 'DocumentController@createStock');


        $api->resource('warehouses', 'WarehouseController');
        $api->resource('clients', 'ClientController');

        $api->get('get-vendors', 'ClientController@getVendors');
        $api->get('{company}/presentations', 'Api\PresentationController@index');
        $api->post('{company}/presentations', 'Api\PresentationController@store');
        $api->get('print-pallet', 'DocumentController@print_pallets');
        $api->get('getProductsAll', 'DocumentController@getProductsAll');
        $api->post('create-Ean14', 'DocumentController@createEan14');
        $api->post('save-requisition', 'DocumentController@saveRequisition');
        $api->get('get-requisitions', 'DocumentController@getRequisitions');

        $api->get('validate-close-task/{id}', 'DocumentController@validate_close_task');
        $api->get('stock-code', 'StockController@getStorageByCode');
        $api->get('get-countries', 'DocumentController@getCountries');
        $api->post('get-cities', 'DocumentController@getCities');
        $api->get('getWarehousesByUserId', 'WarehouseController@getWarehousesByUserId');
        $api->post('saveProducts-plan-acond', 'DocumentController@saveProducts');
        $api->post('clients-order', 'DocumentController@getClientsByOrder');
        $api->get('products-by-type/{type}', 'DocumentController@getProductsByType');
        $api->get('delete-document/{id}', 'DocumentController@deleteDocument');
        $api->resource('form-products', 'FormProductsController');

        $api->get('getZonesCategory', 'DocumentController@getZonesCategory');
        $api->get('get-products-by-search/{search}', 'ProductController@getProductsBySearch');
        $api->post('get-thirds-by-search', 'DocumentController@getThirdsBySearch');
        $api->resource('product-categories', 'ProductCategoryController');

        $api->post('task-picking-undo', 'TaskController@undoPickingTask');
        $api->post('task-picking-store', 'TaskController@storePicked');


        $api->post('picking', 'DocumentController@picking');
        $api->post('create-service', 'DocumentController@createService');
        $api->get('get-services', 'DocumentController@getServices');
        $api->get('get-service-by-id/{id}', 'DocumentController@getServiceById');
        $api->post('update-service', 'DocumentController@updateService');
        $api->post('get-services-by-search', 'DocumentController@getServicesBySearch');
        $api->get('get-states', 'DocumentController@getStates');
        $api->post('enterSerial', 'DocumentController@enterSerial');
        $api->post('saveTulas', 'DocumentController@saveTulas');
        $api->get('getDocumentsMaaji', 'DocumentController@getDocumentsMaaji');
        $api->post('updateReceiptTulas', 'DocumentController@updateReceiptTulas');
        $api->get('getpersonalMaaji', 'DocumentController@getpersonalMaaji');
        $api->post('CreateTaskTulas', 'DocumentController@CreateTaskTulas');
        $api->get('getTulasMaaji/{id}', 'DocumentController@getTulasMaaji');
        $api->get('getTulasMaajiCollect/{id}', 'DocumentController@getTulasMaajiCollect');
        $api->get('getTulasMaajiReceived/{id}', 'DocumentController@getTulasMaajiReceived');
        $api->get('getTulasMaajiFinish/{id}', 'DocumentController@getTulasMaajiFinish');
        $api->post('ActiveDocument', 'DocumentController@ActiveDocument');
        $api->post('chengeTulas', 'DocumentController@chengeTulas');

        $api->post('update14DetailQuantyReceived', 'DocumentController@update14DetailQuantyReceived');
        $api->post('CreateTaskTulasOp', 'DocumentController@CreateTaskTulasOp');
        $api->get('getDetailDocuments/{id}', 'DocumentController@getDetailDocuments');

        $api->get('getTulasById/{id}', 'DocumentController@getTulasById');
        $api->get('finishOp/{id}', 'DocumentController@finishOp');
        $api->post('createValidateTask', 'DocumentController@createValidateTask');
        $api->post('validateOp', 'DocumentController@validateOp');
        $api->post('validateOpTemporary', 'DocumentController@validateOpTemporary');
        $api->get('print_list/{id}', 'DocumentController@print_list');
        $api->get('getplatesMaaji/{id}', 'DocumentController@getplatesMaaji');
        $api->get('getdriversMaaji/{id}', 'DocumentController@getdriversMaaji');
        $api->post('saveOp', 'DocumentController@saveOp');

        $api->post('searchPrecinto', 'DocumentController@searchPrecinto');
        $api->post('updateObservation', 'DocumentController@updateObservation');
        $api->get('getTulasMaajiValidate/{id}', 'DocumentController@getTulasMaajiValidate');
        $api->post('updateFacturationNumber', 'DocumentController@updateFacturationNumber');
        $api->post('CreatePicking', 'DocumentController@CreatePicking');
    });

    $api->post('chengeTulasConsecutive', 'DocumentController@chengeTulasConsecutive');
    $api->post('saveOpPlan', 'DocumentController@saveOpPlan');
    $api->post('pick-suggestion', 'DocumentController@pickSuggestion');
    $api->get('get-suggestions/{taskId}', 'DocumentController@getSuggestions');
    $api->post('CreatePicking', 'DocumentController@CreatePicking');
    // $api->post('authorize', 'Auth\OAuthController@authorizeClient');
    $api->resource('stock', 'StockController');
    $api->get('stock_position', 'StockController@stockPosition');
    $api->get('stock-resume', 'StockController@indexResume');
    $api->post('storage-products', 'PalletController@storage');
    $api->resource('products', 'ProductController');
    $api->get('task-picking-suggestion/{taskId}/{parentId}', 'TaskController@getPickingTask');
    $api->get('task-picking-history/{scheduleId}', 'TaskController@getPickingHistory');
    $api->get('task-picking-enlist/{taskId}', 'TaskController@viewPickingTaskEnlist');
    $api->get('remService/{taskId}', 'DocumentController@remService');
    $api->get('patsService/{taskId}', 'DocumentController@patsService');
    $api->get('patsServiceByPacking/{taskId}', 'DocumentController@patsServiceByPacking');
    $api->post('get14DetailById', 'DocumentController@get14DetailById');
    $api->post('get-Document-Dispatch', 'DocumentController@getDocumentDispatch');
    $api->get('suspendDocument/{id}', 'DocumentController@suspendDocument');
    $api->get('cancelDocument/{id}', 'DocumentController@cancelDocument');
    $api->get('getMaterials', 'ProductController@getMaterials');
    $api->post('CreatePickingEspecial', 'DocumentController@CreatePickingEspecial');
    $api->get('getDocumentById/{id}', 'DocumentController@getDocumentById');
    $api->get('getDocumentId/{id}', 'DocumentController@getDocumentId');
    $api->get('confirmSuspendDocument/{id}', 'DocumentController@confirmSuspendDocument');
    $api->get('cancelSuspendDocument/{id}', 'DocumentController@cancelSuspendDocument');
    $api->get('confirmCancelDocument/{id}', 'DocumentController@confirmCancelDocument');
    $api->get('cancelCancelDocument/{id}', 'DocumentController@cancelCancelDocument');
    $api->post('orderReturn', 'DocumentController@orderReturn');
    $api->get('createPacking/{id}', 'DocumentController@createPacking');
    $api->post('savePacking', 'DocumentController@savePacking');
    $api->post('generateEan14Packing', 'DocumentController@generateEan14Packing');
    $api->post('closeEan14', 'DocumentController@closeEan14');
    $api->post('validateEanClosed', 'DocumentController@validateEanClosed');
    $api->post('createReubicarPacking', 'DocumentController@createReubicarPacking');
    $api->get('getDataTransitionByDocument/{id}', 'StockTransitionController@getDataTransitionByDocument');
    $api->post('saveReubicarPacking', 'StockTransitionController@saveReubicarPacking');
    $api->get('get-plan-getAll14ByOrder/{id}', 'DocumentController@getAll14ByOrder');
    $api->post('get-plan-loadTruck', 'DocumentController@loadTruck');
    $api->post('createTrasTask', 'DocumentController@createTrasTask');
    $api->get('ConsultTrans/{id}', 'DocumentController@ConsultTrans');
    $api->post('relocated', 'DocumentController@relocated');
    $api->post('code-position', 'ZonePositionController@generateCodePosition');
    $api->post('createStockFull', 'DocumentController@createStockFull');
    $api->post('recogerOpsTemporary', 'DocumentController@recogerOpsTemporary');
    $api->resource('stock-count', 'StockCountController');
    $api->get('inventary-stock', 'DocumentController@inventary_stock');
    $api->post('get-plan-getProductAdjust', 'DocumentController@getProductAdjust');
    $api->post('save-Service-Data-Inventary', 'DocumentController@saveServiceDataInventary');
    $api->get('consulProductByean/{id}', 'DocumentController@consulProductByean');
    $api->get('get-StockBy-Schedule-Count/{id}', 'DocumentController@getStockByScheduleCount');
    $api->get('calendar', 'ScheduleController@getCalendar');
    $api->get('getCategory/{id}', 'DocumentController@getCategory');
    $api->post('relocateMercancy', 'DocumentController@relocateMercancy');
    $api->get('getTransitionData/{id}', 'DocumentController@getTransitionData');
    $api->post('sendTaskCome', 'DocumentController@sendTaskCome');
    $api->post('sendTaskDespa', 'DocumentController@sendTaskDespa');
    $api->get('getConcept/{id}', 'DocumentController@getConcept');
    $api->get('getZones/{id}', 'DocumentController@getZones');
    $api->get('consultDocument/{id}', 'DocumentController@consultDocument');
    $api->get('getDocumentsReprint', 'DocumentController@getDocumentsReprint');
    $api->get('ean-codes14-by-document/{id}', 'EanCode14Controller@getAllCodes14ByDocumentId');
    $api->post('generateEan14PackingReprint', 'DocumentController@generateEan14PackingReprint');
    $api->get('getCode14Stored/{id}', 'DocumentController@getCode14Stored');
    $api->get('getDocumentBySearch/{id}', 'DocumentController@getDocumentBySearch');
    $api->get('getDocumentByNumber/{id}', 'DocumentController@getDocumentByNumber');
    $api->get('getAllDepartureDocumentReceipt', 'DocumentController@getAllDepartureDocumentReceipt');
    $api->post('document-departure/{company}', 'Api\DocumentsController@storeDeparture');
    $api->post('getDispatchByFilter', 'DocumentController@getDispatchByFilterByFilter');
    $api->post('getDetailDispatch', 'DocumentController@getDetailDispatch');
    $api->post('get14code', 'DocumentController@get14code');
    $api->post('updateInventoryByEan14Code', 'DocumentController@updateInventoryByEan14Code');
    $api->post('getTaskByFilter', 'DocumentController@getTaskByFilter');
    $api->get('getUsersWithTask', 'DocumentController@getUsersWithTask');
    $api->post('getPackingByFilter', 'DocumentController@getPackingByFilter');

    $api->get('getClients/{search}', 'DocumentController@getClients');
    $api->get('getClientsV', 'DocumentController@getClientsV');
    $api->post('getDocumentPlanT', 'DocumentController@getDocumentPlanT');
    $api->get('cities', 'LocationController@getCities');

    $api->get('viewPickingTaskEnlistN/{taskId}', 'TaskController@viewPickingTaskEnlistN');
    $api->post('Codigos14ByDocument', 'DocumentController@Codigos14ByDocument');
    $api->post('CreateMasterBox', 'DocumentController@CreateMasterBox');
    $api->get('getAllDepartureDocumentReceiptT', 'DocumentController@getAllDepartureDocumentReceiptT');
    $api->post('document-status/{company}', 'DocumentController@documentosEntregados');
    $api->get('getDocumentBySearchNew/{id}', 'DocumentController@getDocumentBySearchNew');
    $api->get('getAllDepartureDocumentF', 'DocumentController@getAllDepartureDocumentF');
    $api->post('CreateDocumentTask', 'DocumentController@CreateDocumentTask');

    $api->get('getDocumentByIdS/{id}', 'DocumentController@getDocumentByIdS');
    $api->post('dispatch-plan-acond', 'DocumentController@dispatch');
    $api->resource('users', 'UserController');
    $api->resource('tasks', 'TaskController');

    $api->get('searchStock/{id}', 'DocumentController@searchStock');
    $api->post('saveDocuments', 'DocumentController@saveDocuments');
    $api->get('getClientsFilter', 'ClientController@getClientsFilter');
    $api->post('getDocumentsMaajiR', 'DocumentController@getDocumentsMaajiR');
    $api->get('getConcepts', 'DocumentController@getConcepts');
    $api->get('searchStockPosition/{id}', 'DocumentController@searchStockPosition');
    $api->post('sendStockCountPosition', 'DocumentController@sendStockCountPosition');
    $api->get('searchStockPositionByStock/{id}', 'DocumentController@searchStockPositionByStock');
    $api->post('adjustPosition', 'DocumentController@adjustPosition');
    $api->post('authorize', 'AuthController@login');

    $api->post('saveFileComEx', 'DocumentController@saveFileComEx');
    $api->get('getDocumentByTask/{id}', 'DocumentController@getDocumentByTask');

    $api->post('getDocumentsEan14', 'DocumentController@getDocumentsEan14');
    $api->get('getEanDetailByDocument/{id}', 'DocumentController@getEanDetailByDocument');
    $api->post('getEanDetailInDocuments', 'DocumentController@getEanDetailInDocuments');
    $api->post('createTaskDispatch', 'DocumentController@createTaskDispatch');

    //RUTA PARA DOCUMENTOS DE panel-control
    $api->post('getDocumentsProcessMaaji', 'DocumentController@getDocumentsProcessMaaji');
    //Servicio pra generar la tarea de creacion consecutivo sizfra
    $api->post('generateTaskIngresoSizfra', 'DocumentController@generateTaskIngresoSizfra');
    // Consulta para los documentos que seran despachados y generar la cita de despacho
    $api->post('getDocumentsBySchedule', 'DocumentController@getDocumentsBySchedule');
    //Consulta de los conductores para el despacho
    $api->get('getDriverDispatch', 'DocumentController@getDriverDispatch');
    //Placas de los conductores
    $api->get('getPlateDriverDispatch', 'DocumentController@getPlateDriverDispatch');
    //Generate tarea cita despacho
    $api->post('generateTaskDispatch', 'DocumentController@generateTaskDispatch');
    //Consulta de los datos que se despacharan segun la tarea que se esta ejecutando
    $api->post('getDataDispatchTula', 'DocumentController@getDataDispatchTula');
    // Ruta para cerrar los documents desde panle-control
    $api->post('cerrarDocuments', 'DocumentController@cerrarDocuments');
    //Ruta para actualizar las tulas en estado despacho al cargar el camion.
    $api->post('updateDispatchTulas', 'DocumentController@updateDispatchTulas');
    //Ruta para consultar la data para la tareca de gestion recibo en zona franca
    $api->post('getDataManagementReceipt', 'DocumentController@getDataManagementReceipt');
    //Ruta para consultar la data para la tareca de gestion recibo en zona franca
    $api->post('generateTaskReceiptTulas', 'DocumentController@generateTaskReceiptTulas');
    //Ruta para consultar la data para la tareca de recibe.
    $api->post('getDataReceipt', 'DocumentController@getDataReceipt');
    //Ruta para guargar la placa del recibe.
    $api->post('setPlateReceipt', 'DocumentController@setPlateReceipt');
    //Cierre de tareca de recibo.
    $api->post('closeTaskReceipt', 'DocumentController@closeTaskReceipt');
    //Crear archivos desde comex
    $api->post('saveArchivoDocuments', 'DocumentController@saveArchivoDocuments');
    // Documentos
    $api->post('getDocumentsDateTulas', 'DocumentController@getDocumentsDateTulas');
    //Ruta para la consulta de los codigos ean pertenecientes a undocumento
    $api->get('getCodesEan14ByDocument/{id}', 'DocumentController@getCodesEan14ByDocument');
    //Consulta de los confeccionistas
    $api->get('getConfeccionistas', 'DocumentController@getConfeccionistas');
    //Consulta de los confeccionistas
    $api->post('getDocumentosConsult', 'DocumentController@getDocumentosConsult');
    //Consulta de los confeccionistas
    $api->get('getTulasPeso/{id}', 'DocumentController@getTulasPeso');
    //Metodo para actualizar el peso de las tulas
    $api->post('updatePesoTulas', 'DocumentController@updatePesoTulas');
    // Ruta para la eliminacion de un archivo de comex
    $api->get('deleteArchivoComex/{id}', 'DocumentController@deleteArchivoComex');
    // Ruta para la eliminacion de un archivo de comex
    $api->post('guardarArchivo', 'DocumentController@guardarArchivo');
    // Consulta documentos alocación masiva
    $api->post('getDocumentPlanMassive', 'DocumentController@getDocumentPlanMassive');
    // Crear picking masivo
    $api->post('createPickingMassive', 'DocumentController@createPickingMassive');
    // Crear packing masivo distribuyendo los usuarios por referencias
    $api->post('createPickingMassiveByReferences', 'DocumentController@createPickingMassiveByReferences');
    // Consultar sugerencia picking masivo
    $api->get('picking-massive-suggestion/{taskId}/{parentId}', 'TaskController@getPickingMassive');
    // Consultar enlist de la wave
    $api->get('task-picking-enlist-massive/{parentId}', 'TaskController@viewPickingTaskEnlistMassive');
    // Consultar enlist de la wave al detalle
    $api->get('viewPickingTaskEnlistDetail/{parentId}', 'TaskController@viewPickingTaskEnlistDetail');
    // Crear packing masivo
    $api->post('createPackingMassive', 'DocumentController@createPackingMassive');
    // Procesar unidad mercada del picking masivo
    $api->post('pick-suggestion-massive', 'DocumentController@pickSuggestionMassive');
    //Consulta de ean 14 pro codigo
    $api->post('getCode14ByCodigo', 'EanCode14Controller@getCode14ByCodigo');
    //Ruta para actualizar el peso.
    $api->post('updatePesoEan14', 'EanCode14Controller@updatePesoEan14');
    //Actualizar la trasnportadora
    $api->post('updateDriverDispatch', 'EanCode14Controller@updateDriverDispatch');
    //Consulta de zona para las ubicaciones disponibles
    $api->post('getAlmacenamientoDisponible', 'ZonePositionController@getAlmacenamientoDisponible');
    //Consulta de ubicacion by zonas
    $api->post('getPositionsByZone', 'ZonePositionController@getPositionByZoneId');
    // Crear picking masivo
    $api->post('taskPrintWaves', 'DocumentController@taskPrintWaves');
    // Crear reubicación picking masivo
    $api->get('createReubicatePickingMassive/{waveId}', 'DocumentController@createReubicatePickingMassive');
    // Consultar documentos asociados a la wave
    $api->get('getDocumentsByWave/{waveId}', 'DocumentController@getDocumentsByWave');
    // Generar eanes por olas
    $api->get('generateCodesByWave/{waveId}', 'DocumentController@generateCodesByWave');
    // Consultar documentos asociados a la wave con sus respectivos eanes 14
    $api->get('getDocumentsAndCode14ByWave/{waveId}', 'DocumentController@getDocumentsAndCode14ByWave');
    // Generar tarea de gestionar packing
    $api->get('createManagePackingMassive/{waveId}', 'DocumentController@createManagePackingMassive');
    // Consultar la información de la referencia asociada a una wave
    $api->get('getDataReferenceByWave/{waveId}', 'DocumentController@getDataReferenceByWave');
    // Reubica la referencia de la wave en una nueva posición
    $api->post('reubicatePickingMassive', 'DocumentController@reubicatePickingMassive');
    // Reubica el ean 14 de la wave en una posición del inventario
    $api->post('reubicateCode14PickingMassive', 'DocumentController@reubicateCode14PickingMassive');
    // Consulta la data necesaria para pintar la información en la tarea de packing masivo
    $api->get('getDataPackingMassive/{waveId}/{ean13}', 'DocumentController@getDataPackingMassive');
    // Guarda y procesa las unidades empacadas
    $api->post('savePackingMassive', 'DocumentController@savePackingMassive');
    // Cerrar la caja del packing masivo
    $api->post('closeEan14PackingMassive', 'DocumentController@closeEan14PackingMassive');
    // Generar rótulo adicional para la wave actual
    $api->post('generateEan14AditionalPackingMassive', 'DocumentController@generateEan14AditionalPackingMassive');
    // Generar tarea de reubicar packing masivo
    $api->get('validateCloseTaskPackingMassive/{waveId}', 'DocumentController@validateCloseTaskPackingMassive');
    // Consultar tulas.
    $api->post('getDocumentsReceiveReport', 'DocumentController@getDocumentsReceiveReport');
    //Detalle de los documentos recibidos
    $api->post('getDetailsDocumentsReceipt', 'DocumentController@getDetailsDocumentsReceipt');
    //Consulta para el informe de comex
    $api->post('getDocumentComexByFilter', 'DocumentController@getDocumentComexByFilter');
    //Consulta de los codigos amarrados a una caja master
    $api->post('getCodesByMaster', 'DocumentController@getCodesByMaster');
    //Ruta para eliminar el code 14 de una caja master
    $api->post('deleteCode14ByMaster', 'DocumentController@deleteCode14ByMaster');
    // Ruta para actualizar una caja master.
    $api->post('updateMasterBox', 'DocumentController@updateMasterBox');
    // Consulta de los detalles de la caja master
    $api->post('getMasterBoxByFilter', 'DocumentController@getMasterBoxByFilter');

    $api->post('updatePesoMasterBox', 'DocumentController@updatePesoMasterBox');
    $api->post('getDetailCode14', 'DocumentController@getDetailCode14');
    $api->post('getPesoByMaster', 'DocumentController@getPesoByMaster');

    $api->post('guardarAviewPickingTaskEnlistNrchivoPostman', 'DocumentController@guardarArchivoPostman');
    $api->post('abrirArchivo', 'DocumentController@abrirArchivo');
    $api->get('getPersonalForComex', 'DocumentController@getPersonalForComex');

    //Ruta para consultar el stock por la posición
    $api->post('buscarStock', 'DocumentController@buscarStock');

    //Ruta para eliminar el stock por la posición
    $api->post('deleteStock', 'DocumentController@deleteStock');

    // Consulta documentos alocación masiva por documentos
    $api->post('getDocumentPlanMassiveAllocation', 'DocumentController@getDocumentPlanMassiveAllocation');

    // Allocation massive
    $api->post('createPickingAllocationMassive', 'DocumentController@createPickingAllocationMassive');
    $api->post('createPickingAllocationMassiveByDocuments', 'DocumentController@createPickingAllocationMassiveByDocuments');
    $api->post('taskPrintEanAllocationMassive', 'DocumentController@taskPrintEanAllocationMassive');
    $api->get('task-picking-suggestion-allocation-massive/{taskId}/{parentId}', 'TaskController@getPickingTaskAllocationMassive');
    $api->get('task-picking-enlist-allocation-massive/{taskId}', 'TaskController@viewPickingTaskEnlistAllocationMassive');
    $api->get('viewPickingTaskEnlistAllocationMassiveN/{taskId}', 'TaskController@viewPickingTaskEnlistAllocationMassiveN');
    $api->get('createPackingAllocationMassive/{id}', 'DocumentController@createPackingAllocationMassive');
    $api->post('pick-suggestion-allocation-massive', 'DocumentController@pickSuggestionAllocationmassive');
    $api->get('createReubicatePickingAllocationMassive/{taskId}', 'DocumentController@createReubicatePickingAllocationMassive');
    $api->get('createManagePackingAllocationMassive/{waveId}', 'DocumentController@createManagePackingAllocationMassive');
    $api->post('createPackingAllocationMassive', 'DocumentController@createPackingAllocationMassive');
    $api->get('getDataReferenceByTaskPickingEnlist/{waveId}', 'DocumentController@getDataReferenceByTaskPickingEnlist');
    $api->post('reubicatePickingAllocationMassive', 'DocumentController@reubicatePickingAllocationMassive');
    $api->get('getDataPackingAllocationMassive/{waveId}/{ean13}', 'DocumentController@getDataPackingAllocationMassive');
    $api->post('savePackingAllocationMassive', 'DocumentController@savePackingAllocationMassive');
    $api->post('closeEan14PackingAllocationMassive', 'DocumentController@closeEan14PackingAllocationMassive');
    $api->post('generateEan14AditionalPackingAllocationMassive', 'DocumentController@generateEan14AditionalPackingAllocationMassive');
    $api->get('validateCloseTaskPackingAllocationMassive/{waveId}', 'DocumentController@validateCloseTaskPackingAllocationMassive');
    $api->get('getCode14ByWave/{waveId}', 'DocumentController@getCode14ByWave');
});
