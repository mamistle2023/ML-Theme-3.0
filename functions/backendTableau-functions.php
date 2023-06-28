<?php






// Tableau-Beitrag abrufen
if (have_posts()) {
    while (have_posts()) {
        the_post();

        // Layout des Tableau-Beitrags abrufen
        $tableau_layout = get_post_meta(get_the_ID(), '_tableau_layout', true);

        // Überprüfen, ob $tableau_layout ein gültiger Wert ist, bevor die foreach-Schleife verwendet wird
        if ($tableau_layout) {
            // Je nach ausgewähltem Layout unterschiedlichen Inhalt anzeigen
            switch ($tableau_layout) {
                case 'layout1':
                    // Layout 1
                    break;

                case 'layout2':
                    // Layout 2
                    break;

                case 'layout3':
                    // Layout 3
                    break;

                default:
                    // Standardlayout
                    break;
            }
        }
    }
}
?>
