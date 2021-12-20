<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a category which
| is assigned the "api" middleware category. Enjoy building your API!
|
*/

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/forgotPassword', [App\Http\Controllers\AuthController::class, 'forgotPassword']);
Route::post('/resetPassword', [App\Http\Controllers\AuthController::class, 'resetPassword']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\UserController::class, 'ownself']);

    Route::post('/claims/count', [App\Http\Controllers\ClaimController::class, 'count']);
    Route::get('/claims/details', [App\Http\Controllers\ClaimController::class, 'claim']);
    Route::get('/claims/history/{id}', [App\Http\Controllers\ClaimController::class, 'history']);
    Route::get('/claims/{status}', [App\Http\Controllers\ClaimController::class, 'claims']);
    Route::get('/claims', [App\Http\Controllers\ClaimController::class, 'activeClaims']);
    Route::get('/claims/{form_date}/{to_date}/{status}', [App\Http\Controllers\ClaimController::class, 'durationClaims']);

    // TODO :: Maybe block off surveyor, workshop, insurer
    Route::post('/warranties/count', [App\Http\Controllers\WarrantyController::class, 'count']);
    Route::get('/warranties/details', [App\Http\Controllers\WarrantyController::class, 'warranty']);
    Route::get('/warranties/history/{id}', [App\Http\Controllers\WarrantyController::class, 'history']);
    Route::get('/warranties/{status}', [App\Http\Controllers\WarrantyController::class, 'warranties']);
    Route::get('/warranties', [App\Http\Controllers\WarrantyController::class, 'activeWarranties']);
    Route::get('/warranties/{form_date}/{to_date}/{status}', [App\Http\Controllers\WarrantyController::class, 'durationWarranties']);

    Route::post('/motors/count', [App\Http\Controllers\MotorController::class, 'count']);
    Route::get('/motors/details', [App\Http\Controllers\MotorController::class, 'motor']);
    Route::get('/motors/history/{id}', [App\Http\Controllers\MotorController::class, 'history']);
    Route::get('/motors/{status}', [App\Http\Controllers\MotorController::class, 'motors']);
    Route::get('/motors', [App\Http\Controllers\MotorController::class, 'activeMotors']);
    Route::get('/motors/{form_date}/{to_date}/{status}', [App\Http\Controllers\MotorController::class, 'durationMotors']);

    Route::post('/dashboard/servicing/count', [App\Http\Controllers\Dashboard\ServicingController::class, 'count']);
    Route::get('/dashboard/servicing/details', [App\Http\Controllers\Dashboard\ServicingController::class, 'service']);
    Route::get('/dashboard/servicing/history/{id}', [App\Http\Controllers\Dashboard\ServicingController::class, 'history']);
    Route::get('/dashboard/servicing/{status}', [App\Http\Controllers\Dashboard\ServicingController::class, 'services']);
    Route::get('/dashboard/servicing', [App\Http\Controllers\Dashboard\ServicingController::class, 'activeServices']);
    Route::get('/dashboard/servicing/{form_date}/{to_date}/{status}', [App\Http\Controllers\Dashboard\ServicingController::class, 'durationServices']);

    Route::post('/dashboard/accidents/count', [App\Http\Controllers\Dashboard\AccidentController::class, 'count']);
    Route::get('/dashboard/accidents/details', [App\Http\Controllers\Dashboard\AccidentController::class, 'accident']);
    Route::get('/dashboard/accidents/history/{id}', [App\Http\Controllers\Dashboard\AccidentController::class, 'history']);
    Route::get('/dashboard/accidents/{status}', [App\Http\Controllers\Dashboard\AccidentController::class, 'accidents']);
    Route::get('/dashboard/accidents', [App\Http\Controllers\Dashboard\AccidentController::class, 'activeAccidents']);
    Route::get('/dashboard/accidents/{form_date}/{to_date}/{status}', [App\Http\Controllers\Dashboard\AccidentController::class, 'durationAccidents']);

    Route::post('/changePassword', [App\Http\Controllers\UserController::class, 'changePassword']);
    Route::post('/changeProfile', [App\Http\Controllers\UserController::class, 'changeProfile']);

    // Insurer, AllCars, Surveyor, Workshop Admin | Insurer, AllCars Support Staff
    Route::middleware(['report'])->group(function () {
        // reports
        Route::get('/reports', [App\Http\Controllers\ClaimController::class, 'reports']);
        Route::post('/reports/export', [App\Http\Controllers\ClaimController::class, 'exportReports']);

        // reports warranty general
        Route::get('/reports-warranties', [App\Http\Controllers\WarrantyController::class, 'reports']);
        Route::post('/reports-warranties/export', [App\Http\Controllers\WarrantyController::class, 'exportReports']);

        // reports motor general
        Route::get('/reports-motors', [App\Http\Controllers\MotorController::class, 'reports']);
        Route::post('/reports-motors/export', [App\Http\Controllers\MotorController::class, 'exportReports']);
    });

    // TODO:: Admin or All Cars Staff and Workshop Admin and staff
    Route::post('/companies', [App\Http\Controllers\UserController::class, 'companies']);

    Route::middleware(['role:admin', 'category:insurer,workshop,surveyor,dealer'])->group(function () {
        Route::get('/companies/users', [App\Http\Controllers\CompanyUserController::class, 'index']);
        Route::post('/companies/users', [App\Http\Controllers\CompanyUserController::class, 'store']);
    });

    Route::middleware(['role:admin', 'category:workshop'])->group(function () {
        Route::get('/companies/slots', [App\Http\Controllers\CompanySlotController::class, 'index']);
        Route::post('/companies/slots', [App\Http\Controllers\CompanySlotController::class, 'store']);
        Route::delete('/companies/slots/{slot}', [App\Http\Controllers\CompanySlotController::class, 'destroy']);

        Route::get('/service-types', [App\Http\Controllers\ServiceTypeController::class, 'index']);
        Route::get('/service-types/{type}', [App\Http\Controllers\ServiceTypeController::class, 'detail']);
        Route::post('/service-types', [App\Http\Controllers\ServiceTypeController::class, 'store']);
        Route::put('/service-types/{type}/update', [App\Http\Controllers\ServiceTypeController::class, 'update']);
    });

    // Admin or All Cars Staff
    Route::middleware(['admin.allcars.staff'])->group(function () {
        Route::get('/users', [App\Http\Controllers\UserController::class, 'users']);
        Route::get('/users/edit', [App\Http\Controllers\UserController::class, 'user']);
        Route::get('/users/{company_id}', [App\Http\Controllers\UserController::class, 'companyUsers']);

        Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'customers']);
        Route::get('/customer-detail/{id}', [App\Http\Controllers\CustomerController::class, 'detail']);
    });

    Route::middleware(['role:admin,support_staff,salesperson', 'category:all_cars,workshop,dealer'])->group(function () {
        Route::get('/customers/index', [App\Http\Controllers\CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [App\Http\Controllers\CustomerController::class, 'show']);
        Route::get('/vehicles/customer/{customer}', [App\Http\Controllers\VehicleController::class, 'vehiclesCustomer']);

        // for page customer -> vehicles
        Route::get('/customer/vehicles', [App\Http\Controllers\VehicleController::class, 'customerVehicle']);

        // for page customer -> vehicles -> details
        Route::get('/vehicles/{vehicle}/access', [App\Http\Controllers\VehicleController::class, 'vehiclesAccess']);
        Route::get('/vehicles/{vehicle}/insurance', [App\Http\Controllers\VehicleController::class, 'vehicleInsurance']);
        Route::get('/vehicles/{vehicle}/warranty', [App\Http\Controllers\VehicleController::class, 'vehicleWarranty']);
        Route::get('/vehicles/{vehicle}/service-history', [App\Http\Controllers\VehicleController::class, 'vehicleServiceHistory']);
        Route::get('/vehicles/{vehicle}/reporting-history', [App\Http\Controllers\VehicleController::class, 'vehicleReportingHistory']);
        Route::get('/vehicles/{vehicle}/claims-history', [App\Http\Controllers\VehicleController::class, 'vehicleClaimsHistory']);
    });

    Route::middleware(['role:admin', 'category:all_cars,workshop'])->group(function () {
        Route::put('/customers/{customer}/update', [App\Http\Controllers\CustomerController::class, 'update']);
        Route::get('/customers/{customer}/vehicles', [App\Http\Controllers\CustomerController::class, 'vehicles']);

        Route::get('/vehicles', [App\Http\Controllers\VehicleController::class, 'index']);
        Route::get('/vehicles/{vehicle}', [App\Http\Controllers\VehicleController::class, 'show']);
    });

    // Servicing & Accident Reporting (for all_cars and workshops)
    Route::middleware(['role:admin,support_staff', 'category:all_cars,workshop'])->group(function () {
        // upload claims document
        Route::post('/claims/upload', [App\Http\Controllers\DocumentController::class, 'claimUpload']);

        // get list workshops
        Route::get('/workshops', [App\Http\Controllers\CompanyController::class, 'workshops']);

        Route::get('/servicing', [App\Http\Controllers\ServicingController::class, 'index']);
        Route::post('/servicing', [App\Http\Controllers\ServicingController::class, 'store']);
        Route::get('/servicing/car-models', [App\Http\Controllers\ServicingController::class, 'carModel']);
        Route::get('/servicing/car-makes', [App\Http\Controllers\ServicingController::class, 'carMake']);
        Route::get('/servicing/{service}', [App\Http\Controllers\ServicingController::class, 'show']);
        Route::put('/servicing/{service}/update', [App\Http\Controllers\ServicingController::class, 'update']);

        // Servicing Reports
        Route::get('/servicing/{service}/reports', [App\Http\Controllers\ServicingReportController::class, 'show']);
        Route::post('/servicing/{service}/reports', [App\Http\Controllers\ServicingReportController::class, 'store']);

        // Upload Servicing Reports Files
        Route::post('/servicing/{service}/reports/documents', [App\Http\Controllers\ServicingReportController::class, 'document']);
        Route::post('/servicing/{service}/reports/invoices', [App\Http\Controllers\ServicingReportController::class, 'invoice']);
        Route::delete('/servicing-reports/documents/{document}', [App\Http\Controllers\ServicingReportController::class, 'destroyDocument']);
        Route::delete('/servicing-reports/invoices/{invoice}', [App\Http\Controllers\ServicingReportController::class, 'destroyInvoice']);

        // Reporting Accident
        Route::get('/accidents', [App\Http\Controllers\AccidentController::class, 'index']);
        Route::get('/accidents/{accident}', [App\Http\Controllers\AccidentController::class, 'show']);
        Route::post('/accidents/{accident}/reports', [App\Http\Controllers\AccidentReportController::class, 'store']);
        Route::post('/accidents/{accident}/documents', [App\Http\Controllers\AccidentReportController::class, 'document']);
        Route::delete('/accident-reports/documents/{document}', [App\Http\Controllers\AccidentReportController::class, 'removeDocument']);
    });

    // Workshops Modules
    Route::get('/workshops/{company}/slots', [App\Http\Controllers\WorkshopController::class, 'slots']);
    Route::get('/workshops/{company}/times', [App\Http\Controllers\WorkshopController::class, 'times']);
    Route::get('/workshops/{company}/service-types', [App\Http\Controllers\WorkshopController::class, 'serviceTypes']);

    // Workshop and All Cars Staff
    Route::middleware(['workshop.allcars.staff'])->group(function () {
        Route::post('/claims/completed', [App\Http\Controllers\ClaimController::class, 'completed']);
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::post('/users/edit', [App\Http\Controllers\UserController::class, 'edit']);
        Route::post('/users/create', [App\Http\Controllers\UserController::class, 'create']);
        Route::post('/changeCompanyInfo', [App\Http\Controllers\CompanyController::class, 'changeCompanyInfo']);
    });

    Route::middleware(['category:workshop', 'role:support_staff,admin'])->group(function () {
        Route::post('/claims/create', [App\Http\Controllers\ClaimController::class, 'create']);
        Route::post('/claims/draft', [App\Http\Controllers\ClaimController::class, 'draft']);
        Route::post('/claims/archive', [App\Http\Controllers\ClaimController::class, 'archive']);
        Route::post('/claims/workshopApprove', [App\Http\Controllers\ClaimController::class, 'workshopApprove']);
        Route::post('/claims/workshopReject', [App\Http\Controllers\ClaimController::class, 'workshopReject']);
    });

    Route::middleware(['category:surveyor', 'role:support_staff,admin'])->group(function () {
        Route::post('/claims/surveyorApprove', [App\Http\Controllers\ClaimController::class, 'surveyorApprove']);
        Route::post('/claims/surveyorReject', [App\Http\Controllers\ClaimController::class, 'surveyorReject']);
    });

    Route::middleware(['category:insurer', 'role:support_staff,admin'])->group(function () {
        Route::post('/claims/insurerApprove', [App\Http\Controllers\ClaimController::class, 'insurerApprove']);
        Route::post('/claims/insurerReject', [App\Http\Controllers\ClaimController::class, 'insurerReject']);
        Route::post('/claims/sendSurveyor', [App\Http\Controllers\ClaimController::class, 'sendSurveyor']);
    });

    Route::middleware(['category:dealer', 'role:salesperson,admin'])->group(function () {

        Route::get('/warranty/makes', [App\Http\Controllers\WarrantyController::class, 'makes']);
        Route::post('/warranty/models', [App\Http\Controllers\WarrantyController::class, 'models']);

        Route::get('/packages', [App\Http\Controllers\PackageController::class, 'packages']);
        Route::post('/warranties/create', [App\Http\Controllers\WarrantyController::class, 'create']);
        Route::post('/warranties/draft', [App\Http\Controllers\WarrantyController::class, 'draft']);
        Route::post('/warranties/archive', [App\Http\Controllers\WarrantyController::class, 'archive']);
        Route::post('/warranties/upload', [App\Http\Controllers\DocumentController::class, 'warrantyUpload']);
        Route::post('/warranties/searchPrices', [App\Http\Controllers\WarrantyController::class, 'searchPrices']);

        Route::post('/warranties/dealerApprove', [App\Http\Controllers\WarrantyController::class, 'dealerApprove']);
        Route::post('/warranties/dealerReject', [App\Http\Controllers\WarrantyController::class, 'dealerReject']);

        Route::get('/motor/makes', [App\Http\Controllers\MotorController::class, 'makes']);
        Route::post('/motor/models', [App\Http\Controllers\MotorController::class, 'models']);

        Route::post('/motors/create', [App\Http\Controllers\MotorController::class, 'create']);
        Route::post('/motors/draft', [App\Http\Controllers\MotorController::class, 'draft']);
        Route::post('/motors/archive', [App\Http\Controllers\MotorController::class, 'archive']);
        Route::post('/motors/searchCar', [App\Http\Controllers\MotorController::class, 'searchCar']);

        Route::post('/motors/dealerApprove', [App\Http\Controllers\MotorController::class, 'dealerApprove']);
        Route::post('/motors/dealerReject', [App\Http\Controllers\MotorController::class, 'dealerReject']);
        Route::post('/motors/submitLog', [App\Http\Controllers\MotorController::class, 'submitLog']);
    });

    Route::middleware(['category:all_cars', 'role:support_staff'])->group(function () {
        Route::post('/claims/approve', [App\Http\Controllers\ClaimController::class, 'approve']);
        Route::post('/claims/reject', [App\Http\Controllers\ClaimController::class, 'reject']);
        Route::post('/claims/sendInsurer', [App\Http\Controllers\ClaimController::class, 'sendInsurer']);
        Route::post('/claims/approveDocuments', [App\Http\Controllers\ClaimController::class, 'approveDocuments']);
        Route::post('/claims/rejectDocuments', [App\Http\Controllers\ClaimController::class, 'rejectDocuments']);

        Route::post('/warranties/quote', [App\Http\Controllers\WarrantyController::class, 'quote']);
        Route::post('/warranties/approve', [App\Http\Controllers\WarrantyController::class, 'approve']);
        Route::post('/warranties/reject', [App\Http\Controllers\WarrantyController::class, 'reject']);

        Route::post('/motors/quote', [App\Http\Controllers\MotorController::class, 'quote']);
        Route::post('/motors/approve', [App\Http\Controllers\MotorController::class, 'approve']);
        Route::post('/motors/reject', [App\Http\Controllers\MotorController::class, 'reject']);
        Route::post('/motors/submitCI', [App\Http\Controllers\MotorController::class, 'submitCI']);
    });

    // Dealers and All Cars Staff
    Route::middleware(['dealer.allcars.staff'])->group(function () {
        Route::post('/motors/upload', [App\Http\Controllers\DocumentController::class, 'motorUpload']);
    });

    Route::middleware(['category:all_cars', 'role:admin,support_staff'])->group(function () {
        Route::post('/claims/upload-insurer-invoice/{id}', [App\Http\Controllers\DocumentController::class, 'uploadInsurerInvoice']);
        Route::get('/dealers', [App\Http\Controllers\CompanyController::class, 'dealers']);
        Route::get('/insurers', [App\Http\Controllers\CompanyController::class, 'insurers']);
        Route::get('/surveyors', [App\Http\Controllers\CompanyController::class, 'surveyors']);
    });

    Route::middleware(['category:all_cars', 'role:admin'])->group(function () {
        Route::get('/surveyors/options', [App\Http\Controllers\CompanyController::class, 'surveyorOptions']);
        Route::get('/surveyors/edit', [App\Http\Controllers\CompanyController::class, 'surveyor']);
        Route::post('/surveyors/edit', [App\Http\Controllers\CompanyController::class, 'editSurveyor']);
        Route::post('/surveyors/create', [App\Http\Controllers\CompanyController::class, 'createSurveyor']);

        Route::get('/insurers/edit', [App\Http\Controllers\CompanyController::class, 'insurer']);
        Route::post('/insurers/edit', [App\Http\Controllers\CompanyController::class, 'editInsurer']);
        Route::post('/insurers/create', [App\Http\Controllers\CompanyController::class, 'createInsurer']);

        Route::get('/workshops/edit', [App\Http\Controllers\CompanyController::class, 'workshop']);
        Route::post('/workshops/edit', [App\Http\Controllers\CompanyController::class, 'editWorkshop']);
        Route::post('/workshops/create', [App\Http\Controllers\CompanyController::class, 'createWorkshop']);


        Route::get('/dealers/edit', [App\Http\Controllers\CompanyController::class, 'dealer']);
        Route::post('/dealers/edit', [App\Http\Controllers\CompanyController::class, 'editDealer']);
        Route::post('/dealers/create', [App\Http\Controllers\CompanyController::class, 'createDealer']);

        Route::get('/appointments/{id}', [App\Http\Controllers\CompanyController::class, 'appointments']);

        Route::get('/warrantyPrices', [App\Http\Controllers\WarrantyController::class, 'prices']);
        Route::post('/warrantyPrices/import', [App\Http\Controllers\WarrantyController::class, 'importPrices']);
        Route::post('/warrantyPrices/export', [App\Http\Controllers\WarrantyController::class, 'exportPrices']);

        Route::post('warrantyPrices/create', [App\Http\Controllers\WarrantyController::class, 'createPrice']);
        Route::get('warrantyPrices/edit', [App\Http\Controllers\WarrantyController::class, 'price']);
        Route::post('warrantyPrices/edit', [App\Http\Controllers\WarrantyController::class, 'editPrice']);
        Route::post('warrantyPrices/delete', [App\Http\Controllers\WarrantyController::class, 'deletePrices']);
    });
});

