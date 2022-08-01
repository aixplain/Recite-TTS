<?php


/**
 * The core function class for processing posts.
 *
 *
 * @since      1.0.0
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/includes
 * @author     aiXplain <anas.bakro@aixplain.com>
 */
class AiXplain_Recite_TTS_Core {

	/**
	 * The core function class for processing posts.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $attachmentMetaKey = 'aixplain_bbs_synthesized';
	protected $statusMetaKey = 'aixplain_bbs_synthesized_attachment';
	protected $errorMetaKey = 'aixplain_bbs_synthesize_error';
	const BACKGROUND_JOB_HOOK = 'aixplain_bbs_process_all_posts';
	const STOP_FLAG = 'aixplain_bbs_stop_flag';
	protected const STATUS_ENABLED = 1;
	protected const STATUS_DISABLED = 2;
	protected const STATUS_HAS_API_ERROR = 3;
	static $debugMode = true;

	public function __construct() {

	}

	/**
	 * @return mixed
	 */
	public function getSynthesisedPostsCount() {
		return aixplain_bss_count_posts_has_meta( $this->attachmentMetaKey );
	}

	public function getNoneSynthesizablePostsCount() {
		return aixplain_bss_count_posts_has_meta( $this->statusMetaKey, self::STATUS_DISABLED );
	}

	public function getPostsHasApiErrorCount() {
		return aixplain_bss_count_posts_has_meta( $this->statusMetaKey, self::STATUS_HAS_API_ERROR );
	}

	/**
	 * @param $postId
	 *
	 * @return false|string|null
	 */
	public function getPostAudioLink( $postId ) {
		$attachmentId = get_post_meta( $postId, $this->attachmentMetaKey );

		if ( ! $attachmentId ) {
			return null;
		}
		$url = wp_get_attachment_url( $attachmentId[0] );

		return $url;
	}

	/**
	 * @param $postId
	 *
	 * @return false|string|null
	 */
	public function getPostErrorMessage( $postId ) {
		$errorMessage = get_post_meta( $postId, $this->errorMetaKey );
		if ( ! $errorMessage || !count( $errorMessage ) ) {
			return "";
		}

		return $errorMessage[0];
	}

	/**
	 *
	 */
	public function startSynthesizeAllPostsJob() {
		//Get Action ID:
		$isAlreadyRunning = $this->isSynthesizeAllPostsJobRunning();
		if ( $isAlreadyRunning ) {
			return true;
		}

		$actionId = as_enqueue_async_action( self::BACKGROUND_JOB_HOOK, [], '', true );

		return true;

	}

	/**
	 * @return bool
	 */
	public function stopSynthesizeAllPostsJob() {
		//Below doesn't work as once the job started
		as_unschedule_all_actions( self::BACKGROUND_JOB_HOOK );
		//So had to add a flag
		update_option( self::STOP_FLAG, true, false );
		$isRunning = true;
		while ( $isRunning ) {

			$isRunning = $this->isSynthesizeAllPostsJobRunning();
			if ( $isRunning ) {
				sleep( 1 );
			}else{
				update_option( self::STOP_FLAG, false, false );
			}
		}

		return $isRunning;
	}

	/**
	 *
	 */
	public function isSynthesizeAllPostsJobRunning() {
		return as_has_scheduled_action( self::BACKGROUND_JOB_HOOK );
	}

	/**
	 * @return bool
	 */
	public function synthesizeNextPost() {
		$processed = false;
		$offset    = 0;
		//Get next post which hasn't been synthesized
		$posts = aixplain_bss_get_next_post( [
			[
				'key'     => $this->statusMetaKey,
				'compare' => 'NOT EXISTS'
			],
			[
				'key'     => $this->attachmentMetaKey,
				'compare' => 'NOT EXISTS'
			],
		], $offset );
		if ( count( $posts ) ) {
			try {
				$this->synthesizePost( $posts[0] );
			} catch ( Exception $e ) {
				AiXplain_Recite_TTS_Core::log( 'synthesizeNextPost: ' . $e->getMessage());
				//wp_send_json_error( $e->getMessage(), $e->getCode() );
			}
		}

		return true;

	}

