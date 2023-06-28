jQuery(document).ready(function() {


    jQuery('#portfolio-images-list, #tableau-images-list').sortable();


    console.log('admin.js wurde geladen!');

    // Definieren der portfolioImageCheckboxes-Variable
    window.portfolioImageCheckboxes = [];

    // Funktion zum Hinzufügen der Portfolio-Bilder
    window.addPortfolioImages = function() {
        var frame = wp.media({
            title: 'Bilder auswählen',
            multiple: true,
            library: {
                type: 'image'
            }
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var imageList = jQuery('#portfolio-images-list');

            for (var i = 0; i < attachments.length; i++) {
                var listItem = jQuery('<li></li>');
                var hiddenInput = jQuery('<input type="hidden" name="portfolio_images[]" />').val(attachments[i].id);
                var image = jQuery('<img />').attr('src', attachments[i].url).attr('width', 100).attr('height', 'auto');
                var removeButton = jQuery('<button type="button" class="button button-secondary remove-portfolio-image">Entfernen</button>');
                var checkBox = jQuery('<input type="checkbox" name="portfolio_image_checkbox[]" value="'+ attachments[i].id +'" />');
                checkBox.prop('checked', window.portfolioImageCheckboxes.includes(attachments[i].id));

                removeButton.on('click', function() {
                    jQuery(this).parent().remove();
                });

                listItem.append(hiddenInput);
                listItem.append(image);
                listItem.append(removeButton);
                listItem.append(checkBox);

                imageList.append(listItem);
            }
        });

        frame.open();
    };

    // Funktion zum Entfernen eines Portfolio-Bildes
    function removePortfolioImage() {
        jQuery(this).parent().remove();
    }

    // Funktion zum Speichern der Reihenfolge der Portfolio-Bilder
    function savePortfolioImageOrder() {
        var imageList = jQuery('#portfolio-images-list');
        var imageOrder = [];

        imageList.find('li').each(function() {
            var imageId = jQuery(this).find('input[name="portfolio_images[]"]').val();
            imageOrder.push(imageId);
        });

        var data = {
            action: 'portfolio_image_order',
            post_id: jQuery('#post_ID').val(),
            image_order: imageOrder
        };

        jQuery.post(ajaxurl, data, function(response) {
            console.log(response.data);
        });
    }

    // Event-Handler für das Klicken auf "Bilder auswählen" Button
    jQuery('#add-portfolio-images').on('click', function(e) {
        e.preventDefault();
        addPortfolioImages();
    });

    // Event-Handler für das Klicken auf "Entfernen" Button eines Portfolio-Bildes
    jQuery(document).on('click', '.remove-portfolio-image', removePortfolioImage);

    // AJAX-Anfrage zum Abrufen von Portfolio-Bildern und Hinzufügen von Tableau-Bildern beim Laden der Seite
    function addTableauImagesFromPortfolio() {
        var portfolioId = jQuery('#portfolio_id').val();

        // Überprüfen, ob die Portfolio ID vorhanden ist
        if (portfolioId !== '') {
            jQuery.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_portfolio_images',
                    portfolio_id: portfolioId
                },
                success: function(response) {
                    if (response.success) {
                        for (var i = 0; i < response.data.images.length; i++) {
                            var image = response.data.images[i];
                            addTableauImage(image.id, image.url);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                }
            });
        } else {
            console.error('Portfolio ID fehlt');
        }
    }

    addTableauImagesFromPortfolio();

    // Funktion zum Hinzufügen eines Tableau-Bildes
// Funktion zum Hinzufügen eines Tableau-Bildes
// Funktion zum Hinzufügen eines Tableau-Bildes
// Funktion zum Hinzufügen eines Tableau-Bildes
function addTableauImage(imageId, imageUrl) {
    var imageList = jQuery('#tableau-images-list');
  
    var listItem = jQuery('<li></li>');
    var hiddenInput = jQuery('<input type="hidden" name="tableau_images[]" />').val(imageId);
    var image = jQuery('<img />').attr('src', imageUrl).addClass('img-fluid').on('load', function() {
        jQuery(this).removeAttr('width').removeAttr('height');
      });
      
    var removeButton = jQuery('<button type="button" class="button button-secondary remove-tableau-image">Entfernen</button>');
  
    removeButton.on('click', function() {
      jQuery(this).parent().remove();
    });
  
    listItem.append(hiddenInput);
    listItem.append(image);
    listItem.append(removeButton);
  
    imageList.append(listItem);
  
    // Hinzugefügtes Bild als Draggable-Element initialisieren
    listItem.addClass('draggable-image');
    listItem.data('image-id', imageId);
    listItem.draggable({ revert: 'invalid' });
  }
  

    // Event-Handler für das Klicken auf "Bilder auswählen" Button für Tableau-Bilder
    window.addTableauImages = function() {
        var frame = wp.media({
            title: 'Bilder auswählen',
            multiple: true,
            library: {
                type: 'image'
            }
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var imageList = jQuery('#tableau-images-list');

            for (var i = 0; i < attachments.length; i++) {
                var listItem = jQuery('<li></li>');
                var hiddenInput = jQuery('<input type="hidden" name="tableau_images[]" />').val(attachments[i].id);
                var image = jQuery('<img />').attr('src', attachments[i].url).attr('width', 100).attr('height', 'auto');
                var removeButton = jQuery('<button type="button" class="button button-secondary remove-tableau-image">Entfernen</button>');

                removeButton.on('click', function() {
                    jQuery(this).parent().remove();
                });

                listItem.append(hiddenInput);
                listItem.append(image);
                listItem.append(removeButton);

                imageList.append(listItem);

                // Hinzugefügtes Bild als Draggable-Element initialisieren
                listItem.addClass('draggable-image');
                listItem.data('image-id', attachments[i].id);
                listItem.draggable({ revert: 'invalid' });
            }
        });

        frame.open();
    }

    // Event-Handler für das Klicken auf "Entfernen" Button eines Tableau-Bildes
    jQuery(document).on('click', '.remove-tableau-image', function() {
        jQuery(this).parent().remove();
    });

    // Funktion zum Generieren der Tableau-Beiträge
    function generateTableauPosts() {
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'generate_tableau_posts'
            },
            success: function(response) {
                if (response.success) {
                    alert('Tableau-Beiträge wurden erfolgreich generiert.');
                } else {
                    alert('Fehler beim Generieren der Tableau-Beiträge.');
                }
            }
        });
    }

    // Event-Handler für das Klicken auf "Tableau-Beiträge generieren" Button
    jQuery('#generate-tableau-posts').on('click', function() {
        generateTableauPosts();
    });
});