Route::post('/stripe/callback', [App\Http\Controllers\Stripe\StripeCallbackController::class, 'store']);

/**
 * For Mobile App,
 * TODO: Middleware for authentication
 */
Route::prefix('mobile')->group(function () {
    Route::post('/login', [App\Http\Controllers\Mobile\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\Mobile\AuthController::class, 'register']);
    Route::post('/check', [App\Http\Controllers\Mobile\AuthController::class, 'check']);
    Route::middleware(['auth:mobile'])->group(function () {
        // Vehicles
        Route::get('/vehicles/summary', [App\Http\Controllers\Mobile\Vehicle\VehicleController::class, 'summary']);
        Route::get('/vehicles', [App\Http\Controllers\Mobile\Vehicle\VehicleController::class, 'index']);
        Route::get('/vehicles/{vehicle}', [App\Http\Controllers\Mobile\Vehicle\VehicleController::class, 'show']);
        Route::get('/vehicles/{vehicle}/motors', [App\Http\Controllers\Mobile\Vehicle\VehicleController::class, 'motors']);
        Route::get('/vehicles/{vehicle}/warranties', [App\Http\Controllers\Mobile\Vehicle\VehicleController::class, 'warranties']);

        // Customer Devices
        Route::post('/devices', [App\Http\Controllers\Mobile\Device\DeviceController::class, 'store']);
        Route::delete('/devices', [App\Http\Controllers\Mobile\Device\DeviceController::class, 'destroy']);

        // Vehicle Access Control
        Route::get('/vehicles/{vehicle}/access', [App\Http\Controllers\Mobile\Vehicle\VehicleAccessController::class, 'index']);
        Route::post('/vehicles/{vehicle}/access', [App\Http\Controllers\Mobile\Vehicle\VehicleAccessController::class, 'store']);
        Route::put('/vehicles/{vehicle}/access', [App\Http\Controllers\Mobile\Vehicle\VehicleAccessController::class, 'update']);
        Route::delete('/vehicles/{vehicle}/access', [App\Http\Controllers\Mobile\Vehicle\VehicleAccessController::class, 'destroy']);

        // Profiles
        Route::get('/profiles', [App\Http\Controllers\Mobile\Profile\ProfileController::class, 'show']);
        Route::post('/profiles/update', [App\Http\Controllers\Mobile\Profile\ProfileController::class, 'update']);

        // Settings
        Route::get('/settings', [App\Http\Controllers\Mobile\Setting\SettingController::class, 'index']);
        Route::put('/settings', [App\Http\Controllers\Mobile\Setting\SettingController::class, 'update']);

        // Services (Book Servicing)
        Route::get('/services', [App\Http\Controllers\Mobile\Service\ServiceController::class, 'index']);
        Route::post('/services', [App\Http\Controllers\Mobile\Service\ServiceController::class, 'store']);
        Route::post('/services/{service}/update', [App\Http\Controllers\Mobile\Service\ServiceController::class, 'update']);
        Route::get('/services/{service}', [App\Http\Controllers\Mobile\Service\ServiceController::class, 'show']);
        Route::delete('/services/{service}', [App\Http\Controllers\Mobile\Service\ServiceController::class, 'destroy']);
        Route::get('/services/{service}/cancel', [App\Http\Controllers\Mobile\Service\ServiceController::class, 'cancel']);

        // Workshops
        Route::get('/workshops', [App\Http\Controllers\Mobile\Workshop\WorkshopController::class, 'index']);

        // Servicing Types
        Route::get('/companies/{company}/service-types', [App\Http\Controllers\Mobile\Company\CompanyServiceTypeController::class, 'index']);

        // Servicing Slots
        Route::get('/companies/{company}/{date}/service-slots', [App\Http\Controllers\Mobile\Company\CompanyServiceSlotController::class, 'index']);

        // Warranty Paynow
        Route::post('/warranties/paynow-ref', [App\Http\Controllers\Mobile\Warranty\WarrantyPaynowController::class, 'reference']);
        Route::post('/warranties/paynow', [App\Http\Controllers\Mobile\Warranty\WarrantyPaynowController::class, 'store']);

        // Warranty Stripe
        Route::post('/warranties/stripe', [App\Http\Controllers\Mobile\Warranty\WarrantyStripeController::class, 'store']);
        Route::post('/warranties/stripe-intent', [App\Http\Controllers\Mobile\Warranty\WarrantyStripeController::class, 'intent']);

        // Warranties
        Route::get('/warranties', [App\Http\Controllers\Mobile\Warranty\WarrantyController::class, 'index']);
        Route::get('/warranties/{warranty}', [App\Http\Controllers\Mobile\Warranty\WarrantyController::class, 'show']);
        Route::post('/warranties/buy', [App\Http\Controllers\Mobile\Warranty\WarrantyController::class, 'buy']);
        Route::post('/warranties/enquiry', [App\Http\Controllers\Mobile\Warranty\WarrantyController::class, 'enquiry']);
        Route::get('/packages', [App\Http\Controllers\Mobile\Warranty\WarrantyController::class, 'packages']);

        // Prices
        Route::get('/prices/makes', [App\Http\Controllers\Mobile\Warranty\WarrantyPriceController::class, 'makes']);
        Route::post('/prices/models', [App\Http\Controllers\Mobile\Warranty\WarrantyPriceController::class, 'models']);
        Route::post('/prices', [App\Http\Controllers\Mobile\Warranty\WarrantyPriceController::class, 'index']);
        Route::get('/prices/{price}', [App\Http\Controllers\Mobile\Warranty\WarrantyPriceController::class, 'show']);

        // Motors
        Route::get('/motors', [App\Http\Controllers\Mobile\Motor\MotorController::class, 'index']);
        Route::get('/motors/makes', [App\Http\Controllers\Mobile\Motor\MotorController::class, 'makes']);
        Route::post('/motors/models', [App\Http\Controllers\Mobile\Motor\MotorController::class, 'models']);
        Route::post('/motors/enquiry', [App\Http\Controllers\Mobile\Motor\MotorController::class, 'enquiry']);
        Route::post('/motors/searchCar', [App\Http\Controllers\Mobile\Motor\MotorController::class, 'searchCar']);
        Route::get('/motors/{motor}', [App\Http\Controllers\Mobile\Motor\MotorController::class, 'show']);
        Route::post('/motors/stripe', [App\Http\Controllers\Mobile\Motor\MotorStripeController::class, 'store']);
        Route::post('/motors/paynow-ref', [App\Http\Controllers\Mobile\Motor\MotorPaynowController::class, 'reference']);
        Route::post('/motors/paynow', [App\Http\Controllers\Mobile\Motor\MotorPaynowController::class, 'store']);

        // Accidents / Report
        Route::get('/reports/vehicleInfo', [App\Http\Controllers\Mobile\Report\ReportController::class, 'vehicleInfo']);
        Route::get('/reports', [App\Http\Controllers\Mobile\Report\ReportController::class, 'index']);
        Route::post('/reports', [App\Http\Controllers\Mobile\Report\ReportController::class, 'store']);
        Route::put('/reports/{report}/update', [App\Http\Controllers\Mobile\Report\ReportController::class, 'update']);
        Route::get('/reports/{report}', [App\Http\Controllers\Mobile\Report\ReportController::class, 'show']);

        // Accidents / Report Documents
        Route::post('/reports/{report}/documents', [App\Http\Controllers\Mobile\Report\ReportDocumentController::class, 'store']);
        Route::get('/reports/{report}/documents', [App\Http\Controllers\Mobile\Report\ReportDocumentController::class, 'index']);

        // Accidents / Report Driver
        Route::post('/reports/{report}/drivers', [App\Http\Controllers\Mobile\Report\ReportDriverController::class, 'store']);

        // Accidents / Report Involvement
        Route::post('/reports/{report}/involves', [App\Http\Controllers\Mobile\Report\ReportInvolveController::class, 'store']);

        // Notifications and Activities
        Route::get('/notifications', [App\Http\Controllers\Mobile\Notification\NotificationController::class, 'notification']);
        Route::post('/notifications/read', [App\Http\Controllers\Mobile\Notification\NotificationController::class, 'notificationReadAll']);
        Route::post('/notifications/{id}/read', [App\Http\Controllers\Mobile\Notification\NotificationController::class, 'read']);
        Route::get('/activities', [App\Http\Controllers\Mobile\Notification\NotificationController::class, 'activity']);
        Route::post('/activities/read', [App\Http\Controllers\Mobile\Notification\NotificationController::class, 'activityReadAll']);

        // Constants
        Route::get('/const/{class}', [App\Http\Controllers\Mobile\ConstantController::class, 'index']);
    });
});
