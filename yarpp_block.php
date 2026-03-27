<?php

/**
 * Plugin Name: List YARPP Block
 * Plugin URI: https://marc.tv/
 * Description: YARPP Block 
 * Version: 2.7.1
 * Author: Marc Tönsing
 * Author URI: https://toensing.com
 * Text Domain: list-yarpp-block
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



function register_list_yarpp_block()
{
  register_block_type( __DIR__ . '/build', [
    'render_callback' => 'render_list_yarpp_block'
  ]);

  wp_set_script_translations( 'yarpp-block-list-editor-script', 'list-yarpp-block' );

}

add_action( 'init', 'register_list_yarpp_block' );
add_action( 'clean_post_cache', 'list_yarpp_block_bump_cache_version' );
add_action( 'set_object_terms', 'list_yarpp_block_bump_cache_version' );


/**
 * Render block output 
 *
 */

function render_list_yarpp_block($attributes, $content)
{
  static $request_cache = array();
  $is_backend = list_yarpp_block_is_editor_preview();
  $cache_key = list_yarpp_block_get_cache_key( $attributes );

  if ( isset( $request_cache[ $cache_key ] ) ) {
    return $request_cache[ $cache_key ];
  }

  if ( ! $is_backend ) {
    $cached_block = get_transient( $cache_key );

    if ( is_array( $cached_block ) && array_key_exists( 'html', $cached_block ) ) {
      $request_cache[ $cache_key ] = (string) $cached_block['html'];

      return $request_cache[ $cache_key ];
    }
  }

  $block = getBlocks($attributes);
  $request_cache[ $cache_key ] = $block;

  if ( ! $is_backend ) {
    set_transient(
      $cache_key,
      array(
        'html' => $block,
      ),
      list_yarpp_block_get_cache_ttl( $attributes )
    );
  }

  return $block;
}

function list_yarpp_block_is_editor_preview()
{
  if ( ! defined( 'REST_REQUEST' ) || true !== REST_REQUEST ) {
    return false;
  }

  $context = isset( $_GET['context'] ) ? sanitize_text_field( wp_unslash( $_GET['context'] ) ) : '';

  return 'edit' === $context;
}

function list_yarpp_block_get_heading_html( $headline, $level, $alignclass )
{
  if ( '' === $headline ) {
    return '';
  }

  $class = '' === $alignclass ? '' : ' class="' . esc_attr( $alignclass ) . '"';
  $level = tag_escape( $level );

  return sprintf( '<%1$s%2$s>%3$s</%1$s>', $level, $class, esc_html( $headline ) );
}

function list_yarpp_block_get_context_post_id()
{
  global $post;

  $post_id = get_the_ID();

  if ( ! $post_id && $post instanceof \WP_Post ) {
    $post_id = $post->ID;
  }

  if ( ! $post_id && is_singular() ) {
    $post_id = get_queried_object_id();
  }

  return absint( $post_id );
}

function list_yarpp_block_get_cache_version()
{
  $version = get_option( 'list_yarpp_block_cache_version', '1' );

  return is_string( $version ) && '' !== $version ? $version : '1';
}

function list_yarpp_block_bump_cache_version()
{
  update_option( 'list_yarpp_block_cache_version', (string) microtime( true ), false );
}

function list_yarpp_block_get_cache_key( $attributes )
{
  $cache_data = array(
    'post_id'       => list_yarpp_block_get_context_post_id(),
    'attributes'    => $attributes,
    'cache_version' => list_yarpp_block_get_cache_version(),
  );

  return 'list_yarpp_block_' . md5( wp_json_encode( $cache_data ) );
}

function list_yarpp_block_get_cache_ttl( $attributes )
{
  $ttl = (int) apply_filters( 'list_yarpp_block_cache_ttl', HOUR_IN_SECONDS, $attributes );

  return $ttl > 0 ? $ttl : HOUR_IN_SECONDS;
}

function list_yarpp_block_prime_post_and_thumbnail_caches( $post_ids )
{
  if ( ! function_exists( '_prime_post_caches' ) ) {
    return;
  }

  $post_ids = array_values( array_unique( array_filter( array_map( 'absint', $post_ids ) ) ) );

  if ( empty( $post_ids ) ) {
    return;
  }

  _prime_post_caches( $post_ids, false, true );

  $thumbnail_ids = array();

  foreach ( $post_ids as $post_id ) {
    $thumbnail_id = get_post_thumbnail_id( $post_id );

    if ( $thumbnail_id ) {
      $thumbnail_ids[] = (int) $thumbnail_id;
    }
  }

  $thumbnail_ids = array_values( array_unique( $thumbnail_ids ) );

  if ( ! empty( $thumbnail_ids ) ) {
    _prime_post_caches( $thumbnail_ids, false, true );
  }
}

