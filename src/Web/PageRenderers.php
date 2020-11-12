<?php

namespace Web\PageRenderers;


function renderHomePage($requestParams) {
    // render the home page
    if (isset($_SESSION['userId'])) {
        error_log("here");
    }

    include __DIR__ . "/../../static/html/home.html";
}

function renderSignUp($requestParams) {
    include __DIR__ . "/../../static/html/registration.html";
}

function renderLogIn($requestParams) {
    include __DIR__ . "/../../static/html/signin.html";
}