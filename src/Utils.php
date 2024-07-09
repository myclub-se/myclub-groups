<?php

namespace MyClub\MyClubGroups;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use DateTime;
use DateTimeZone;
use Exception;
use WP_Query;

/**
 * Utility class that provides various helper methods.
 *
 */
class Utils
{
    /**
     * Add a featured image to a post in the WordPress database.
     *
     * @param int $post_id The ID of the post to add the featured image to.
     * @param object|null $image The image information object. Should contain 'raw' property with 'url' property.
     * @param string $prefix Optional. The prefix to be added to the image URL before adding it to the database. Default is an empty string.
     *
     * @return void
     * @since 1.0.0
     */
    static function add_featured_image( int $post_id, $image, string $prefix = '' )
    {
        $attachment_id = null;

        if ( isset( $image ) ) {
            $attachment = Utils::add_image( $image->raw->url, $prefix );
            if ( isset( $attachment ) ) {
                $attachment_id = $attachment[ 'id' ];
            }
        }

        if ( $attachment_id !== null && ( (int)get_post_meta( $post_id, '_thumbnail_id', true ) ) !== $attachment_id ) {
            set_post_thumbnail( $post_id, $attachment_id );
        }
    }

    /**
     * Add an image to the WordPress media library.
     *
     * @param string $image_url The URL of the image to add.
     * @param string $prefix Optional. Prefix to be added to the image filename. Default is an empty string.
     *
     * @return array|null The attachment information of the attachment or null
     *
     * @since 1.0.0
     */
    static function add_image( string $image_url, string $prefix = '' ): ?array
    {
        $attachment_id = null;
        $image = pathinfo( $image_url );

        $name = sanitize_title( $prefix . urldecode( $image[ 'filename' ] ) );
        $filename = $name;
        if ( array_key_exists( 'extension', $image ) ) {
            $filename .= '.' . $image[ 'extension' ];
        }

        $args = array (
            'posts_per_page' => 1,
            'post_type'      => 'attachment',
            'name'           => $name
        );

        $query_results = new WP_Query( $args );

        if ( !isset( $query_results->posts, $query_results->posts[ 0 ] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );

            $file = [
                'name'     => $filename,
                'tmp_name' => download_url( $image_url )
            ];

            if ( !is_wp_error( $file[ 'tmp_name' ] ) ) {
                $attachment_id = media_handle_sideload( $file );

                if ( is_wp_error( $attachment_id ) ) {
                    wp_delete_file( $file[ 'tmp_name' ] );
                    $attachment_id = null;
                }
            }
        } else {
            $attachment_id = $query_results->posts[ 0 ]->ID;
        }

        if ( $attachment_id !== null ) {
            $image_url = null;
            $image_src_array = wp_get_attachment_image_src( $attachment_id, 'medium' );

            if ( $image_src_array ) {
                $image_url = $image_src_array[ 0 ];
            }

            return [
                'id' => $attachment_id,
                'url' => $image_url
            ];
        } else {
            return $attachment_id;
        }
    }

    /**
     * Change the host name in a given URL to match the host name of the WordPress site.
     *
     * @param string $oldUrl The URL with the host name to be changed.
     *
     * @return string The modified URL with the updated host name.
     *
     * @since 1.0.0
     */
    static function change_host_name( string $oldUrl ): string
    {
        $host_url_parts = wp_parse_url( home_url() );
        $old_url_parts = wp_parse_url( $oldUrl );

        $scheme = isset( $host_url_parts[ 'scheme' ] ) ? $host_url_parts[ 'scheme' ] . '://' : '';
        $host = $host_url_parts[ 'host' ];

        $port = isset( $old_url_parts[ 'port' ] ) ? ':' . $old_url_parts[ 'port' ] : '';
        $path = isset( $old_url_parts[ 'path' ] ) ? $old_url_parts[ 'path' ] : '';
        $query = isset( $old_url_parts[ 'query' ] ) ? '?' . $old_url_parts[ 'query' ] : '';

        return $scheme . $host . $port . $path . $query;
    }

    /**
     * Delete a post and related attachments and metadata from the WordPress database.
     *
     * @param int $post_id The ID of the post to delete.
     *
     * @return void
     * @since 1.0.0
     */
    static function delete_post( int $post_id )
    {
        if ( has_post_thumbnail( $post_id ) ) {
            $attachment_id = get_post_thumbnail_id( $post_id );
            delete_post_thumbnail( $post_id );
            wp_delete_attachment( $attachment_id, true );
        }

        $meta = get_post_meta( $post_id, 'myclub_groups_members', true );

        if ( $meta ) {
            $members = json_decode( $meta );
            foreach ( $members->members as $member ) {
                if ( property_exists($member, 'member_image') && $member->member_image->id ) {
                    wp_delete_attachment( $member->member_image->id );
                }
            }

            foreach ( $members->leaders as $leader ) {
                if ( property_exists($leader, 'member_image') && $leader->member_image->id ) {
                    wp_delete_attachment( $leader->member_image->id );
                }
            }
        }

        wp_delete_post( $post_id, true );
    }

    /**
     * Formats a given UTC time to the format specified in WordPress options.
     *
     * @param string|int $utc_time The UTC time to format.
     *
     * @return string The formatted date/time string.
     * @since 1.0.0
     */
    static function format_date_time( $utc_time ): string
    {
        try {
            // Retrieve the timezone string from WordPress options
            $timezone_string = wp_timezone_string();
            if ( !$timezone_string ) {
                $timezone_string = 'Europe/Stockholm';
            }
            $timezone = new DateTimeZone( $timezone_string );

            // Create DateTime object for last sync, correct it to WordPress timezone
            $date_time_utc = new DateTime( $utc_time, new DateTimeZone( 'UTC' ) );
            $date_time_utc->setTimezone( $timezone );

            // Format the date/time string according to your requirements
            $formatted_time = $date_time_utc->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

        } catch ( Exception $e ) {
            $formatted_time = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $utc_time );
        }

        return $formatted_time;
    }

    static function sanitize_array( array $array ): array
    {
        foreach( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = Utils::sanitize_array( $value );
            } else {
                $value = sanitize_text_field( $value );
            }
        }

        return $array;
    }

    /**
     * Update or create an option in the WordPress database.
     *
     * @param string $option_name The name of the option to update or create.
     * @param mixed $value The value to update or create the option with.
     * @param string $autoload Optional. Whether to autoload the option. Default is 'yes'.
     *
     * @return void
     * @since 1.0.0
     */
    static function update_or_create_option( string $option_name, $value, string $autoload = 'yes' )
    {
        if ( get_option( $option_name, 'non-existent' ) === 'non-existent' ) {
            add_option( $option_name, $value, '', $autoload );
        } else {
            update_option( $option_name, $value, $autoload );
        }
    }
}