<?php
    

// Register Tableau post type
function custom_tableau_post_type() {
    $labels = array(
        'name'               => 'Tableau',
        'singular_name'      => 'Tableau',
        'menu_name'          => 'Tableau',
        'name_admin_bar'     => 'Tableau',
        'add_new'            => 'Neues Tableau hinzufügen',
        'add_new_item'       => 'Neues Tableau hinzufügen',
        'edit_item'          => 'Tableau bearbeiten',
        'new_item'           => 'Neues Tableau',
        'view_item'          => 'Tableau anzeigen',
        'all_items'          => 'Alle Tableaus',
        'search_items'       => 'Tableaus durchsuchen',
        'parent_item_colon'  => 'Übergeordnetes Tableau:',
        'not_found'          => 'Keine Tableaus gefunden.',
        'not_found_in_trash' => 'Keine Tableaus im Papierkorb gefunden.'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'tableau' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array( 'title', 'thumbnail' ),
        'menu_icon'           => 'dashicons-images-alt' // Optional: Icon für das Menü
    );

    register_post_type( 'tableau', $args );
}
add_action( 'init', 'custom_tableau_post_type' );


if ( ! function_exists( 'render_portfolio_images_meta_box' ) ) {
    function render_portfolio_images_meta_box( $post ) {
        $imageIds = get_post_meta( $post->ID, '_portfolio_images', true );
        $images_check = get_post_meta( $post->ID, '_portfolio_image_checkbox', true ) ?: [];
        $title = get_post_meta( $post->ID, '_portfolio_title', true );
        $description = get_post_meta( $post->ID, '_portfolio_description', true );

        wp_nonce_field( 'portfolio_images_nonce', 'portfolio_images_nonce' );
        ?>

        <label for="portfolio_title">Titel:</label>
        <input type="text" name="portfolio_title" id="portfolio_title" value="<?php echo esc_attr( $title ); ?>" />
        <br/>
        <label for="portfolio_description">Beschreibung:</label>
        <textarea name="portfolio_description" id="portfolio_description" rows="5" style="width: 100%;"><?php echo esc_textarea( $description ); ?></textarea>
        <br/>
        <label for="portfolio_images">Bilder hinzufügen:</label>
        <input type="button" class="button button-secondary" value="Bilder auswählen" onclick="addPortfolioImages();" />
        <ul id="portfolio-images-list">
            <?php if ( is_array($imageIds) && !empty($imageIds) ) : ?>
                <?php foreach ( $imageIds as $index => $imageId ) : ?>
                    


                    <?php $image = wp_get_attachment_image_src( $imageId, 'full' )[0]; ?> <!-- URL des Bildes holen -->

                        



                    <li>
                        <input type="hidden" name="portfolio_images[]" value="<?php echo esc_attr( $imageId ); ?>" />
                        <img src="<?php echo esc_attr( $image ); ?>" width="100" height="auto" />
                        
                        <input type="checkbox" name="portfolio_image_checkbox[]" value="<?php echo esc_attr( $imageId ); ?>" <?php echo in_array($imageId, $images_check) ? 'checked' : ''; ?> />
                        <button type="button" class="button button-secondary remove-portfolio-image">Entfernen</button>
                    </li>

                        



                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <?php
    }
}



