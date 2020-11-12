<?php

namespace Web\PageRenderers;


function renderHomePage($requestParams) {
    // render the home page
    include __DIR__ . "/../../static/html/home.html";
}

function renderSignUp($requestParams) {
    include __DIR__ . "/../../static/html/registration.html";
}