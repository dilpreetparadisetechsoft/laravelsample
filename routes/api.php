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

Route::post('auth/login', 'Api\Auth\LoginController@accessLogin');
Route::post('auth/logout', 'Api\Auth\LoginController@logout');
Route::group(['middleware' => ['roleToken']], function(){
	Route::resource('role', 'Api\Roles\RolesController');
});

Route::group(['middleware' => ['emailTemplateToken']], function(){
	Route::resource('emailTemplate', 'Api\EmailTemplate\EmailTemplateController');
});
Route::group(['middleware' => ['templateToken']], function(){
	Route::resource('template', 'Api\Template\TemplateController');
});
Route::group(['middleware' => ['emailAttachmentToken']], function(){
	Route::resource('emailAttachment', 'Api\EmailAttachment\EmailAttachmentController');
	Route::post('update/emailAttachment/{id?}', 'Api\EmailAttachment\EmailAttachmentController@updateAttachment');
	
});
Route::group(['middleware' => ['documentTypeToken']], function(){
	Route::resource('documentType', 'Api\DocumentType\DcoumentTypeController');
});

Route::group(['middleware' => ['taxToken']], function(){
	Route::resource('tax', 'Api\Tax\Taxcontroller');
});
Route::group(['middleware' => ['taskTypeToken']], function(){
	Route::resource('tasktype', 'Api\TaskType\TasktypeController');
});
Route::group(['middleware' => ['assignTaskToken']], function(){
	Route::get('get/assignTaskByMe', 'Api\Customer\AssignTaskController@assignedByMe');
	Route::get('get/assignTaskToMe', 'Api\Customer\AssignTaskController@assignedMe');
	Route::get('assignTaskGetTags', 'Api\Customer\AssignTaskController@assignTaskGetTags');
	
	Route::post('update/status/assignTask', 'Api\Customer\AssignTaskController@updateStatusAssignTask');
	Route::post('save/task/comment', 'Api\Customer\AssignTaskController@saveTaskComment');
});
Route::group(['middleware' => ['schedulerToken']], function(){
	Route::resource('scheduler', 'Api\Scheduler\SchedulerController');
	Route::get('estimate/scheduler/{type?}', 'Api\Scheduler\SchedulerController@estimatesScheduler');
	Route::get('get/single/estimate/scheduler/{estimate_id?}', 'Api\Scheduler\SchedulerController@singleEstimateScheduler');
	Route::resource('assignJob', 'Api\Scheduler\AssignJobController');
});
Route::group(['middleware' => ['customerToken']], function(){
	Route::resource('customer', 'Api\Customer\CustomerController');
	Route::get('get/customer/data', 'Api\Customer\CustomerController@getCustomerData');

	Route::get('get/customerDocs/{customer_id?}', 'Api\Customer\CustomerDocsController@index');
	Route::resource('customerDocs', 'Api\Customer\CustomerDocsController');
	
	Route::get('get/customerContactHistory/{customer_id?}', 'Api\Customer\CustomerContactHistoryController@index');
	Route::resource('customerContactHistory', 'Api\Customer\CustomerContactHistoryController');

	Route::get('get/outSource/{customer_id?}', 'Api\Customer\CustomerOutSourceController@index');
	Route::resource('outSource', 'Api\Customer\CustomerOutSourceController');

	Route::get('get/assignTask/{customer_id?}', 'Api\Customer\AssignTaskController@index');
	Route::resource('assignTask', 'Api\Customer\AssignTaskController');

	Route::get('get/invoices/{customer_id?}', 'Api\Customer\FinanceController@indexInvoices');
	Route::resource('invoices', 'Api\Customer\FinanceController');
	Route::get('get/estimate/invoice/details/{estimate_id?}', 'Api\Customer\FinanceController@getEstimateInvoiceDetails');
	
	Route::get('estimate/invoice/create/email/{estimate_id?}', 'Api\Customer\FinanceController@estimateInvoiceCreatEmail');
	Route::post('estimate/invoice/send/email', 'Api\Customer\FinanceController@estimateInvoiceSendEmail');	
		
});
Route::group(['middleware' => ['departmentToken']], function(){
	Route::resource('department', 'Api\Department\Departmentcontroller');
});
Route::group(['middleware' => ['buildingToken']], function(){
	Route::resource('building', 'Api\Building\BuildingController');
});
Route::group(['middleware' => ['servicesToken']], function(){
	Route::resource('services', 'Api\Services\ServicesController');
});
Route::group(['middleware' => ['jobStatusToken']], function(){
	Route::resource('jobStatus', 'Api\JobStatus\JobStatusController');
	Route::get('get/all/jobStatus', 'Api\JobStatus\JobStatusController@getAllJobStatus');
});
Route::group(['middleware' => ['leadSourceToken']], function(){
	Route::resource('leadSource', 'Api\LeadSource\LeadSourceController');
});

Route::group(['middleware' => ['companyToken']], function(){
	Route::resource('company', 'Api\Company\CompanyController');
	Route::post('edit/company/{comp_id?}', 'Api\Company\CompanyController@update')->name('company.update');
});
Route::group(['middleware' => ['moduleToken']], function(){
	Route::resource('module', 'Api\Module\ModuleController');
});
Route::group(['middleware' => ['privilageToken']], function(){
	Route::resource('privilage', 'Api\Privilage\PrivilageController');
});
Route::group(['middleware' => ['userToken']], function(){
	Route::resource('user', 'Api\User\UserController');
});
Route::group(['middleware' => ['representativeToken']], function(){
	Route::resource('representative', 'Api\User\RepresentativeController');
});


