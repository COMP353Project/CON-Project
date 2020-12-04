$(function () {
    'use strict';

    $('[data-toggle="offcanvas"]').on('click', function () {
        $('.offcanvas-collapse').toggleClass('open');
        console.log("OPENED");
    })
});

function getPosts() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", "/groups/add/byname");
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        createPosts();
    };
    xhttp.send(JSON.stringify(formData));
}

function createPosts() {

}