function getBlocks($attributes)
{

  $allowed_blocktypes = array( 'related', 'latest' );
  $allowed_alignments = array( 'wide', 'full', 'left', 'right', 'center' );
  $allowed_levels = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
  $imgsize = isset( $attributes['imgsize'] ) ? absint( $attributes['imgsize'] ) : 300;
  $blocktype = isset( $attributes['blocktype'] ) ? sanitize_key( $attributes['blocktype'] ) : 'related';
  $align = isset( $attributes['align'] ) ? sanitize_key( $attributes['align'] ) : '';
  $headline = isset( $attributes['headline'] ) ? sanitize_text_field( $attributes['headline'] ) : '';
  $level = isset( $attributes['level'] ) ? sanitize_key( $attributes['level'] ) : 'h3';
  $item_attributes = array(
    'targetblank' => ! empty( $attributes['targetblank'] ),
    'imgsize'     => $imgsize > 0 ? $imgsize : 300,
  );

  $alignclass = '';
  $cpid = list_yarpp_block_get_context_post_id();
  $excludes = $cpid ? array( $cpid ) : array();
  $related_posts_array = array();
  $posts = array();
  $html = '';
  $is_backend = list_yarpp_block_is_editor_preview();

  if ( ! in_array( $blocktype, $allowed_blocktypes, true ) ) {
    $blocktype = 'related';
  }

  if ( ! in_array( $level, $allowed_levels, true ) ) {
    $level = 'h3';
  }

  if ( in_array( $align, $allowed_alignments, true ) ) {
    $alignclass = 'align' . $align;
  }

  $heading = list_yarpp_block_get_heading_html( $headline, $level, $alignclass );

  /* Is YARPP installed?  */
  if ( function_exists( 'yarpp_get_related' ) ) {
    $posts = yarpp_get_related( array('limit' => 3), $cpid );
  } elseif ( 'related' === $blocktype ) {
    if( $is_backend ){
      return '<p class="notice">' . __('YARPP plugin is not installed and activated. ', 'list-yarpp-block') . '</p>';
    } else {
      return '';
    }
  }

  if ( ! empty( $posts ) && is_array( $posts ) ) {
    foreach ( $posts as $post ) {
      if ( isset( $post->ID ) ) {
        $related_posts_array[] = (int) $post->ID;
      }
    }
  }

  if ( ! empty( $related_posts_array ) ) {
    list_yarpp_block_prime_post_and_thumbnail_caches( $related_posts_array );
  }

  /* Enough posts available?  */
  if ( 'related' === $blocktype ) {
    if ( count( $related_posts_array ) < 3 ) {
      if( $is_backend ){
        return '<p class="notice">' . __('Less than 3 related posts found.', 'list-yarpp-block') . '</p>';
      } else {
        return '';
      }
    }

    $html = $heading;
    $html .= '<ul class="' . esc_attr( trim( $alignclass . ' wp-block-latest-posts__list is-grid columns-3 wp-block-latest-posts' ) ) . '">';

    foreach ( $related_posts_array as $related_post_id ) {
      $html .= render_listitem( $related_post_id, $item_attributes, $is_backend );
    }

    $html .= '</ul>';

    return $html;
  }

  $excludes = array_merge($excludes, $related_posts_array);

  $args = array(
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'posts_per_page'         => 3,
    'post__not_in'           => $excludes,
    'ignore_sticky_posts'    => true,
    'meta_key'               => '_thumbnail_id',
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'fields'                 => 'ids',
    'no_found_rows'          => true,
    'update_post_term_cache' => false,
    'suppress_filters'       => false,
  );

  $latest_post_ids = get_posts( $args );

  if ( ! empty( $latest_post_ids ) ) {
    list_yarpp_block_prime_post_and_thumbnail_caches( $latest_post_ids );
  }

  $html = $heading;
  $html .= '<ul class="' . esc_attr( trim( $alignclass . ' wp-block-latest-posts__list is-grid columns-3 wp-block-latest-posts' ) ) . '">';

  foreach ( $latest_post_ids as $latest_post_id ) {
    $html .= render_listitem( $latest_post_id, $item_attributes, $is_backend );
  }

  $html .= '</ul>';

  if ($blocktype == 'latest') {
    return $html;
  }
}

function render_listitem($pid, $attributes, $is_backend = false)
{
  $html = '<li>';
  $params = '';
  $url = get_the_permalink( $pid );

  if ( ! empty( $attributes['targetblank'] ) ) {
    $params = ' target="_blank" rel="noopener"';
  }

  $title = get_the_title($pid);
  $imgsize = isset( $attributes['imgsize'] ) ? absint( $attributes['imgsize'] ) : 300;
  $imgsize = $imgsize > 0 ? $imgsize : 300;
  $img = get_the_post_thumbnail( $pid, array( $imgsize, 0 ) );
  if ( $is_backend ) {
    $html .= '<div class="wp-block-latest-posts__featured-image">' . $img . '</div>';
    $html .= '<span>' . esc_html( $title ) . '</span>';
  } else {
    $html .= '<div class="wp-block-latest-posts__featured-image"><a href="' . esc_url( $url ) . '"' . $params . '>' . $img . '</a></div>';
    $html .= '<a href="' . esc_url( $url ) . '"' . $params . '>' . esc_html( $title ) . '</a>';
  }
  $html .= '</li>';

  return $html;
}