	/**
	 * @param $post
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function synthesizePost( $post ) {
		set_time_limit( 600 * 10 );
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		//Strip html and quotes
		$postContent = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$postContent = preg_replace( '#\[[^\]]+\]#', '', $postContent );
		$postContent = str_replace( '&nbsp;', ' ', $postContent );
		$postContent = html_entity_decode( $postContent, ENT_QUOTES | ENT_COMPAT, 'UTF-8' );
		$postContent = html_entity_decode( $postContent, ENT_HTML5, 'UTF-8' );
		$postContent = html_entity_decode( $postContent );
		$postContent = trim( $postContent );
		if ( ! $postContent ) {
			//Since there is no content then mark at as not synthesizable
			$this->_setPostAsNoneSynthesizable( $post, "empty_content" );
			return "Post has no content";
		}
		//Cal aiXplain platform to try to synthesize content
		$apiProviderResponse = $this->callApiProvider( $postContent );
		AiXplain_Recite_TTS_Core::log( json_encode($apiProviderResponse));
		if ( is_array($apiProviderResponse) && isset($apiProviderResponse["error"]) ) {
			//If no content for some reason then store the error
			AiXplain_Recite_TTS_Core::log("synthesizePost: logging api error - ".($apiProviderResponse["error"]??"") );
			$this->_setPostAsHasApiError( $post, $apiProviderResponse["error"] );
			return "Api Error - ".($apiProviderResponse["error"]??"");
		}else if (!is_array($apiProviderResponse)) {
			$this->_setPostAsHasApiError( $post, $apiProviderResponse );
			AiXplain_Recite_TTS_Core::log("synthesizePost: logging api error - other");
			return "Api Error - other";
		}
		AiXplain_Recite_TTS_Core::log("synthesizePost: passed error");
		$attachmentId = $this->_upload_from_url( $apiProviderResponse["audio_url"], $post );

		if ( ! $attachmentId ) {
			AiXplain_Recite_TTS_Core::log("synthesizePost: couldn't upload");
			$this->_setPostAsHasApiError( $post, "couldn't upload ".$apiProviderResponse["audio_url"] );
			return "file couldn't be uploaded";
		}
		$this->_linkPostToAttachmentMeta( $post, $attachmentId );

		return false;

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
		if ( is_array( $attachmentId ) && count( $attachmentId ) ) {
			$attachmentId = $attachmentId[0];
		}
		wp_delete_post( $attachmentId, true );

		return true;

	}

	/**
	 * @param $transcript
	 *
	 * @return array
	 */
	public function callApiProvider( $transcript ) {
		try {
			$res = wp_remote_post( get_option( 'aixplain_bss_api_url' ), [
				'body'    => json_encode( [ 'data' => $transcript ] ),
				'timeout' => 60 * 5,
				'headers' => [
					'x-api-key'    => get_option( 'aixplain_bss_api_key' ),
					'Content-Type' => 'application/json',
				]
			] );
			if($res->errors && count($res->errors))
				return [ "error" => json_encode($res->errors) ];
			if ( $res['response']['code'] >= 200 && $res['response']['code'] < 299 ) {
				$res = json_decode( $res['body'], 1 );
				$pollingURL = isset( $res['data'] ) ? $res['data'] : $res['url'];
				while ( true ) {
					$pollingRes = wp_remote_get( $pollingURL, [
						'headers' => [
							'x-api-key'    => get_option( 'aixplain_bss_api_key' ),
							'Content-Type' => 'application/json',
							'timeout' => 60 * 5,
						]
					] );
					if($pollingRes && count($pollingRes->errors))
						return [ "error" => json_encode($pollingRes->errors && count($pollingRes->errors)) ];
					if ( $pollingRes['response']['code'] >= 200 && $pollingRes['response']['code'] < 299 ) {
						$parsedPollingRes = json_decode( $pollingRes['body'], 1 );
						if ( $parsedPollingRes['completed'] === true ) {
							//We got the audio at $pollingRes['data']
							return [ "audio_url" => $parsedPollingRes['data'] ];
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

						return [ "error" => $error ];
						//throw new Exception( $error, 422 );
					}
				}
			} else {
				$decodedRes = json_decode( $res['body'], 1 );
				return [ "error" => $decodedRes ? $decodedRes['message'] : $res['body'] ];
				//throw new Exception( $decodedRes ? $decodedRes['message'] : $res['body'], 422 );
			}
		} catch ( Exception $e ) {
			return [ "error" => json_encode($e->getMessage()) ];
		}
	}

	/**
	 * @param $fileUrl
	 * @param WP_Post $post
	 *
	 * @return int|WP_Error|null
	 */
	private function _upload_from_url( $fileUrl, WP_Post $post ) {
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

	/**
	 * @param
	 */
	public function markPostsHaveApiErrorAsUnSynthesised() {
		$post = aixplain_bss_get_next_post_has_meta( $this->statusMetaKey, self::STATUS_HAS_API_ERROR );
		while ( $post ) {
			$this->_unsetPostMeta( $post );
			$post = aixplain_bss_get_next_post_has_meta( $this->statusMetaKey, self::STATUS_HAS_API_ERROR );
		}

	}

	/**
	 * @param $attachmentId
	 */
	public function markPostAsUnSynthesisedFromAttachment( $attachmentId ) {
		$post = aixplain_bss_get_next_post_has_meta( $this->attachmentMetaKey, $attachmentId );
		if ( $post ) {
			$this->_unsetPostMeta( $post, $attachmentId );
		}
	}

	/**
	 * @param $post
	 * @param $attachmentId
	 */
	private function _unsetPostMeta( $post, $attachmentId = null ) {
		if ( $attachmentId ) {
			delete_post_meta( $post->ID, $this->attachmentMetaKey, $attachmentId );
		}
		delete_post_meta( $post->ID, $this->statusMetaKey );
		delete_post_meta( $post->ID, $this->errorMetaKey );
	}

	/**
	 * @param $post
	 * @param $attachmentId
	 */
	private function _linkPostToAttachmentMeta( $post, $attachmentId ) {
		update_post_meta( $post->ID, $this->statusMetaKey, static::STATUS_ENABLED );
		update_post_meta( $post->ID, $this->attachmentMetaKey, $attachmentId );
		delete_post_meta( $post->ID, $this->errorMetaKey );
	}

	/**
	 * @param $post
	 * @param $attachmentId
	 */
	private function _setPostAsNoneSynthesizable( $post, $error ) {
		delete_post_meta( $post->ID, $this->attachmentMetaKey );
		update_post_meta( $post->ID, $this->statusMetaKey, static::STATUS_DISABLED );
		update_post_meta( $post->ID, $this->errorMetaKey, $error );
	}

	/**
	 * @param $post
	 */
	private function _setPostAsHasApiError( $post, $error ) {
		delete_post_meta( $post->ID, $this->attachmentMetaKey );
		update_post_meta( $post->ID, $this->statusMetaKey, static::STATUS_HAS_API_ERROR );
		update_post_meta( $post->ID, $this->errorMetaKey, $error );
	}


	public static function log( $message ) {
		if ( static::$debugMode ) {
			error_log( $message );
		}
	}

}
