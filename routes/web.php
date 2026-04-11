<?php
require_once "functions.php";
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => renderPage("index"));
Route::get('/login', fn() => renderPage("login"));
