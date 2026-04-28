<?php
use Illuminate\Contracts\View\View;

function renderPage($page, $parametri = [
    'title' => 'IOT Project'
]) : View {
    return view("layouts.app", [
        "page" => $page,
        'title' => $parametri["title"],
        'params' => $parametri
    ]);
};
