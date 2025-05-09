import './bootstrap.js';
import $ from 'jquery';
window.$ = window.jQuery = $;
import 'bootstrap';
import './menu.js';

$(document).ready(function() {
    console.log("jQuery est prêt !");
});
document.addEventListener("DOMContentLoaded", function () {
    console.log("Ici ces't le log");
});

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