Route::group(['middleware' => ['branchToken']], function(){
	Route::resource('branch', 'Api\Branch\BranchController');
	Route::get('all/location', 'Api\Branch\BranchController@getAllLocation');
});

Route::group(['middleware' => ['locationToken']], function (){
	Route::resource('location', 'Api\Location\LocationController');
	Route::get('get/location/helper/data', 'Api\Location\LocationController@getHelperData');
});

Route::group(['middleware' => ['chargeCodeToken']], function (){
	Route::resource('chargecode', 'Api\ChargeCode\ChargeCodeController');
});
Route::group(['middleware' => ['kitToken']], function (){
	Route::resource('kit', 'Api\Kit\KitController');
	Route::get('get/kit/helper/data', 'Api\Kit\KitController@getHelperData')->name('kit.helper.data');
});
Route::group(['middleware' => ['equipmentToken']], function (){
	Route::resource('equipment', 'Api\Equipment\EquipmentController');
});
Route::group(['middleware' => ['insuranceCompanyToken']], function (){
	Route::resource('insurancecompany', 'Api\InsuranceCompany\InsuranceCompanyController');
});
Route::group(['middleware' => ['indicatorToken']], function (){
	Route::resource('indicator', 'Api\Indicator\IndicatorController');
});

Route::group(['middleware' => ['workScopeToken']], function (){
	Route::resource('workScope', 'Api\WorkScope\WorkScopeTitlesController');
	Route::resource('workScopeRecommendation', 'Api\WorkScope\WorkScopeRecommendationsController');
});
Route::group(['middleware' => ['workOrderToken']], function (){
	Route::get('get/workOrder/{estimate_id?}', 'Api\WorkOrder\WorkOrderController@index');
	Route::get('workOrder/create/{estimate_id?}', 'Api\WorkOrder\WorkOrderController@create');
	Route::resource('workOrder', 'Api\WorkOrder\WorkOrderController');
	Route::resource('workOrderEquipment', 'Api\WorkOrder\WorkOrderEquipmentController');
});

Route::group(['middleware' => ['purchaseOrderToken']], function (){
	Route::get('get/purchaseOrder/{estimate_id}', 'Api\PurchaseOrder\PurchaseOrderController@index');
	Route::get('create/purchaseOrder/{estimate_id}', 'Api\PurchaseOrder\PurchaseOrderController@create');
	Route::resource('purchaseOrder', 'Api\PurchaseOrder\PurchaseOrderController');
});
Route::group(['middleware' => ['networkToken']], function (){
	Route::resource('network', 'Api\Networks\NetworksController');
});

Route::group(['middleware' => ['groupToken']], function (){
	Route::resource('group', 'Api\Group\GroupController');
});
Route::group(['middleware' => ['interestToken']], function (){
	Route::resource('interest', 'Api\Interest\InterestController');
});
Route::group(['middleware' => ['occupationToken']], function (){
	Route::resource('occupation', 'Api\Occupation\OccupationController');
});
Route::group(['middleware' => ['pipeLineStageToken']], function (){
	Route::resource('pipeLineStage', 'Api\PipeLineStage\PipeLineStageController');
});

Route::group(['middleware' => ['excelUploadToken']], function (){
	Route::resource('excelUpload', 'Api\ExcelUpload\EstimateExcelController');
});

Route::group(['middleware' => ['estimateToken']], function (){
	Route::resource('estimate', 'Api\Estimate\EstimateController');
	Route::get('estimate/history/previews/{estimate_id?}', 'Api\Estimate\EstimateController@estimateHistoryPreviews');
	Route::get('estimate/history/details/{estimate_log_id?}', 'Api\Estimate\EstimateController@estimateHistoryDetails');
	Route::post('estimateUpdateStatus', 'Api\Estimate\EstimateController@estimateUpdateStatus');
	Route::get('get/estimate/wip', 'Api\Estimate\EstimateController@wip');
	Route::get('get/estimate/helper/data', 'Api\Estimate\EstimateController@getEstimateHelperData');
	Route::post('get/charge/codes', 'Api\Estimate\EstimateController@getChargeCodes');
	Route::get('customerEstimate/{customer_id?}', 'Api\Estimate\EstimateController@customerEstimate');
	Route::post('send/email/estimate', 'Api\Estimate\EstimateController@estimateSendEmail');
});
Route::group(['middleware' => ['reportToken']], function (){
	Route::get('getReportFilter', 'Api\Report\ReportController@index');
	Route::post('getReport', 'Api\Report\ReportController@getReport');
});
Route::group(['middleware' => ['authToken']], function(){
	Route::get('get/user/data', 'Api\User\UserController@getUserHelperData');
	Route::get('get/privilage/helper/data/', 'Api\Privilage\PrivilageController@getHelperData');
	Route::get('get/user/detail/{uuid?}', 'Api\User\UserController@getUserDetail')->name('user.details');
	Route::post('update/user/detail/{uuid?}', 'Api\User\UserController@updateUserDetail')->name('update.user.details');
	Route::resource('settings','Api\Settings\SettingsController');
	Route::resource('notifications', 'Api\Notification\NotificationsController');
	
});

Route::post('/getstates', function(Illuminate\Http\Request $request){
	return getState($request->input('country_name'));
})->name('home.getstates');
Route::post('/getcitis', function(Illuminate\Http\Request $request){
	return getStateCity($request->input('state_name'));
})->name('home.getciti');