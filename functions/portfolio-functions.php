<?php 

//
//
//

function enqueue_admin_styles() {
    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css' );
}
add_action( 'admin_enqueue_scripts', 'enqueue_admin_styles' );



// Enqueue AJAX script and localize
function tableau_posts_generator_admin_enqueue_scripts($hook) {
    if ('toplevel_page_tableau-posts-generator' !== $hook) {
        return;
    }

    wp_enqueue_script('tableau-posts-generator-admin', get_template_directory_uri() . '/admin.js', array('jquery', 'wp-util'), '1.0', true);
    wp_localize_script('tableau-posts-generator-admin', 'TableauPostsGenerator', array(
        'nonce' => wp_create_nonce('tableau_posts_generation_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('admin_enqueue_scripts', 'tableau_posts_generator_admin_enqueue_scripts');

// AJAX handler
function ajax_generate_tableau_posts_handler() {
    check_ajax_referer('tableau_posts_generation_nonce', 'nonce');
    $response = array(
        'status' => 'success',
        'message' => 'Tableau posts successfully generated.',
    );
    wp_send_json($response);
}
add_action('wp_ajax_generate_tableau_posts', 'ajax_generate_tableau_posts_handler');

























//
//
// Registrieren des benutzerdefinierten Beitragstyps
function custom_portfolio_post_type() {
    $labels = array(
        'name'               => 'Portfolio',
        'singular_name'      => 'Portfolio',
        'menu_name'          => 'Portfolio',
        'name_admin_bar'     => 'Portfolio',
        'add_new'            => 'Neues Portfolio hinzufügen',
        'add_new_item'       => 'Neues Portfolio hinzufügen',
        'edit_item'          => 'Portfolio bearbeiten',
        'new_item'           => 'Neues Portfolio',
        'view_item'          => 'Portfolio anzeigen',
        'all_items'          => 'Alle Portfolios',
        'search_items'       => 'Portfolios durchsuchen',
        'parent_item_colon'  => 'Übergeordnetes Portfolio:',
        'not_found'          => 'Keine Portfolios gefunden.',
        'not_found_in_trash' => 'Keine Portfolios im Papierkorb gefunden.'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'portfolio' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array( 'title', 'thumbnail' ),
        'menu_icon'           => 'dashicons-portfolio' // Optional: Icon für das Menü
    );

    register_post_type( 'portfolio', $args );
}
add_action( 'init', 'custom_portfolio_post_type' );

// Entfernen der Standardfelder
function remove_default_post_type_fields() {
    remove_post_type_support( 'portfolio', 'editor' );
}
add_action( 'admin_menu', 'remove_default_post_type_fields' );

// Hinzufügen eines mehrzeiligen Textfelds
function add_portfolio_description_meta_box() {
    add_meta_box(
        'portfolio_description',
        'Beschreibung',
        'render_portfolio_description_meta_box',
        'portfolio',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'add_portfolio_description_meta_box' );

function render_portfolio_description_meta_box( $post ) {
    $description = get_post_meta( $post->ID, '_portfolio_description', true );

    wp_nonce_field( 'portfolio_description_nonce', 'portfolio_description_nonce' );
    ?>
    <label for="portfolio_description">Beschreibung:</label>
    <textarea name="portfolio_description" id="portfolio_description" rows="5" style="width: 100%;"><?php echo esc_textarea( $description ); ?></textarea>
    <?php
}

function save_portfolio_description_meta_box( $post_id ) {
    if ( ! isset( $_POST['portfolio_description_nonce'] ) || ! wp_verify_nonce( $_POST['portfolio_description_nonce'], 'portfolio_description_nonce' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['portfolio_description'] ) ) {
        update_post_meta( $post_id, '_portfolio_description', sanitize_textarea_field( $_POST['portfolio_description'] ) );
    }
}
add_action( 'save_post', 'save_portfolio_description_meta_box' );

// Anpassen der Bildgröße in der Medienbibliothek
function customize_media_library_image_size( $sizes ) {
  //  $sizes['portfolio-thumbnail'] = array(
   //     'width'  => 400,
   //     'height' => 9999,
   //     'crop'   => false,
   // );
    return $sizes;
}
add_filter( 'image_size_names_choose', 'customize_media_library_image_size' );
//add_image_size( 'portfolio-thumbnail', 400, 9999, false );

// Hinzufügen von Metabox für Bilder
function add_portfolio_images_meta_box() {
    add_meta_box(
        'portfolio_images',
        'Bilder',
        'render_portfolio_images_meta_box',
        'portfolio',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'add_portfolio_images_meta_box' );

function render_portfolio_images_meta_box( $post ) {
    $imageIds = get_post_meta( $post->ID, '_portfolio_images', true );
    $checkboxes = get_post_meta( $post->ID, '_portfolio_image_checkbox', true ) ?: [];

    wp_localize_script('custom-admin-script', 'portfolioImageCheckboxes', $checkboxes);
    
    wp_nonce_field( 'portfolio_images_nonce', 'portfolio_images_nonce' ); // Sicherheits-Nonce hinzufügen
    ?>
    <label for="portfolio_images">Bilder hinzufügen:</label>
    <input type="button" class="button button-secondary" value="Bilder auswählen" onclick="addPortfolioImages();" />
    <ul id="portfolio-images-list">
        <?php if ( is_array($imageIds) && !empty($imageIds) ) : ?>
            <?php foreach ( $imageIds as $index => $imageId ) : ?>
                
                <?php $image = wp_get_attachment_image_src( $imageId, 'full' )[0]; ?> <!-- URL des Bildes holen -->
                <li class="draggable">
                    <input type="hidden" name="portfolio_images[]"   value="<?php echo esc_attr( $imageId ); ?>" />
                    <img src="<?php echo esc_attr( $image ); ?>"  width="100" height="auto" />
                    
                    




                    <input type="checkbox" name="portfolio_image_checkbox[]" value="<?php echo esc_attr( $imageId ); ?>" <?php echo in_array($imageId, $checkboxes) ? 'checked' : ''; ?> />
                    <button type="button" class="button button-secondary remove-portfolio-image">Entfernen</button>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <?php
}


    


function save_portfolio_images_meta_box( $post_id ) {
    if ( ! isset( $_POST['portfolio_images_nonce'] ) || ! wp_verify_nonce( $_POST['portfolio_images_nonce'], 'portfolio_images_nonce' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['portfolio_images'] ) ) {
        $images = array_map( 'sanitize_text_field', $_POST['portfolio_images'] );
        update_post_meta( $post_id, '_portfolio_images', $images );
    } else {
        delete_post_meta( $post_id, '_portfolio_images' );
    }

    // Hier speichern Sie die Checkbox-Werte
    if ( isset( $_POST['portfolio_image_checkbox'] ) ) {
        $checkBoxValues = array_map( 'sanitize_text_field', $_POST['portfolio_image_checkbox'] );
        update_post_meta( $post_id, '_portfolio_image_checkbox', $checkBoxValues );
    } else {
        delete_post_meta( $post_id, '_portfolio_image_checkbox' );
    }
}

add_action( 'save_post', 'save_portfolio_images_meta_box' );


// Enqueue Custom Admin Scripts
function enqueue_custom_admin_scripts() {
    wp_enqueue_media(); // Einbinden des media-editor-Skripts

    wp_enqueue_script( 'custom-admin-script', get_template_directory_uri() . '/js/admin.js', array( 'jquery', 'media-editor', 'jquery-ui-sortable' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'enqueue_custom_admin_scripts' );

function savePortfolioImageOrder() {
    $imageList = $_POST['image_list'];
    $imageOrder = array();

    foreach ($imageList as $image) {
        $imageOrder[] = sanitize_text_field($image);
    }

    update_post_meta($_POST['post_id'], '_portfolio_images', $imageOrder);
    wp_send_json_success('Bildreihenfolge erfolgreich gespeichert.');
}

add_action('wp_ajax_portfolio_image_order', 'savePortfolioImageOrder');

function save_portfolio_image_order() {
    if (isset($_POST['image_order'])) {
        $post_id = $_POST['post_id'];
        $image_order = $_POST['image_order'];

        update_post_meta($post_id, '_portfolio_image_order', $image_order);
    }

    wp_die();
}
add_action('wp_ajax_portfolio_image_order', 'save_portfolio_image_order');

////
///
//
function generate_tableau_posts() {
    // Überprüfen, ob die Tableau-Beiträge bereits generiert wurden
    $tableau_generated = get_option( 'tableau_posts_generated', true );

    if ( $tableau_generated ) {
        return; // Funktion beenden, wenn die Beiträge bereits generiert wurden
    }

    $portfolio_args = array(
        'post_type'      => 'portfolio',
        'posts_per_page' => -1,
    );

    $portfolio_query = new WP_Query( $portfolio_args );

    if ( $portfolio_query->have_posts() ) {
        while ( $portfolio_query->have_posts() ) {
            $portfolio_query->the_post();

            // Restlicher Code zum Erstellen der Tableau-Beiträge
            $portfolio_id = get_the_ID();
            $portfolio_title = get_the_title();
            $portfolio_description = get_post_meta( $portfolio_id, '_portfolio_description', true );
            $portfolio_images = get_post_meta( $portfolio_id, '_portfolio_images', true );

            // Filtern Sie die Bilder basierend auf der Checkbox-Auswahl
            $filtered_images = array_filter( $portfolio_images, function( $image_id ) use ( $portfolio_id ) {
                $checkbox_values = get_post_meta( $portfolio_id, '_portfolio_image_checkbox', true );
                return in_array( $image_id, $checkbox_values );
            } );

            $tableau_post_args = array(
                'post_type'    => 'tableau',
                'post_title'   => $portfolio_title,
                'post_content' => $portfolio_description,
                'post_status'  => 'publish',
            );

            $tableau_post_id = wp_insert_post( $tableau_post_args );

            if ( ! empty( $filtered_images ) ) {
                update_post_meta( $tableau_post_id, '_portfolio_images', $filtered_images );
            }
        }

        wp_reset_postdata();
    }

    // Setze die Option, um anzuzeigen, dass die Beiträge bereits generiert wurden
    update_option( 'tableau_posts_generated', true );
}
add_action( 'init', 'generate_tableau_posts' );
// Hier die generate_tableau_posts-Funktion aufrufen
generate_tableau_posts();



// Funktion zum Hinzufügen des Untermenüpunkts
function add_tableau_generate_button() {
    add_submenu_page(
        'edit.php?post_type=tableau', // Slug des übergeordneten Menüs (Tableau-Beiträge)
        'Tableau-Beiträge generieren', // Seitentitel
        'Tableau generieren', // Menütitel
        'manage_options', // Benutzerrolle, die Zugriff auf die Seite hat
        'generate_tableau_posts', // Slug der Seite
        'generate_tableau_posts_page' // Callback-Funktion zum Rendern der Seite
    );
}
add_action('admin_menu', 'add_tableau_generate_button');

// Callback-Funktion zum Rendern der Seite
function generate_tableau_posts_page() {
    ?>
    <div class="wrap">
        <h1>Tableau-Beiträge generieren</h1>
        <p>Hier können Sie neue Portfolio-Beiträge in Tableau-Beiträge umwandeln.</p>
        <button id="generate-tableau-posts" class="button button-primary">Tableau-Beiträge generieren</button>
    </div>
    <?php
}

//
//
//
function create_tableau_posts_from_portfolio() {
    // Alle Portfolio-Beiträge abrufen
    $portfolio_posts = get_posts(array(
        'post_type' => 'portfolio',
        'posts_per_page' => -1,  // Alle Portfolio-Beiträge abrufen
    ));

    // Durch alle Portfolio-Beiträge durchgehen
    foreach ( $portfolio_posts as $portfolio_post ) {
        // Überprüfen, ob bereits ein Tableau-Beitrag für dieses Portfolio erstellt wurde
        $existing_tableau_posts = get_posts(array(
            'post_type' => 'tableau',
            'meta_query' => array(
                array(
                    'key' => '_portfolio_id',  // Dies ist ein benutzerdefiniertes Metafeld, das wir verwenden, um den Portfolio-Beitrag zu verfolgen
                    'value' => $portfolio_post->ID,
                ),
            ),
        ));

        if ( empty($existing_tableau_posts) ) {
            // Es wurde noch kein Tableau-Beitrag für dieses Portfolio erstellt, also erstellen wir einen

            // Den Titel und die Beschreibung aus dem Portfolio-Beitrag abrufen
            $title = $portfolio_post->post_title;
            $description = get_post_meta( $portfolio_post->ID, '_portfolio_description', true );
            // Die Bild-IDs aus dem Portfolio-Beitrag abrufen
            $image_ids = get_post_meta( $portfolio_post->ID, '_portfolio_images', true );

            // Die Daten für den neuen Tableau-Beitrag
            $post_data = array(
                'post_type'     => 'tableau',
                'post_title'    => $title,  // Der Titel aus dem Portfolio-Beitrag
                'post_content'  => $description,  // Die Beschreibung aus dem Portfolio-Beitrag
                'post_status'   => 'publish',
            );

            // Den neuen Tableau-Beitrag erstellen und die ID des neuen Beitrags abrufen
            $tableau_post_id = wp_insert_post( $post_data );

            // Die Bild-IDs zum Tableau-Beitrag hinzufügen
            update_post_meta( $tableau_post_id, '_portfolio_images', $image_ids );
            

            // Die ID des Portfolio-Beitrags zum Tableau-Beitrag hinzufügen (damit wir wissen, welcher Portfolio-Beitrag diesem Tableau-Beitrag zugeordnet ist)
            update_post_meta( $tableau_post_id, '_portfolio_id', $portfolio_post->ID );
        }
    }
}

// Die create_tableau_posts_from_portfolio-Funktion an eine geeignete Aktion hängen
// In diesem Fall hängen wir sie an 'wp_loaded', das ist ein Hook, der ausgeführt wird, nachdem WordPress vollständig geladen wurde
add_action( 'wp_loaded', 'create_tableau_posts_from_portfolio' );

function my_ajax_callbacks() {
    // ... other AJAX callbacks ...

    // AJAX callback for 'get_checkbox_values'
    add_action('wp_ajax_get_checkbox_values', 'get_checkbox_values');

    function get_checkbox_values() {
        $post_id = $_POST['post_id'];
        $checkbox_values = get_post_meta($post_id, '_portfolio_image_checkbox', true);

        if ($checkbox_values) {
            $checkbox_values = maybe_unserialize($checkbox_values);
            wp_send_json_success(['checkbox_values' => $checkbox_values]);
        } else {
            wp_send_json_error();
        }
    }
}
add_action('init', 'my_ajax_callbacks');



?>
