<?php

namespace Web\PageRenderers;

use Http\Request;

function renderHomePage(Request $request, $args) {
    // render the home page

    include __DIR__ . "/../../static/html/home.html";
}

function renderSignUp(Request $request, $args) {
    include __DIR__ . "/../../static/html/registration.html";
}

function renderLogIn(Request $request, $args) {
    include __DIR__ . "/../../static/html/signin.html";
}