function render_tableau_images_meta_box( $post ) {
    // Die gespeicherten Bild-IDs aus den Post-Metadaten abrufen
    $imageIds = get_post_meta( $post->ID, '_portfolio_images', true );

    // Sicherheitsnonce hinzufügen
    wp_nonce_field( 'tableau_images_nonce', 'tableau_images_nonce' );
    ?>


<hr><hr><hr>
    <div class="col-md-6">
        <div id="image-column-1" class="image-column"></div>
    </div>
    <div class="col-md-6">
        <div id="image-column-2" class="image-column"></div>
    </div>

    <hr><hr><hr>
    <style>
    .image-column {
        border: 1px solid #ccc; /* Randstil und -farbe anpassen */
        height: 400px; /* Höhe der Spalten anpassen */
    }
</style>


    <label for="tableau_images">Bilder:</label>
    <ul id="tableau-images-list">



        <?php 
        if ( is_array($imageIds) && !empty($imageIds) ) : 
            foreach ( $imageIds as $index => $imageId ) : 
                // URL des Bildes holen
                $image = wp_get_attachment_image_src( $imageId, 'full' )[0]; 
        ?>
        
        <li>
            <!-- Bild ID als verborgenes Eingabefeld hinzufügen -->
            <input type="hidden" name="tableau_images[]" value="<?php echo esc_attr( $imageId ); ?>" />
            <!-- Bild anzeigen -->
            <img src="<?php echo esc_attr( $image ); ?>" width="100" height="auto" />
            <!-- Entfernen-Button hinzufügen -->
         
        </li>
        <?php 
            endforeach; 
        endif; 
        ?>
    </ul>
   

    <?php
}




//
//
//
function add_tableau_meta_box() {
    add_meta_box(
        'tableau_images_meta_box', // ID der Meta-Box
        'Bilder', // Titel der Meta-Box
        'render_tableau_images_meta_box', // Callback-Funktion, die den Inhalt der Meta-Box anzeigt
        'tableau', // Post-Typ, zu dem die Meta-Box hinzugefügt wird
        'normal', // Kontext, in dem die Meta-Box angezeigt wird ('normal', 'advanced' oder 'side')
        'high' // Priorität innerhalb des Kontexts ('high', 'core', 'default' oder 'low')
    );
}
add_action( 'add_meta_boxes', 'add_tableau_meta_box' );



function enqueue_custom_scripts() {
    wp_enqueue_script( 'backend-tableau-script', get_template_directory_uri() . '/js/backendTableau.js', array( 'jquery', 'jquery-ui-droppable' ), '1.0', true );
 }
 add_action( 'admin_enqueue_scripts', 'enqueue_custom_scripts' );
 



 add_action('wp_ajax_image_dropped', 'handle_image_dropped');
 function handle_image_dropped() {
     // Überprüfen Sie die Nonce, um die Sicherheit zu gewährleisten
     check_ajax_referer('ajax_nonce', 'nonce');
 
     // Extrahieren Sie die Bild-ID und die Spalten-ID aus der Post-Anforderung
     $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
     $column_id = isset($_POST['column_id']) ? sanitize_text_field($_POST['column_id']) : '';
 
     // Überprüfen Sie, ob die Bild-ID und die Spalten-ID gültig sind
     if ($image_id > 0 && !empty($column_id)) {
         // Führen Sie die entsprechende Aktion durch, z.B. das Bild der Spalte in der Datenbank zuweisen
         // ...
         wp_send_json_success('Das Bild wurde erfolgreich der Spalte zugewiesen.');
     } else {
         wp_send_json_error('Die Bild-ID oder die Spalten-ID ist ungültig.');
     }
 
     // Beenden Sie die Ausführung
     wp_die();
 }
 

// Funktion zum Speichern der Bild- und Spalten-Änderungen
function saveImageColumnChanges() {
    if (isset($_POST['image_order'])) {
      $imageOrder = $_POST['image_order'];
  
      // Speichere die Bild- und Spalten-Änderungen in der Datenbank oder führe andere spezifische Aktionen durch
  
      // Gib eine Bestätigungsmeldung oder einen Status zurück
      wp_send_json_success('Bild- und Spalten-Änderungen wurden erfolgreich gespeichert.');
    } else {
      // Gib eine Fehlermeldung zurück, falls keine Daten empfangen wurden
      wp_send_json_error('Keine Daten empfangen.');
    }
  }
  add_action('wp_ajax_save_image_column_changes', 'saveImageColumnChanges');

  
  
?>