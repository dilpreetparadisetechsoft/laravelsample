<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'companyToken' => \App\Http\Middleware\Api\CompanyToken::class,
        'customerToken' => \App\Http\Middleware\Api\CustomerToken::class,
        'departmentToken' => \App\Http\Middleware\Api\DepartmentToken::class,
        'emailTemplateToken' => \App\Http\Middleware\Api\EmailTemplateToken::class,
        'documentTypeToken' => \App\Http\Middleware\Api\DocumentTypeToken::class,
        'emailAttachmentToken' => \App\Http\Middleware\Api\EmailAttachmentToken::class,
        'templateToken' => \App\Http\Middleware\Api\TemplateToken::class,
        'moduleToken' => \App\Http\Middleware\Api\ModuleToken::class,
		'servicesToken' => \App\Http\Middleware\Api\ServicesToken::class,
        'jobStatusToken' => \App\Http\Middleware\Api\JobStatusToken::class,
        'leadSourceToken' => \App\Http\Middleware\Api\LeadSourceToken::class,
        'privilageToken' => \App\Http\Middleware\Api\PrivilageToken::class,
        'taskTypeToken' => \App\Http\Middleware\Api\TaskTypeToken::class,
        'taxToken' => \App\Http\Middleware\Api\TaxToken::class,
        'locationToken' => \App\Http\Middleware\Api\LocationToken::class,
        'userToken' => \App\Http\Middleware\Api\UserToken::class,
        'representativeToken' => \App\Http\Middleware\Api\RepresentativeToken::class,
        'roleToken' => \App\Http\Middleware\Api\RoleToken::class,
        'branchToken' => \App\Http\Middleware\Api\BranchToken::class,
        'authToken' => \App\Http\Middleware\Api\AuthToken::class,
        'chargeCodeToken' => \App\Http\Middleware\Api\ChargeCodeToken::class,
        'kitToken' => \App\Http\Middleware\Api\KitToken::class,
        'equipmentToken' => \App\Http\Middleware\Api\EquipmentToken::class,
        'buildingToken' => \App\Http\Middleware\Api\BuildingToken::class,
        'insuranceCompanyToken' => \App\Http\Middleware\Api\InsuranceCompanyToken::class,
        'indicatorToken' => \App\Http\Middleware\Api\IndicatorToken::class,
        'groupToken' => \App\Http\Middleware\Api\GroupToken::class,
        'interestToken' => \App\Http\Middleware\Api\InterestToken::class,
        'occupationToken' => \App\Http\Middleware\Api\OccupationToken::class,
        'pipeLineStageToken' => \App\Http\Middleware\Api\PipeLineStageToken::class,
        'workScopeToken' => \App\Http\Middleware\Api\WorkScopeToken::class,
        'workOrderToken' => \App\Http\Middleware\Api\WorkOrderToken::class,        
        'estimateToken' => \App\Http\Middleware\Api\EstimateToken::class,        
        'invoiceToken' => \App\Http\Middleware\Api\InvoiceToken::class,          
        'networkToken' => \App\Http\Middleware\Api\NetworkToken::class,        
        'purchaseOrderToken' => \App\Http\Middleware\Api\PurchaseOrderToken::class,    
        'excelUploadToken' => \App\Http\Middleware\Api\ExcelUploadToken::class,      
        'assignTaskToken' => \App\Http\Middleware\Api\AssignTaskToken::class,      
        'schedulerToken' => \App\Http\Middleware\Api\SchedulerToken::class,  
        'reportToken' => \App\Http\Middleware\Api\ReportToken::class,   
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
