<?php
/**
 * Samuel L. Jackson Dummy Content Generator
 *
 * Generate and Delete Dummy Content
 *
 * @package SLJ Dummy Content
 * @subpackage SLJ Generator
 */

/**
 * Generate Dummy Content
 *
 * @since 1.0
 */
add_action( 'wp_ajax_sljdc_generate', 'sljdc_generate' );
add_action( 'wp_ajax_nopriv_sljdc_generate', 'sljdc_generate' );

function sljdc_generate() {

	// Include raw content arrays
	include_once( 'input.php' );

	if ( ! wp_verify_nonce( $_POST['nonce'], 'sljdc_nonce' ) )
		die( 'Failed Nonce Verification' );

	$gen = new Sljdc_Generator;

	$numbers = $gen->get_numbers();

	foreach( $numbers as $type => $num ) {

		for ( $i = 1; $i <= $num; $i++ ) {

			$content_array = $gen->generate_paragraphs( $strings, $inline_elements );

			$content_array = $gen->generate_block_HTML( $content_array, $headers, $html_elements );

			$content_array = $gen->generate_video( $content_array, $videos );

			$post_id = $gen->save_post( $content_array, $headers, $type );

			$gen->add_featured_image( $post_id );

			$gen->add_comments( $post_id );

		}

	}

	exit();

}


class Sljdc_Generator {

	/**
	 * Fetches parameters sent via Ajax
	 *
	 * @since   1.0
	 * @return  array  $numbers  Requested number of posts for Posts and Pages
	 */
	function get_numbers() {
		$posts = ( isset( $_POST['posts'] ) ) ? $_POST['posts'] : 0 ;
		$pages = ( isset( $_POST['pages'] ) ) ? $_POST['pages'] : 0 ;
		$numbers = array( 'post' => $posts, 'page' => $pages );
		return $numbers;
	}


	/**
	 * Creates Paragraps of content for the Posts
	 *
	 * @since   1.0
	 * @param   array  $strings          Strings (paragraphs) of content from input.php
	 * @param   array  $inline_elements  Inline HTML elements to be used within the paragraphs
	 * @return  array  $content_array    Array of Paragraphs that include inline HTML elements
	 */
	function generate_paragraphs( $strings, $inline_elements ) {

		$content_array = array();

		// Generate Random Content
		$paragraph_rand = rand( 1, 5 );
		for ( $a = 1; $a <= $paragraph_rand; $a++ ) {
			$strings_rand = array_rand( $strings, 1 );
			$paragraph    = $strings[$strings_rand];

			// Inline HTML elements
			$inline_rand = rand( 1, 2 );
			if ( 1 == $inline_rand ) {

				// Get random inline HTML element
				$inline_element  = array_rand( $inline_elements, 1 );
				$inline_element  = $inline_elements[$inline_element];

				//fetch random string from $paragraph
				//split paragraph into sentences
				$sentences       = preg_split( '/(?<=[.?!])\s(?=[A-Z"\'])/', $paragraph );
				$sentence        = array_rand( $sentences, 1 );
				$target_sentence = $sentences[$sentence];
				//insert inline HTML elements
				$sentences[$sentence] = sprintf( '<%s>%s</%s>', $inline_element, $target_sentence, $inline_element );
				$paragraph       = implode( ' ', $sentences );

			}

			$content_array[] = '<p>' . $paragraph . '</p>';

		}

		return $content_array;

	}


	/**
	 * Add Block-level HTML elements to content
	 *
	 * @since   1.0
	 * @param   array  $content_array  Array of Paragraphs that include inline HTML elements
	 * @param   array  $headers        Array of shorter strings imported from input.php
	 * @param   array  $html_elements  Array of block-level HTML tags
	 * @return  array  $content_array  Array of Content that now includes block-level HTML elements
	 */
	function generate_block_HTML( $content_array, $headers, $html_elements ) {

		//random number of HTML block level elements
		$html_rand = rand( 1, 3 );

		for ( $b = 1; $b <= $html_rand; $b++ ) {
			$html_rand       = array_rand( $html_elements, 1 );
			$headers_rand    = array_rand( $headers, 1 );
			$element         = $html_elements[$html_rand];
			$content_array[] = sprintf( '<%s>%s</%s>', $element, $headers[$headers_rand], $element );
		}

		return $content_array;
	}


