<?php
use Illuminate\Contracts\View\View;

function renderPage($page, $parametri = [
    'title' => 'IOT Project'
]) : View {
    return view("layouts.app", [
        "page" => $page,
        'version' => env('VERSION', '1.0.0'),
        'title' => $parametri["title"] ?? env("APP_NAME", "IOT Project"),
        'params' => $parametri
    ]);
};
