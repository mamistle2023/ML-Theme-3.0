jQuery(document).ready(function() {
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
    function addTableauImage(imageId, imageUrl) {
        var imageList = jQuery('#tableau-images-list');

        var listItem = jQuery('<li></li>');
        var hiddenInput = jQuery('<input type="hidden" name="tableau_images[]" />').val(imageId);
        var image = jQuery('<img />').attr('src', imageUrl).attr('width', 100).attr('height', 'auto');
        var removeButton = jQuery('<button type="button" class="button button-secondary remove-tableau-image">Entfernen</button>');

        removeButton.on('click', function() {
            jQuery(this).parent().remove();
        });

        listItem.append(hiddenInput);
        listItem.append(image);
        listItem.append(removeButton);
        // Hinzugefügtes Bild als Draggable-Element initialisieren
      // Hinzugefügtes Bild als Draggable-Element initialisieren
  listItem.addClass('draggable-image');
  listItem.data('image-id', imageId);
  listItem.draggable({ revert: 'invalid' });

  imageList.append(listItem);

  // Bild als responsive (img-fluid) markieren
  image.addClass('img-fluid');
    }
// Initialisierung der Original-Elemente als draggable
jQuery('#tableau-images-list li').addClass('draggable-image').draggable({ revert: 'invalid' });



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
  
  jQuery(document).ready(function() {
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
  
    // Initialisierung der Drop-Zonen mit Bootstrap Grid
    jQuery(document).ready(function() {
        console.log('admin.js wurde geladen!');
    
        // ...
    
        // Initialisierung der Drop-Zonen mit Bootstrap Grid
        jQuery('.image-column').sortable({
            connectWith: '.image-column',
            revert: true,
            placeholder: 'sortable-placeholder',
            start: function(event, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(event, ui) {
                var droppedImage = ui.item;
    
                // Überprüfen, ob das Feld bereits ein Bild enthält
                if (droppedImage.siblings().length > 0) {
                    var columnId = droppedImage.closest('.image-column').attr('id');
    
                    // Zurücksetzen des vorhandenen Bildes in die Liste
                    var originalImage = droppedImage.siblings().first();
                    var originalImageId = originalImage.find('input[name="tableau_images[]"]').val();
                    var originalImageClone = originalImage.clone();
                    originalImage.remove();
                    jQuery('#tableau-images-list').append(originalImageClone);
    
                    // Hinzufügen des neuen Bildes in das Feld
                    droppedImage.appendTo('#' + columnId);
    
                    // Entfernen des abgelegten Bildes aus der Liste
                    droppedImage.addClass('dropped-image');
    
                    // Senden der Bild-ID und der Spalten-ID an den Server
                    sendImageIdToServer(originalImageId, columnId);
                } else {
                    var imageId = droppedImage.find('input[name="tableau_images[]"]').val();
                    var columnId = droppedImage.closest('.image-column').attr('id');
    
                    // Entfernen des abgelegten Bildes aus der Liste
                    droppedImage.addClass('dropped-image');
    
                    // Senden der Bild-ID und der Spalten-ID an den Server
                    sendImageIdToServer(imageId, columnId);
                }
            }
        }).disableSelection();
    
        // Initialisierung der Original-Elemente als draggable
        jQuery('#tableau-images-list li').addClass('draggable-image').draggable({
            revert: 'invalid',
            connectToSortable: '.image-column',
            helper: 'clone'
        });
    
        // ...
    });
    
      
    // ...
});

jQuery(document).ready(function($) {
    // Event-Handler für das Klicken auf den Update-Button
    $(document).on('click', '#post .save-post', function(e) {
      e.preventDefault();
  
      // Erfasse den aktuellen Seitenzustand
      var pageState = {
        // Hier kannst du den Seitenzustand erfassen, der gespeichert werden soll
        // Zum Beispiel: Tableau-Bilder, Sortierreihenfolge, etc.
      };
  
      // Sende den Seitenzustand an den Server, um ihn zu speichern
      // Hier kannst du AJAX oder eine andere Methode verwenden, um die Daten an den Server zu senden
  
      console.log('Seitenzustand aktualisiert:', pageState);
  
      // Führe den ursprünglichen Klick auf den Update-Button aus
      $(this).trigger('click');
    });
  });
  // Funktion zum Speichern der Bild- und Spalten-Änderungen über AJAX
function saveImageColumnChanges() {
    var imageList = jQuery('.image-column');
  
    var imageOrder = {};
    imageList.each(function(index) {
      var columnId = jQuery(this).attr('id');
      var imageIds = [];
  
      jQuery(this).find('li').each(function() {
        var imageId = jQuery(this).find('input[name="tableau_images[]"]').val();
        imageIds.push(imageId);
      });
  
      imageOrder[columnId] = imageIds;
    });
  
    var data = {
      action: 'save_image_column_changes',
      image_order: imageOrder
    };
  
    jQuery.post(ajaxurl, data, function(response) {
      console.log(response);
      // Hier kannst du entsprechend auf die Rückgabe der AJAX-Anfrage reagieren
      if (response.success) {
        // Erfolgreich gespeichert
        console.log(response.data);
      } else {
        // Fehler beim Speichern
        console.log(response.data);
      }
    });
  }
  
  // Event-Handler für den Klick auf den "Update" / "Aktualisieren" -Button
  jQuery('#publish').on('click', function(e) {
    e.preventDefault();
    saveImageColumnChanges();
  });
  