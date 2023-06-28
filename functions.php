<?php
// Definition der Konstante "BACKEND_TABLEAU"

// Einbinden der portfolio-functions.php
// Einbinden der portfolio-functions.php
function enqueue_bootstrap() {
    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' );
    wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_bootstrap' );


define('BACKEND_TABLEAU', 'TABLEAU_LAYOUT_SETTINGS');
require_once get_template_directory() . '/functions/backendTableau-functions.php';
require_once get_template_directory() . '/functions/portfolio-functions.php';

require_once get_template_directory() . '/functions/tableau-functions.php';
//require_once get_template_directory() . '/functions/helpers.php';

function enqueue_theme_styles() {
    wp_enqueue_style('theme-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'enqueue_theme_styles');

//Enqueue backendTableau.js script 
function enqueue_backendTableau_js() {
    wp_enqueue_script( 'backendTableau', get_template_directory_uri() . '/js/backendTableau.js', array('jquery'), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_backendTableau_js' );

?>
