<?php

namespace MyClub\MyClubGroups;

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
     * @param int $postId The ID of the post to add the featured image to.
     * @param object|null $image The image information object. Should contain 'raw' property with 'url' property.
     * @param string $prefix Optional. The prefix to be added to the image URL before adding it to the database. Default is an empty string.
     *
     * @return void
     * @since 1.0.0
     */
    static function addFeaturedImage( int $postId, $image, string $prefix = '' )
    {
        $attachmentId = null;

        if ( isset( $image ) ) {
            $attachment = Utils::addImage( $image->raw->url, $prefix );
            if ( isset( $attachment ) ) {
                $attachmentId = $attachment[ 'id' ];
            }
        }

        if ( $attachmentId !== null && ( (int)get_post_meta( $postId, '_thumbnail_id', true ) ) !== $attachmentId ) {
            set_post_thumbnail( $postId, $attachmentId );
        }
    }

    /**
     * Add an image to the WordPress media library.
     *
     * @param string $imageUrl The URL of the image to add.
     * @param string $prefix Optional. Prefix to be added to the image filename. Default is an empty string.
     *
     * @return array|null The attachment information of the attachment or null
     *
     * @since 1.0.0
     */
    static function addImage( string $imageUrl, string $prefix = '' )
    {
        $attachmentId = null;
        $image = pathinfo( $imageUrl );

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

        $queryResults = new WP_Query( $args );

        if ( !isset( $queryResults->posts, $queryResults->posts[ 0 ] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );

            $file = [
                'name'     => $filename,
                'tmp_name' => download_url( $imageUrl )
            ];

            if ( !is_wp_error( $file[ 'tmp_name' ] ) ) {
                $attachmentId = media_handle_sideload( $file );

                if ( is_wp_error( $attachmentId ) ) {
                    @unlink( $file[ 'tmp_name' ] );
                    $attachmentId = null;
                }
            }
        } else {
            $attachmentId = $queryResults->posts[ 0 ]->ID;
        }

        if ( $attachmentId !== null ) {
            $imageUrl = null;
            $imageSrcArray = wp_get_attachment_image_src( $attachmentId, 'medium' );

            if ( $imageSrcArray ) {
                $imageUrl = $imageSrcArray[ 0 ];
            }

            return [
                'id' => $attachmentId,
                'url' => $imageUrl
            ];
        } else {
            return $attachmentId;
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
    static function changeHostName( string $oldUrl ): string
    {
        $hostUrlParts = parse_url( home_url() );
        $oldUrlParts = parse_url( $oldUrl );

        $scheme = isset( $hostUrlParts[ 'scheme' ] ) ? $hostUrlParts[ 'scheme' ] . '://' : '';
        $host = $hostUrlParts[ 'host' ];

        $port = isset( $oldUrlParts[ 'port' ] ) ? ':' . $oldUrlParts[ 'port' ] : '';
        $path = isset( $oldUrlParts[ 'path' ] ) ? $oldUrlParts[ 'path' ] : '';
        $query = isset( $oldUrlParts[ 'query' ] ) ? '?' . $oldUrlParts[ 'query' ] : '';

        return $scheme . $host . $port . $path . $query;
    }

    /**
     * Delete a post and related attachments and metadata from the WordPress database.
     *
     * @param int $postId The ID of the post to delete.
     *
     * @return void
     * @since 1.0.0
     */
    static function deletePost( int $postId )
    {
        if ( has_post_thumbnail( $postId ) ) {
            $attachmentId = get_post_thumbnail_id( $postId );
            delete_post_thumbnail( $postId );
            wp_delete_attachment( $attachmentId, true );
        }

        $meta = get_post_meta( $postId, 'members', true );

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

        wp_delete_post( $postId, true );
    }

    /**
     * Formats a given UTC time to the format specified in WordPress options.
     *
     * @param string|int $utcTime The UTC time to format.
     *
     * @return string The formatted date/time string.
     * @since 1.0.0
     */
    static function formatDateTime( $utcTime ): string
    {
        try {
            // Retrieve the timezone string from WordPress options
            $wpTimezone = get_option( 'timezone_string' );

            // Create DateTimeZone object for WordPress timezone
            $timezone = new DateTimeZone( $wpTimezone );

            // Create DateTime object for last sync, correct it to WordPress timezone
            $dateTimeUtc = new DateTime( $utcTime, new DateTimeZone( 'UTC' ) );
            $dateTimeUtc->setTimezone( $timezone );

            // Format the date/time string according to your requirements
            $formattedTime = $dateTimeUtc->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

        } catch ( Exception $e ) {
            $formattedTime = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $utcTime );
        }

        return $formattedTime;
    }

    /**
     * Update or create an option in the WordPress database.
     *
     * @param string $optionName The name of the option to update or create.
     * @param mixed $value The value to update or create the option with.
     * @param string $autoload Optional. Whether to autoload the option. Default is 'yes'.
     *
     * @return void
     * @since 1.0.0
     */
    static function updateOrCreateOption( string $optionName, $value, string $autoload = 'yes' )
    {
        if ( get_option( $optionName, 'non-existent' ) === 'non-existent' ) {
            add_option( $optionName, $value, '', $autoload );
        } else {
            update_option( $optionName, $value );
        }
    }
}