	/**
	 * Randomly add videos to content
	 *
	 * @since   1.0
	 * @param   array  $content_array  Array of Content that includes paragraphs and block-level HTML elements
	 * @param   array  $videos         Array of videos imported from input.php
	 * @return  array  $content_array  Array of Content that now includes videos
	 */
	function generate_video( $content_array, $videos ) {

		// Insert Video?
		$video_insert_rand = rand( 1, 3 );
		if ( $video_insert_rand == 2 ) {
			$videos_rand     = array_rand( $videos, 1 );
			$video           = $videos[$videos_rand];
			$content_array[] = '<p><iframe height="315" ' . $video . ' frameborder="0" allowfullscreen></iframe></p>';
		}

		return $content_array;
	}


	/**
	 * Create new Posts/Pages using random Content.
	 *
	 * @since   1.0
	 * @param   array   $content_array  Array of generated Content
	 * @param   array   $headers        Array of shorter strings imported from input.php
	 * @param   string  $type           Post Type (Post/Page)
	 * @return  int     $post_id        Post ID of newly-created Post
	 */
	function save_post( $content_array, $headers, $type ) {

		//Prepare New Content
		shuffle( $content_array );
		$content = implode( '', $content_array );

		$header = array_rand( $headers, 1 );

		$time = self::_generate_time();

		// Create post object
		$new_content = array(
			'post_title'    => $headers[$header],
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => $type,
			'post_date'     => $time['local_date'],
			'post_date_gmt' => $time['gmt_date'],
		);

		// Insert the post into the database
		$post_id = wp_insert_post( $new_content );
		update_post_meta( $post_id, 'sljdc', true );

		return $post_id;

	}


	/**
	 * Create Media Attachment and add Featured Image to Posts
	 *
	 * Mad props to http://www.wpexplorer.com/wordpress-featured-image-url/
	 *
	 * @since  1.0
	 * @param  int  $post_id  Post ID of newly-created Post
	 */
	function add_featured_image( $post_id ) {

		/**
		 * Cannot make file_filter a separate Class Method because array_filter()
		 * cannot pass the $string variable to an outside function.
		 */
		if ( ! function_exists( 'file_filter' ) ) {
			function file_filter($string) {
				return ( ! strpos($string, '.jpg') === false );
			}
		}

		// Count number of images in Plugin's image folder
		$count = 0;
		$base  = dirname( __FILE__ );
		$dir   = $base . '/images';
		$files = scandir( $dir );
		$files = array_filter( $files, 'file_filter' );
		$count = count( $files );
		$num   = rand( 1, $count );

		//Select an image
		$image_url  = plugins_url('images/' . $num . '.jpg', __FILE__);
		$upload_dir = wp_upload_dir(); // Set upload folder
		$image_data = file_get_contents($image_url); // Get image data
		$filename   = basename($image_url); // Create image file name

		// Check folder permission and define file location
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		file_put_contents( $file, $image_data );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		// Set attachment data
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		// Include image.php
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );
		update_post_meta( $attach_id, 'sljdc', true );

