(function ($) {
	'use strict';

	$(function (){
		/**
		 * 參考：https://philkurth.com.au/articles/pass-data-php-javascript-wordpress/
		 * 準備 parse 由 PHP 啵嘅 data
		 */
		// if our data block is not available, don't do anything
		var $media_selector_data = $('#media_selector_data');
		if (!$media_selector_data.length) {
			return;
		}
		// attempt to parse the content of our data block
		try {
			$media_selector_data = JSON.parse( $media_selector_data.text() );
		} catch (err) { // invalid json
			return;
		}
		// if parsing was successful, you should see the data in your console
		console.log( $media_selector_data );

		// Uploading files
		//var file_frame;
		//var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
		//var set_to_post_id = $media_selector_data['media_selector_attachment_id']; // Set this

		$('#upload_image_button').on('click', function( event ){

			event.preventDefault();

			var file_frame;
			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				// Set the post ID to what we want
				//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				// Open frame
				file_frame.open();
				return;
			} else {
				// Set the wp.media post id so the uploader grabs the ID we want when initialised
				//wp.media.model.settings.post.id = set_to_post_id;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: 'Select a image to upload',
				button: {
					text: 'Use this image',
				},
				multiple: false	// Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// We set multiple to false so only get one image from the uploader
				//console.log( file_frame.state().get('selection') );
				//var selections = file_frame.state().get( 'selection' );
				var attachment = file_frame.state().get('selection').first().toJSON();
				//console.log( attachment );
				
				// Do something with attachment.id and/or attachment.url here
				$( '#' + $media_selector_data.media_selector_image_preview_id ).attr( 'src', attachment['url'] ).css( 'width', 'auto' );
				$( '#' + $media_selector_data.media_selector_image_attachment_id ).val( attachment['id'] );
				$( '#' + $media_selector_data.media_selector_text_id ).attr( 'value', attachment['url'] );

				// Restore the main post ID
				//wp.media.model.settings.post.id = wp_media_post_id;
			});

			// Finally, open the modal
			file_frame.open();
		});

		// Restore the main ID when the add media button is pressed
		//$( 'a.add_media' ).on( 'click', function() {
		//	wp.media.model.settings.post.id = wp_media_post_id;
		//});

		/**
		 * 當 media_selector_text_id 改变緊，顯示個 image
		 * 當 user 由 media library 用 copy & paste 個 image path 時就會見到效果
		 */
		$( '#' + $media_selector_data.media_selector_text_id ).on( 'input', function(){

			var image_path = $( '#' + $media_selector_data.media_selector_text_id ).val();

			if ( image_path ){
				$( '#' + $media_selector_data.media_selector_image_preview_id ).attr( 'src', image_path ).css( 'width', 'auto' );
			}
		});

	});

})(jQuery);