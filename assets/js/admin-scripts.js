jQuery(document).ready(function($) {
    // Uploading files
    var file_frame;
    var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
    var set_to_post_id = 0; // Set this
    $('#listinger_upload_images_button').on('click', function(event){
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            file_frame.open();
            return;
        } else {
            wp.media.model.settings.post.id = set_to_post_id;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select images to upload',
            button: {
                text: 'Use these images',
            },
            multiple: true // Set to true to allow multiple files to be selected
        });

        // When images are selected, run a callback.
        file_frame.on( 'select', function() {
            var attachments = file_frame.state().get('selection').toJSON();
            var image_ids = attachments.map(function(attachment) {
                return attachment.id;
            }).join(',');

            $('#listinger_product_images').val(image_ids);

            var images_html = '';
            attachments.forEach(function(attachment) {
                images_html += '<img src="' + attachment.url + '" style="max-height: 400px;">';
            });

            $('#listinger_product_images_preview').html(images_html);
            wp.media.model.settings.post.id = wp_media_post_id;
        });

        // Finally, open the modal
        file_frame.open();
    });

    // Restore the main ID when the add media button is pressed
    $('a.add_media').on('click', function() {
        wp.media.model.settings.post.id = wp_media_post_id;
    });

    $('#listinger_remove_images_button').on('click', function() {
        $('#listinger_product_images').val('');
        $('#listinger_product_images_preview').html('');
    });
});