		// And finally assign featured image to post
		set_post_thumbnail( $post_id, $attach_id );

	}

	/**
	 * Create Random Comments
	 *
	 * @since  1.0
	 * @param  int  $post_id  Post ID of newly-created Post
	 */
	function add_comments( $post_id ) {

		// Create Secure Comments User
		if ( ! email_exists( 'sljdummycontent@gmail.com' ) ) {
			// Generate Secure Password
			$password = self::_generate_random( 'password', 35 );
			// Generate Secure Username
			$username = self::_generate_random( 'username', 10 );

			$user_args = array(
				'name'       => 'Samuel L. Jackson',
				'user_email' => 'sljdummycontent@gmail.com',
				'user_pass'  => $password,
				'user_login' => $username,
				'role'       => 'subscriber',
			);

			$user_id = wp_insert_user( $user_args );
			update_user_meta( $user_id, 'sljdc', true );

		} else {

			$user = get_user_by( 'email', 'sljdummycontent@gmail.com' );
			$user_id = $user->ID;

		}

		// Incliude input arrays
		include( 'input.php' );

		$comments_num = rand( 1, 10 );

		for ( $i = 1; $i <= $comments_num; $i++ ) {

			$time = self::_generate_time();

			$header = array_rand( $headers, 1 );

			$comment = $headers[$header];

			// Assemble Commment

			$args = array(
				'comment_post_ID'  => $post_id,
				'comment_content'  => $comment,
				'user_id'          => $user_id,
				'comment_date'     => $time['local_date'],
				'comment_approved' => 1,
			);

			wp_insert_comment( $args );

		}

	}

	/**
	 * Generate Times for Posts and Comments
	 *
	 * @since   1.0
	 * @return  array  $time  Random GMT date and associated Local Date
	 */
	private static function _generate_time() {

		// Generate random post dates within the last two weeks
		$time_rand = rand( 2, 336 );
		$time['gmt_date'] = date( 'Y-m-d H:i:s', strtotime( '-' . $time_rand . ' hours' ) );
		$time['local_date'] = get_date_from_gmt( $time['gmt_date'] );

		return $time;

	}

	/**
	 * Generate Random, Secure Password and Username for Created User
	 *
	 * @since   1.0
	 * @return  array  $time  Random GMT date and associated Local Date
	 */
	private static function _generate_random( $type = 'password', $length = 35 ) {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!@#$%^&*~";
		if ( $type = 'password' ) {
			$alphabet = "abcdefghijklmnopqrstuwxyz";
		}
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;

		for ( $i = 0; $i < $length; $i++ ) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}

		return implode( $pass );
	}

} // End Sljdc_Generator


/**
 * Delete Dummy Content
 *
 * @since 1.0
 */
add_action( 'wp_ajax_sljdc_delete', 'sljdc_delete' );
add_action( 'wp_ajax_nopriv_sljdc_delete', 'sljdc_delete' );

function sljdc_delete() {

	if ( ! wp_verify_nonce( $_POST['nonce'], 'sljdc_nonce' ) )
		die( 'Failed Nonce Verification' );

	//Get dummy content Posts & Pages
	$sljdc_posts   = get_posts( array( 'meta_key' => 'sljdc', 'posts_per_page' => -1 ) );
	$sljdc_pages   = get_pages( array( 'meta_key' => 'sljdc', 'posts_per_page' => -1 ) );
	$sljdc_posts   = is_array( $sljdc_posts ) ? $sljdc_posts : array( $sljdc_posts );
	$sljdc_pages   = is_array( $sljdc_pages ) ? $sljdc_pages : array( $sljdc_pages );
	$sljdc_content = array_merge( $sljdc_posts, $sljdc_pages );

	// Delete each dummy content Post/Page
	foreach ( $sljdc_content as $post ) {

		// Delete attachments (e.g., Featured Image) attached to each post.
		$post_attachments = get_children( array( 'post_parent' => $post->ID ) );
		if ( $post_attachments ) {
			foreach ( $post_attachments as $attachment ) {
				// Force Delete Attachment and bypass Trash
				wp_delete_attachment( $attachment->ID, true );
			}
		}

		// Delete Comments
		$comments = get_comments( array( 'post_id' => $post->ID ) );
		foreach ( $comments as $comment ) {
			// Force Delete Comment and bypass Trash
			wp_delete_comment( $comment->comment_ID, true );
		}

		// Force Delete Post/Page and bypass Trash.
		wp_delete_post( $post->ID, true );

	}

	/**
	 * Delete User created for Comments
	 * Only get Subscribers, so Admins don't accidentally get deleted
	 */
	$users = get_users( array( 'meta_key' => 'sljdc', 'role' => 'Subscriber' ) );
	foreach ( $users as $user ) {
			// Delete Generated User
			wp_delete_user( $user->ID );
	}

}