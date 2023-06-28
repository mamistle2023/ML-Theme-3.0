<?php 
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
    $sizes['portfolio-thumbnail'] = array(
        'width'  => 400,
        'height' => 9999,
        'crop'   => false,
    );
    return $sizes;
}
add_filter( 'image_size_names_choose', 'customize_media_library_image_size' );
add_image_size( 'portfolio-thumbnail', 400, 9999, false );

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
    $images = get_post_meta( $post->ID, '_portfolio_images', true );
    $images_check = get_post_meta( $post->ID, '_portfolio_images_check', true );

    wp_nonce_field( 'portfolio_images_nonce', 'portfolio_images_nonce' );
    ?>
    <label for="portfolio_images">Bilder hinzufügen:</label>
    <input type="button" class="button button-secondary" value="Bilder auswählen" onclick="addPortfolioImages();" />
    <ul id="portfolio-images-list">
        <?php if ( ! empty( $images ) ) : ?>
            <?php foreach ( $images as $key => $image ) : ?>
                <li>
                    <input type="hidden" name="portfolio_images[]" value="<?php echo esc_attr( $image ); ?>" />
                    <img src="<?php echo esc_attr( $image ); ?>" width="100" height="auto" />
                    <label>
                        <input type="checkbox" name="portfolio_images_check[]" value="<?php echo esc_attr( $image ); ?>" <?php checked( in_array( $image, $images_check ), true ); ?> /> Tableau
                    </label>
                    <button type="button" class="button button-secondary remove-portfolio-image">Entfernen</button>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <?php
}

function save_portfolio_images_meta_box_updated( $post_id ) {
    if ( ! isset( $_POST['portfolio_images_nonce'] ) || ! wp_verify_nonce( $_POST['portfolio_images_nonce'], 'portfolio_images_nonce' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['portfolio_images'] ) ) {
        $images = array_map( 'sanitize_text_field', $_POST['portfolio_images'] );
        update_post_meta( $post_id, '_portfolio_images', $images );

        // Speichern des Status des Checkbox-Feldes
        if ( isset( $_POST['portfolio_images_check'] ) ) {
            $images_check = array_map( 'sanitize_text_field', $_POST['portfolio_images_check'] );
            update_post_meta( $post_id, '_portfolio_images_check', $images_check );
        } else {
            delete_post_meta( $post_id, '_portfolio_images_check' );
        }
    } else {
        delete_post_meta( $post_id, '_portfolio_images' );
        delete_post_meta( $post_id, '_portfolio_images_check' );
    }
}
remove_action( 'save_post', 'save_portfolio_images_meta_box' );
add_action( 'save_post', 'save_portfolio_images_meta_box_updated' );

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

?>
