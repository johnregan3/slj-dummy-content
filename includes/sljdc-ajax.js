( function ($) {

	$( document ).ready( function() {

		$( "#generate-content" ).click( function() {

			posts  = $( '.sljdc_num_posts option:selected' ).val();
			pages  = $( '.sljdc_num_pages option:selected' ).val();

			sljdc_create_content( posts, pages );
		});

		$( "#delete-content" ).click( function() {

			sljdc_delete_content();

		});

		function sljdc_create_content( posts, pages ) {
			$.ajax({
				type : "post",
				dataType : "json",
				url : sljdc_ajax.ajaxurl,
				data : { action: "sljdc_generate", posts : posts, pages : pages, nonce : sljdc_ajax.sljdc_nonce },
				beforeSend: function() {
					$( '#generate-content' ).addClass( 'button-primary-disabled' );
					$( '.generate .spinner' ).css('visibility', 'visible');
				},
				success: function(response) {
					$( '#generate-content' ).removeClass( 'button-primary-disabled' );
					$( '.generate .spinner' ).css('visibility', 'hidden');
					$( '#generate-message' ).html( 'Success!' ).fadeTo( 'slow', 1 ).delay( '3000' ).fadeTo( 'slow', 0 );
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log( jqXHR );
					console.log( textStatus );
					console.log( errorThrown );
					$( '#generate-content' ).removeClass( 'button-primary-disabled' );
					$( '.generate .spinner' ).css('visibility', 'hidden');
					$( '#generate-message' ).html( 'Generation Failed.' ).addClass( 'failure' ).fadeTo( 'slow', 1 ).delay( '3000' ).fadeTo( 'slow', 0 );
				}

			});
		};

		function sljdc_delete_content( ) {
			$.ajax({
				type : "post",
				dataType : "json",
				url : sljdc_ajax.ajaxurl,
				data : { action: "sljdc_delete", nonce : sljdc_ajax.sljdc_nonce },
				beforeSend: function() {
					$( '#delete-content' ).addClass( 'button-primary-disabled' );
					$( '.delete .spinner' ).css('visibility', 'visible');
				},
				success: function( response ) {
					$( '#delete-content' ).removeClass( 'button-primary-disabled' );
					$( '.delete .spinner' ).css('visibility', 'hidden');
					$( '#delete-message' ).html( 'All Content Deleted.' ).slideDown( 'fast' ).fadeTo( 'slow', 1 ).delay( '3000' ).fadeTo( 'slow', 0 );
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log( jqXHR );
					console.log( textStatus );
					console.log( errorThrown );
					$( '#delete-content' ).removeClass( 'button-primary-disabled' );
					$( '.delete .spinner' ).css('visibility', 'hidden');
					$( '#delete-message' ).html( 'Deletion Failed.' ).addClass( 'failure' ).fadeTo( 'slow', 1 ).delay( '3000' ).fadeTo( 'slow', 0 );

				}

			});
		};



	});
})(jQuery);