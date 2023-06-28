<?php
function generate_tableau_posts() {
    $portfolio_posts = get_posts(array(
        'post_type' => 'portfolio',
        'posts_per_page' => -1,
    ));

    foreach ($portfolio_posts as $portfolio_post) {
        $portfolio_id = $portfolio_post->ID;

        // Überprüfen, ob bereits ein Tableau-Beitrag für das Portfolio existiert
        $existing_tableau = get_posts(array(
            'post_type' => 'tableau',
            'meta_key' => '_portfolio_id',
            'meta_value' => $portfolio_id,
            'posts_per_page' => 1,
        ));

        if (!empty($existing_tableau)) {
            continue; // Wenn bereits ein Tableau-Beitrag existiert, zum nächsten Portfolio-Beitrag gehen
        }

        // Tableau-Beitrag erstellen
        $tableau_post = array(
            'post_title' => 'Tableau für ' . get_the_title($portfolio_id),
            'post_type' => 'tableau',
            'post_status' => 'publish',
        );

        $tableau_post_id = wp_insert_post($tableau_post);

        if ($tableau_post_id) {
            // Tableau-Beitrag mit Portfolio-Beitrag verknüpfen
            update_post_meta($tableau_post_id, '_portfolio_id', $portfolio_id);

            // Bilder aus dem Portfolio-Beitrag in den Tableau-Beitrag kopieren
            $portfolio_images = get_post_meta($portfolio_id, '_portfolio_images', true);
            if (!empty($portfolio_images)) {
                update_post_meta($tableau_post_id, '_portfolio_images', $portfolio_images);
            }
        }
    }
}
?>