<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => new JsonResponse(['now' => (new DateTime)->format(DateTime::ATOM)]));
