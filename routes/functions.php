<?php
use Illuminate\Contracts\View\View;

function renderPage($page, $parametri = [
    'title' => 'Caravel'
]) : View {
    return view("header", [
        "page" => $page,
        'title' => $parametri["title"],
        'params' => $parametri
    ]);
};
