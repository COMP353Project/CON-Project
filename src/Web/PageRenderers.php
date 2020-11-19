<?php

namespace Web\PageRenderers;

use Http\Request;

function renderHomePage(Request $request, $args) {
    // render the home page
    include __DIR__ . "/../../static/html/public.html";
}

function renderSignUp(Request $request, $args) {
    include __DIR__ . "/../../static/html/index.html";
}

function renderLogIn(Request $request, $args) {
    include __DIR__ . "/../../static/html/index.html";
}