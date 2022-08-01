<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://aixplain.com/
 * @since      1.0.0
 *
 * @package    AiXplain_Recite_TTS
 * @subpackage    AiXplain_Recite_TTS/admin/partials
 */
global $totalPosts, $totalSynthesisedPosts, $toBeProcessed, $notPossibleToProcess, $jobAlreadyRunning, $postsHasApiError, $stopFlag;


if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
if ( isset( $_REQUEST['aixplain_bss_api_key'] ) ) {
	update_option( 'aixplain_bss_api_key', $_REQUEST['aixplain_bss_api_key'] );
	update_option( 'aixplain_bss_api_url', $_REQUEST['aixplain_bss_api_url'] );
	update_option( 'aixplain_bss_placement', $_REQUEST['aixplain_bss_placement'] );
//	update_option( 'aixplain_bss_render', $_REQUEST['aixplain_bss_render'] );

}
?>

<div class="aixplain-bss ">
    <h1>
        <a href="https://aixplain.com" target="_blank">aiXplain</a> Recite TTS Settings
    </h1>
    <a href="https://aixplain.com" target="_blank" class="aixplain-bss-brand">
        <img src="<?=site_url()?>/wp-content/plugins/aixplain-recite-tts/admin/images/icon-large.png">
    </a>
    <div><small>Currently it supports posts only!</small></div>

    <div class="content">
        <div>
            <h2>Settings</h2>
            <form method="get">
                <input type="hidden" name="page" value="aixplain-bss"/>
                <div>
                    <label>API Key <i><small>should belong a text to speech function/pipeline like speech synthesis</small></i></label>
                    <input name="aixplain_bss_api_key" type="password"
                           value="<?= get_option( 'aixplain_bss_api_key' ) ?>"/>
                </div>
                <div>
                    <label>API URL</label>
                    <input name="aixplain_bss_api_url" value="<?= get_option( 'aixplain_bss_api_url' ) ?>"/>
                </div>
                <div>
                    <label>Placement in Single Post View</label>
                    <select name="aixplain_bss_placement" value="<?= get_option( 'aixplain_bss_placement' ) ?>">
                        <option <?= get_option( 'aixplain_bss_placement' )!=='none'?:'selected' ?> value="none">Hidden</option>
                        <option <?= get_option( 'aixplain_bss_placement' )!=='top'?:'selected' ?> value="top">Start</option>
                        <option <?= get_option( 'aixplain_bss_placement' )!=='bottom'?:'selected' ?> value="bottom">End</option>
                    </select>
                </div>
<!--                <div>-->
<!--                    <label>Render</label>-->
<!--                    <textarea name="aixplain_bss_render" rows=5>--><?//= get_option( 'aixplain_bss_render' ) ?><!--</textarea>-->
<!--                    <br/>-->
<!--                    <small><i>Variables: <code class="shortcode">$AUDIO_URL$</code></i></small>-->
<!--                </div>-->
                <div>
                    <button>Save</button>
                </div>
            </form>
        </div>
        <div class="">
            <h2>Short Codes</h2>
            <ul>
                <li>Post Audio HTML Element:-->
                    <br/>
                    <code class="shortcode" title="Click to copy to clipboard">[aixplain-blog-speech-synthesis element="audio-element"]</code>
                    <br/>
                    <i><small>Will show "Post has no audio for admins only" if the post has no audio</small></i>
                    <br/>
                    <!--<i><small>Can be configured through the template</small></i>-->

                </li>
                <li>Post Audio URL:
                    <br/>
                    <code class="shortcode" title="Click to copy to clipboard">[aixplain-blog-speech-synthesis element="audio-url"]</code>
                    <br/>
                    <i><small>Will show "Post has no audio" for admins only if the post has no audio</small></i>
                </li>

            </ul>
            <h2>Statistics</h2>
            <ul>
                <li>
                    <span id="aixplain-bss-total-blogs"><?= $totalPosts ?></span> Total Posts
                </li>
                <li>
                    <span id="aixplain-bss-not-possible-blogs"><?= $notPossibleToProcess ?></span> Total Posts - cannot be synthesized

                </li>
                <li>
                    <span id="aixplain-bss-blogs-has-api-error"><?= $postsHasApiError ?></span> Total Posts - has api error

                </li>
                <li>
                    <span id="aixplain-bss-synthesised-blogs"><?= $totalSynthesisedPosts ?></span> Total Synthesised
                    Posts
                </li>



                <li>
                    <span id="aixplain-bss-un-synthesised-blogs"><?= $toBeProcessed ?></span>
                    Total Un-synthesised Posts
                    <br/><br/>
                    <?php if ( $toBeProcessed > 0 || $stopFlag ): ?>
                        <button id="aixplain-bss-process-action" data-trigger-onload="<?=$stopFlag || $jobAlreadyRunning?>" data-is-stop="<?=$stopFlag?>">Process <?= $toBeProcessed ?>
                            Post(s)
                        </button>
					<?php endif ?>
                    <div id="aixplain-bss-response">
                    </div>
                </li>
	            <?php if ( $postsHasApiError > 0 ): ?>
                <li>
                        <hr>
                        <p>Click below to reset posts that have an api error to be able to synthesize them again</p>
                        <button id="aixplain-bss-reset-posts-has-api-errors-action">Reset</button>
                       <i>p.s. you can always force synthesize any post from "All Posts" listing </i>
                </li>
	            <?php endif ?>
            </ul>
        </div>
    </div>
	<?php
	if ( isset( $_REQUEST['aixplain_bss_api_key'] ) ) {
		echo '<br/><div class="updated saved-message">Saved Successfully</div><script>setTimeout(\'jQuery(".saved-message").fadeOut()\',4000)</script>';
	}

	?>
</div>