// Funktion zum Anzeigen oder Ausblenden von Bildern basierend auf den ausgewählten Checkbox-Werten
function toggleTableauImages() {
    var checkedCheckboxes = jQuery('input[name="portfolio_image_checkbox[]"]:checked');
    var tableauImages = jQuery('#tableau-images-list li');

    tableauImages.each(function() {
        var imageId = jQuery(this).find('input[name="tableau_images[]"]').val();

        if (checkedCheckboxes.filter('[value="' + imageId + '"]').length > 0) {
            jQuery(this).show();
        } else {
            jQuery(this).hide();
        }
    });
}

jQuery(document).on('change', 'input[name="portfolio_image_checkbox[]"]', function() {
    toggleTableauImages();
});





// Funktion, die einen AJAX-Aufruf durchführt und die Bild-ID und die Spalten-ID sendet
function sendImageIdToServer(imageId, columnId) {
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'image_dropped',
            image_id: imageId,
            column_id: columnId
        },
        success: function(response) {
            console.log('Bild-ID erfolgreich an den Server gesendet');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Fehler beim Senden der Bild-ID an den Server:', textStatus, errorThrown);
        }
    });
}

// Ändern Sie die drop-Funktion, um die sendImageIdToServer-Funktion aufzurufen

// Initialisierung der Drop-Zonen
jQuery('.drop-zone').droppable({
    drop: function(event, ui) {
        var imageId = ui.helper.data('image-id');
        var columnId = jQuery(this).attr('id'); // ID der Spalte erhalten
        var droppedImage = ui.helper.clone().draggable({ revert: 'invalid' });
        var hiddenInput = jQuery('<input type="hidden" name="dropped_images[]" />').val(imageId);
    
        droppedImage.append(hiddenInput);
        jQuery(this).append(droppedImage);
    
        // Senden der Bild-ID und der Spalten-ID an den Server
        sendImageIdToServer(imageId, columnId);
    }




    
}

);

// Initialisierung der Bilder als draggable Elemente
jQuery('#tableau-images-list li').draggable({
    revert: 'invalid',
    helper: 'clone'
});


// Event-Handler für das Hinzufügen eines Tableau-Bildes
jQuery(document).on('click', '#add-tableau-image', function() {
    // Code zum Hinzufügen des Bildes in die `image-column`
  
    savePageState(); // Aufrufen der Funktion zum Speichern des Seitenzustands
  });
// Event-Handler für das Entfernen eines Tableau-Bildes
jQuery(document).on('click', '.remove-tableau-image', function() {
    // Code zum Entfernen des Bildes aus der `image-column`
  
    savePageState(); // Aufrufen der Funktion zum Speichern des Seitenzustands
  });
// Event-Handler für das Neuanordnen der Tableau-Bilder
jQuery('.image-column').sortable({
    // Optionen und Code für das Sortieren der Bilder
  
    stop: function(event, ui) {
      // Code, der nach dem Abschließen des Sortiervorgangs ausgeführt wird
  
      savePageState(); // Aufrufen der Funktion zum Speichern des Seitenzustands
    }
  });
      