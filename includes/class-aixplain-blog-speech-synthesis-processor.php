<?php


/**
 * The core function class for processing posts.
 *
 *
 * @since      1.0.0
 * @package    Aixplain_Blog_Speech_Synthesis
 * @subpackage Aixplain_Blog_Speech_Synthesis/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class Aixplain_Blog_Speech_Synthesis_Processor {

	/**
	 * The core function class for processing posts.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $attachmentMetaKey = 'aixplain_bbs_synthesized';
	protected $statusMetaKey = 'aixplain_bbs_synthesized_attachment';
	protected const STATUS_ENABLED = 1;
	protected const STATUS_DISABLED = 2;

	public function __construct() {


	}

	public function getSynthesisedPostsCount() {
		return aixplain_bss_count_posts_has_meta( $this->attachmentMetaKey );

	}

	public function getPostAudioLink( $postId ) {
		$attachmentId = get_post_meta( $postId, $this->attachmentMetaKey );

		if ( ! $attachmentId ) {
			return null;
		}
		$url = wp_get_attachment_url( $attachmentId[0] );

		return $url;

	}

	/**
	 * @return bool
	 */
	public function synthesizeNextPost() {
		$processed = false;
		$offset    = 0;
		while ( ! $processed ) {
			$posts = aixplain_bss_get_next_post_has_no_meta( $this->attachmentMetaKey, $offset );
			if ( count( $posts ) ) {
				try {
					$processed = $this->synthesizePost( $posts[0] );
					$offset ++;
				} catch ( Exception $e ) {
					wp_send_json_error( $e->getMessage(), $e->getCode() );
				}
			}
		}

		return true;

	}

	/**
	 * @param $post
	 * r
	 */
	public function synthesizePost( $post ) {
		set_time_limit( 600 * 10 );
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		$postContent = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$postContent = preg_replace( '#\[[^\]]+\]#', '', $postContent );
		$postContent = str_replace( '&nbsp;', ' ', $postContent );
		$postContent = html_entity_decode( $postContent, ENT_QUOTES | ENT_COMPAT, 'UTF-8' );
		$postContent = html_entity_decode( $postContent, ENT_HTML5, 'UTF-8' );
		$postContent = html_entity_decode( $postContent );
		if ( ! $postContent ) {
			return false;
		}
		$audioUrl = $this->callApiProvider( $postContent );
		if ( ! $audioUrl ) {
			return false;
		}
		//$audioUrl = "https://aixplain-texttoaudio.s3.amazonaws.com/617ea533ddac4400085ae4e7.wav";
		$attachmentId = $this->upload_from_url( $audioUrl, $post );

		if ( ! $attachmentId ) {
			throw new Exception( 'Could not upload file', 422 );
		}
		update_post_meta( $post->ID, $this->statusMetaKey, static::STATUS_ENABLED );
		update_post_meta( $post->ID, $this->attachmentMetaKey, $attachmentId );

		return true;

	}

	/**
	 * @param $post
	 * r
	 */
	public function unSynthesizePost( $post ) {
		set_time_limit( 600 * 10 );
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		$attachmentId = get_post_meta( $post->ID, $this->attachmentMetaKey );
		print_R( $attachmentId );
		if ( is_array( $attachmentId ) && count( $attachmentId ) ) {
			$attachmentId = $attachmentId[0];
		}
		wp_delete_post( $attachmentId[0], true );

		return true;

	}

	/**
	 * @param $transcript
	 *
	 * @return audioUrl
	 */
	public function callApiProvider( $transcript ) {

		$res = wp_remote_post( get_option( 'aixplain_bss_api_url' ), [
			'body'    => json_encode( [ 'data' => $transcript ] ),
			'headers' => [
				'x-api-key'    => get_option( 'aixplain_bss_api_key' ),
				'Content-Type' => 'application/json'
			]
		] );
		if ( $res['response']['code'] >= 200 && $res['response']['code'] < 299 ) {
			$res = json_decode( $res['body'], 1 );

			$pollingURL = isset( $res['data'] ) ? $res['data'] : $res['url'];
			while ( true ) {

				$pollingRes = wp_remote_get( $pollingURL, [
					'headers' => [
						'x-api-key'    => get_option( 'aixplain_bss_api_key' ),
						'Content-Type' => 'application/json'
					]
				] );
				if ( $pollingRes['response']['code'] >= 200 && $pollingRes['response']['code'] < 299 ) {
					$parsedPollingRes = json_decode( $pollingRes['body'], 1 );
					if ( $parsedPollingRes['completed'] === true ) {
						//We got the audio at $pollingRes['data']
						return $parsedPollingRes['data'];
					} else {
						sleep( 3 );
					}
				} else {
					$error             = $pollingRes['body'];
					$decodedPollingRes = json_decode( $pollingRes['body'], 1 );
					if ( $decodedPollingRes ) {
						$error = $decodedPollingRes['error'] ?? '';
						if ( $decodedPollingRes['supplierError'] ?? false ) {
							$error .= ', ' . $decodedPollingRes['supplierError'];
						}
					}

					return false;
					throw new Exception( $error, 422 );
				}
			}

			return $response->body;
		} else {
			$decodedRes = json_decode( $res['body'], 1 );
			throw new Exception( $decodedRes ? $decodedRes['message'] : $res['body'], 422 );
		}
	}


	private function upload_from_url( $fileUrl, WP_Post $post ) {
		if ( ! function_exists( 'download_url' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/media.php';
			include_once ABSPATH . 'wp-admin/includes/image.php';

		}
		// Save as a temporary file
		$tmp = download_url( $fileUrl );

		// Check for download errors
		if ( is_wp_error( $tmp ) ) {
			return null;
		}

		// Get the file extension for the
		$fileextension = '.' . array_reverse( explode( '.', basename( $fileUrl ) ) )[0];
		//  base name:
		$name = 'Speech Synthesis for Post #' . $post->ID . $fileextension;

//		// Take care of  files without extension:
//		$path = pathinfo( $tmp );
//		if ( ! isset( $path['extension'] ) ):
//			$tmpnew = $tmp . '.tmp';
//			if ( ! rename( $tmp, $tmpnew ) ):
//				return '';
//			else:
//				$ext  = pathinfo( $fileUrl, PATHINFO_EXTENSION );
//				$name = $post->post_name . $fileextension;
//				$tmp  = $tmpnew;
//			endif;
//		endif;

		// Upload the  into the WordPress Media Library:
		$file_array = array(
			'name'     => $name,
			'tmp_name' => $tmp
		);
		$id         = media_handle_sideload( $file_array, $post->ID );


		// Check for handle sideload errors:
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return null;
		}

		// Get the attachment url:
		//wp_get_attachment_url( $id );

		return $id;
	}


	public function markPostAsUnSynthesisedFromAttachment( $attachmentId ) {
		$post = aixplain_bss_get_next_post_has_meta( $this->attachmentMetaKey, $attachmentId );
		if ( $post ) {
			$this->_unsetPostMeta( $post, $attachmentId );
		}
	}

	private function _unsetPostMeta( $post, $attachmentId ) {
		delete_post_meta( $post->ID, $this->attachmentMetaKey, $attachmentId );
		delete_post_meta( $post->ID, $this->statusMetaKey );
	}

}
