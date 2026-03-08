<?php

use Illuminate\Support\Facades\Route;
use Rylxes\Gdpr\Http\Controllers\GdprDownloadController;

Route::prefix(config('gdpr.routes.prefix', 'gdpr'))
    ->middleware(config('gdpr.routes.middleware', ['web', 'signed']))
    ->group(function () {
        Route::get('/download/{token}', GdprDownloadController::class)
            ->name('gdpr.download');
    });
