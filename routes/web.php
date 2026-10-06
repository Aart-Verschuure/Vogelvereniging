<?php

use App\Http\Controllers\Admin\MembershipRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BreedingNumberController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\MembershipCancellationController;
use App\Http\Controllers\MemberTypeController;
use App\Http\Controllers\ProfileController;
use App\Models\MemberType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'memberTypes' => MemberType::with('prices')->orderBy('id')->get(),
    ]);
});

// Openbare formulieren voor aan- en afmelden als lid
Route::get('/lid-worden', [MembershipApplicationController::class, 'create'])->name('membership.apply');
Route::post('/lid-worden', [MembershipApplicationController::class, 'store'])->middleware('throttle:5,1')->name('membership.apply.store');
Route::get('/afmelden', [MembershipCancellationController::class, 'create'])->name('membership.cancel');
Route::post('/afmelden', [MembershipCancellationController::class, 'store'])->middleware('throttle:5,1')->name('membership.cancel.store');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Ledenbeheer
    Route::resource('members', MemberController::class);
    Route::post('/members/{member}/kweeknummers', [BreedingNumberController::class, 'store'])->name('members.breeding-numbers.store');
    Route::delete('/kweeknummers/{breedingNumber}', [BreedingNumberController::class, 'destroy'])->name('breeding-numbers.destroy');

    // Lidsoorten en prijzen
    Route::resource('member-types', MemberTypeController::class)->except('show');
    Route::post('/member-types/{member_type}/prijzen', [MemberTypeController::class, 'storePrice'])->name('member-types.prices.store');

    // Facturen
    Route::get('/facturen', [ContributionController::class, 'index'])->name('invoices.index');
    Route::post('/facturen', [ContributionController::class, 'storeForYear'])->name('invoices.store-for-year');
    Route::post('/members/{member}/facturen', [ContributionController::class, 'store'])->name('members.invoices.store');
    Route::get('/facturen/{contribution}', [ContributionController::class, 'show'])->name('invoices.show');
    Route::post('/facturen/{contribution}/betaald', [ContributionController::class, 'markPaid'])->name('invoices.paid');

    // De aan- en afmeldingen staan op het dashboard; deze oude link (uit eerder verstuurde mails) stuurt daarheen door
    Route::redirect('/admin/aanmeldingen', '/dashboard');
    Route::post('/admin/aanmeldingen/{member}/goedkeuren', [MembershipRequestController::class, 'approve'])->name('admin.membership-requests.approve');
    Route::post('/admin/aanmeldingen/{member}/afwijzen', [MembershipRequestController::class, 'reject'])->name('admin.membership-requests.reject');
    Route::post('/admin/terugbetalingen/{contribution}/uitbetaald', [MembershipRequestController::class, 'markRefundPaid'])->name('admin.refunds.paid');

    Route::get('/admin/beheerders', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/beheerders', [UserController::class, 'store'])->name('admin.users.store');
    Route::delete('/admin/beheerders/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

require __DIR__.'/auth.php';
