<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\ClientTransactionController;
use App\Http\Controllers\CustomBillController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FollowUpInfoController;
use App\Http\Controllers\KPILookUpController;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ToDoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPointController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('reset-password', 'resetPassword');
    Route::post('confirm-password', 'confirmPassword');
    Route::get('new-notifications', 'getNewNotifications');
});

//Route::get('increase-percentage', [ClientsController::class, 'runSeeder']);

Route::group(['middleware' => ['jwt.verify']], function () {

    Route::get('months', [MonthController::class, 'getAll']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::controller(AuthController::class)->group(function () {
        Route::get('logout', 'logout');
        Route::get('refresh', 'refresh');

        Route::get('notifications', 'getNotifications');
        Route::get('notifications/read/{id}', 'readNotification');
        Route::get('notifications/read-all', 'readNotifications');

        Route::get('profile', 'profile');
        Route::post('update-avatar', 'updateAvatar');
        Route::put('update-info', 'updateInfo');
        Route::put('update-password', 'updatePassword');
        Route::get('auth-permissions', 'getPermissions');
    });

    Route::controller(RolePermissionController::class)->group(function () {
        Route::get('roles', 'roleList')->middleware(['permission:get-role-list']);
        Route::get('roles/{id}', 'getRole')->middleware(['permission:get-role-info']);
        Route::post('roles', 'createRole')->middleware(['permission:create-role']);
        Route::put('roles/{id}', 'updateRole')->middleware(['permission:update-role']);
        Route::delete('roles/{id}', 'deleteRole')->middleware(['permission:delete-role']);
        Route::get('permissions', 'permissionList')->middleware(['permission:get-permission-list']);
        Route::post('assign-role/{user_id}', 'assignRole')->middleware('permission:assign-role-to-user');
        Route::post('users-assign-role', 'assignUsers')->middleware('permission:assign-role-to-user');
    });

    Route::controller(KPILookUpController::class)->middleware(['permission:update-kpi-lookup'])->group(function() {
        Route::get('kpi-lookups', 'index');
        Route::post('kpi-lookups', 'create');
        Route::put('kpi-lookups/{id}', 'update');
        Route::delete('kpi-lookups/{id}', 'delete');
    });

    Route::controller(UserPointController::class)->group(function () {
        Route::get('point-types', 'getList')->middleware(['permission:get-point-type-list']);
        Route::put('point-types/{id}', 'updatePoint')->middleware(['permission:update-point-type']);
        Route::get('user/points/{user_id}', 'pointData');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index')->middleware('permission:get-user-list');
        Route::get('users/{id}', 'show')->middleware('permission:get-user-info');
        Route::post('users', 'store')->middleware('permission:create-user');
        Route::post('create-employee-profile', 'createProfile')->middleware('permission:create-user-employee-profile');
        Route::post('users-update/{id}', 'update')->middleware('permission:update-user');
        Route::get('users-status/{id}', 'changeStatus')->middleware('permission:update-user');
        Route::delete('users/{id}', 'destroy')->middleware('permission:delete-user');
    });

    Route::controller(EmployeeController::class)->group(function () {
        Route::get('employees', 'getEmployeeList')->middleware('permission:employee-payable-salary-list');
        Route::post('employees/salary', 'storeSalary')->middleware('permission:store-salary');
        Route::get('employees/salary/export', 'salaryExport')->middleware('permission:employee-payable-salary-list');
    });

    Route::controller(ClientsController::class)->group(function () {
        Route::get('client-gps-data', 'clientGps')->middleware('permission:get-client-gps-data');
        Route::get('clients', 'index')->middleware('permission:get-client-list');
        Route::get('get-unpaid-clients', 'unpaidClients')->middleware('permission:get-client-list');
        Route::get('clients/{id}', 'show')->middleware('permission:get-client-info');
        Route::post('clients', 'store')->middleware('permission:create-client');
        Route::post('import/clients', 'importClients')->middleware('permission:import-client');
        Route::put('clients-info-update/{id}', 'updateInfo')->middleware('permission:update-client');
        Route::post('clients-document-update/{id}', 'updateDoc')->middleware('permission:update-client');
        Route::post('clients/delete', 'destroy')->middleware('permission:delete-client');
        Route::get('clients/export/all', 'ClientsExport')->middleware('permission:export-client-list');
    });

    Route::controller(FollowUpInfoController::class)->group(function () {
        Route::get('clients-follow-up/{client_id}', 'show')->middleware('permission:get-client-follow-up');
        Route::get('pending-follow-ups', 'getFollowUps')->middleware('permission:get-pending-client-follow-up');
        Route::get('clients-follow-up/by-user/{user_id}', 'getFollowUpsByUser')->middleware('permission:get-client-follow-up');
        Route::post('clients-follow-up-reminder', 'addReminder')->middleware('permission:create-client-follow-up-reminder');
        Route::post('follow-up', 'store')->middleware('permission:create-client-follow-up');
        Route::put('follow-up/{id}', 'update')->middleware('permission:update-client-follow-up');
        Route::delete('follow-up/{id}', 'delete')->middleware('permission:delete-client-follow-up');
    });

    Route::controller(BillController::class)->group(function () {
        Route::get('bills', 'index')->middleware('permission:get-bills');
        Route::get('bills/slip/{id}', 'billSlip')->middleware('permission:get-bills');
        Route::post('bills', 'store')->middleware('permission:create-bill');
    });

    Route::controller(CustomBillController::class)->group(function () {
        Route::get('custom-bills', 'index')->middleware('permission:get-bills');
        Route::post('custom-bills', 'create')->middleware('permission:create-bill');
        Route::get('custom-bills/{id}', 'get')->middleware('permission:get-bills');
        Route::get('custom-bills/slip/{id}', 'getPDF')->middleware('permission:get-bills');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('clients-payments', 'index')->middleware('permission:get-client-payment');
        Route::post('clients-payments', 'store')->middleware('permission:create-client-payment');
        Route::get('payment_types', 'getTypes')->middleware('permission:get-payment-type');
        Route::get('client-payment-data', 'getPayData')->middleware('permission:get-client-payment');
        Route::get('payslip/{id}', 'getPayslip')->middleware('permission:get-payslip');

        Route::get('services', 'getCategories')->middleware('permission:get-service');
        Route::post('services', 'storeCategories')->middleware('permission:create-service');
        Route::put('services/{id}', 'updateCategories')->middleware('permission:update-service');
        Route::get('services/{id}', 'statusCategories')->middleware('permission:update-service');
    });

    Route::controller(ClientTransactionController::class)->group(function () {
        Route::middleware('permission:get-client-transactions')->group(function () {
            Route::get('monthly-client-transactions', 'monthlyTransactions');
            Route::get('client-transactions', 'index');
            Route::get('client-transactions/export', 'exportData');
            Route::put('client-transactions/export/pdf', 'exportPdf');
        });

        Route::middleware('permission:create-client-transactions')->group(function () {
            Route::post('client-transactions', 'store');
            Route::post('client-transactions/import', 'importData');
        });
    });

    Route::controller(AllowanceController::class)->group(function () {
        Route::middleware('permission:get-transport-allowance')->group(function() {
            Route::get('transport-allowances/filter', 'transportAllowanceSearch');
            Route::get('transport-allowances/get/{id}', 'transportAllowance');
            Route::get('transport-allowances/current', 'currentTransportAllowance');
        });
        Route::get('transport-allowances/export/all', 'transportAllowanceExport')->middleware('permission:export-transport-allowance');
        Route::post('transport-allowance-payment-slip', 'transportAllowancePaymentSlip')->middleware('permission:transport-allowance-payment-slip');
        Route::get('allowance-invoices', 'getInvoices')->middleware('permission:transport-allowance-payment-slip');

        Route::middleware('permission:create-update-transport-allowance')->group(function() {

            Route::post('transport-allowances/start', 'start');
            Route::post('transport-allowances/end/{id}', 'end');
            Route::post('transport-allowances/update/{id}', 'update');
        });

        Route::put('transport-allowances/change-status', 'changeStatus')->middleware('permission:change-transport-allowance-status');

        Route::middleware('permission:get-food-allowance')->group(function() {

            Route::get('food-allowances/filter', 'foodAllowanceSearch');
            Route::get('food-allowances/get/{id}', 'foodAllowance');
        });

        Route::middleware('permission:create-delete-food-allowance')->group(function() {

            Route::post('food-allowances', 'foodAllowanceStore');
            Route::delete('food-allowances/delete/{id}', 'foodAllowanceDelete');
        });

        Route::get('food-allowances/export/all', 'foodAllowanceExport')->middleware('permission:export-food-allowance');
        Route::put('food-allowances/update-status', 'foodAllowanceUpdate')->middleware('permission:change-food-allowance-status');
        Route::post('food-allowance-payment-slip', 'foodAllowancePaymentSlip')->middleware('permission:food-allowance-payment-slip');
    });

    Route::controller(ToDoController::class)->group(function () {
        Route::get('todo-statuses', 'getStatuses')->middleware('permission:get-todo-statuses');
        Route::post('assign-task', 'store')->middleware('permission:assign-tasks-to-users');
        Route::get('todo-get-all', 'index')->middleware('permission:get-todo-list');
        Route::middleware('permission:update-todo-list')->group(function () {
            Route::post('assign-task/{id}', 'addUsers');
            Route::delete('task-user/{task_id}/{user_id}', 'removeUser');
            Route::put('update-todo/{id}', 'updateInfo');
        });
        Route::put('todo-change-status/{id}', 'changeStatus')->middleware('permission:update-todo-status');
        Route::delete('todo/{id}', 'deleteTodo')->middleware('permission:delete-tasks');
        Route::put('todo-change-admin-status/{id}', 'changeAdminStatus')->middleware('permission:approve-or-cancel-tasks');
        Route::middleware('permission:update-task-documents')->group(function () {
            Route::post('add-document', 'addDocuments');
            Route::delete('remove-document/{id}', 'deleteDocument');
        });

    });
});

