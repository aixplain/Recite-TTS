<?php


/**
 * Returns the where string for a metakey query
 *
 * @param string $key
 * @param null|string|array $value
 *
 * @return string
 */
function aixplain_bss_get_meta_where( $key, $value ) {
	$where = "meta_key = '$key'";

	if ( null !== $value ) {
		if ( \is_array( $value ) ) {
			\array_walk( $value, 'aixplain_bss_add_quote' );
			$in    = \implode( ',', $value );  // Seperate values with a comma
			$where .= " AND meta_value IN ($in)";
		} else {
			$where .= " AND meta_value = '$value'";
		}
	}

	return $where;
}

/**
 * Puts quotes around each value in an array when used as a callback function
 *
 * @param $value
 * @param $key
 */
function aixplain_bss_add_quote( &$value, $key ) {
	$value = "'$value'";
}


function aixplain_bss_get_next_post( $meta, $offset = 0 ) {
	$args = array(
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'offset'         => $offset,
		'meta_query'     => $meta
	);

	return query_posts( $args );
}

function aixplain_bss_get_next_post_has_meta( $meta, $value ) {
	$args = array(
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'orderby' => 'post_date',
		'order' => 'DESC',
		'post_type' => 'post',
		'meta_query'     => array(
			array(
				'key'   => $meta,
				'value' => $value // this should work...
			),
		)
	);
	$res  = query_posts( $args );

	return count( $res ) ? $res[0] : null;
}


function aixplain_bss_count_posts_has_meta( $meta,$value='' ) {
	global $wpdb;
	$sql = "SELECT count({$wpdb->posts}.ID) as count 
        FROM {$wpdb->posts} 
        INNER JOIN {$wpdb->postmeta} 
        ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) 
        WHERE {$wpdb->posts}.post_type IN ('post') 
        AND ({$wpdb->posts}.post_status = 'publish')
        AND ({$wpdb->postmeta}.meta_key = '{$meta}')
         ";
	if($value)
		$sql.=" AND ({$wpdb->postmeta}.meta_value = '{$value}')";

	//$prepared_sql = $wpdb->prepare( $sql );
	$res = $wpdb->get_results( $sql );

	return $res[0]->count;
}