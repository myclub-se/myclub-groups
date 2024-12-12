<?php

namespace MyClub\MyClubGroups;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use DateTime;
use DateTimeZone;
use Exception;
use MyClub\MyClubGroups\Services\GroupService;
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
                'id'  => $attachment_id,
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
     * Clears the cache for a specific page or post based on the detected caching plugin.
     *
     * @param int $post_id The ID of the post or page whose cache needs to be cleared.
     *
     * @return bool True if the cache was successfully cleared, false if no supported caching plugin was detected or
     * an error occurred.
     */
    static function clear_cache_for_page( int $post_id ): bool
    {
        $cache_plugin = Utils::detect_cache_plugin();

        try {
            switch ( $cache_plugin ) {
                case 'breeze':
                    do_action( 'breeze_clear_post_cache', $post_id );
                    return true;

                case 'cache_enabler':
                    do_action( 'cache_enabler_clear_page_cache_by_post', $post_id );
                    return true;

                case 'hummingbird':
                    do_action( 'wphb_clear_post_cache', $post_id );
                    return true;

                case 'hyper_cache':
                    if ( function_exists( 'hyper_cache_clean_page' ) ) {
                        hyper_cache_clean_page( $post_id );
                    }
                    return true;

                case 'litespeed_cache':
                    do_action( 'litespeed_purge_post', $post_id );
                    return true;

                case 'siteground_optimizer':
                    if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
                        sg_cachepress_purge_cache( $post_id );
                    }
                    return true;

                case 'swift_performance':
                    if ( function_exists( 'swift_performance_cache_clean' ) ) {
                        swift_performance_cache_clean( $post_id );
                    }
                    return true;

                case 'wp_fastest_cache':
                    if ( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
                        wpfc_clear_post_cache_by_id( $post_id );
                    }
                    return true;

                case 'wp_optimize':
                    if ( function_exists( 'new_woocache_purge_enabled' ) ) {
                        new_woocache_purge_enabled( $post_id );
                    }
                    return true;

                case 'wp_rocket':
                    if ( function_exists( 'rocket_clean_post' ) ) {
                        rocket_clean_post( $post_id ); // Clean cache for this post
                    }
                    return true;

                case 'wp_super_cache':
                    if ( function_exists( 'wp_cache_post_change' ) ) {
                        wp_cache_post_change( $post_id );
                    }
                    return true;

                case 'w3_total_cache':
                    if ( function_exists( 'w3tc_flush_post' ) ) {
                        w3tc_flush_post( $post_id );
                    }
                    return true;

                case 'memcached_cache':
                case 'redis_cache':
                    if ( function_exists( 'wp_cache_delete' ) ) {
                        wp_cache_delete( $post_id );
                    }
                    return true;

                default:
                    // No caching plugin detected or not supported
                    return false;
            }
        } catch ( \Throwable $e ) {
            error_log( 'Exception caught in clear_cache_for_page: ' . $e->getMessage() );
            return false;
        }
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
                if ( property_exists( $member, 'member_image' ) && $member->member_image->id ) {
                    wp_delete_attachment( $member->member_image->id );
                }
            }

            foreach ( $members->leaders as $leader ) {
                if ( property_exists( $leader, 'member_image' ) && $leader->member_image->id ) {
                    wp_delete_attachment( $leader->member_image->id );
                }
            }
        }

        wp_delete_post( $post_id, true );

        $other_cached_post_ids = Utils::get_other_cached_posts( $post_id );

        foreach ( $other_cached_post_ids as $cached_post_id ) {
            Utils::clear_cache_for_page( $cached_post_id );
        }

        Utils::clear_cache_for_page( $post_id );
    }

    /**
     * Detects the active caching plugin by checking the list of active plugins.
     *
     * @return string|false The identifier of the detected caching plugin ('wp_super_cache', 'w3_total_cache',
     * 'wp_rocket', or 'litespeed_cache'), or false if no supported caching plugin is detected.
     * @since 1.2.0
     */
    static function detect_cache_plugin()
    {
        // Use unique identifiers for each plugin (classes, functions, or constants)
        if ( class_exists( 'WP_Super_Cache' ) || defined( 'WPCACHEHOME' ) ) {
            return 'wp_super_cache';
        } elseif ( class_exists( 'W3TC' ) || defined( 'W3TC' ) ) {
            return 'w3_total_cache';
        } elseif ( class_exists( 'RocketLazyLoad' ) || defined( 'WP_ROCKET_VERSION' ) ) {
            return 'wp_rocket';
        } elseif ( class_exists( 'LiteSpeed_Cache' ) || defined( 'LSCWP_V' ) ) {
            return 'litespeed_cache';
        } elseif ( class_exists( 'WpFastestCache' ) || defined( 'WPFC_MAIN_PATH' ) ) {
            return 'wp_fastest_cache';
        } elseif ( class_exists( 'Cache_Enabler' ) || defined( 'CE_PLUGIN_FILE' ) ) {
            return 'cache_enabler';
        } elseif ( class_exists( 'HyperCache' ) || defined( 'HYPER_CACHE_DIR' ) ) {
            return 'hyper_cache';
        } elseif ( class_exists( 'Breeze\Cache' ) || defined( 'BREEZE_VERSION' ) ) {
            return 'breeze';
        } elseif ( class_exists( 'Swift_Performance' ) || defined( 'SWIFT_PERFORMANCE_ACTIVATE' ) ) {
            return 'swift_performance';
        } elseif ( class_exists( 'SiteGround_Optimizer\Options' ) || defined( 'SG_CACHEPRESS_ENV' ) ) {
            return 'siteground_optimizer';
        } elseif ( class_exists( 'Hummingbird\WP_Hummingbird' ) || defined( 'WPHB_VERSION' ) ) {
            return 'hummingbird';
        } elseif ( class_exists( 'WP_Optimize' ) || defined( 'WP_OPTIMIZE_VERSION' ) ) {
            return 'wp_optimize';
        } elseif ( class_exists( 'RedisObjectCache' ) || defined( 'WP_REDIS_VERSION' ) ) {
            return 'redis_cache';
        } elseif ( class_exists( 'Memcached\Backend' ) || defined( 'WP_MEMCACHED_VERSION' ) ) {
            return 'memcached_cache';
        } elseif ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
            return 'advanced_cache';
        }

        // Default return false if no cache plugin is detected
        return false;
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

    /**
     * Get the post ID based on the given attributes.
     *
     * @param array $attributes The attributes used to determine the post ID.
     *                         Supported attributes:
     *                         - post_id: The specific post ID to retrieve.
     *                         - group_id: The group ID used to retrieve the post ID from the database.
     *
     * @return int The retrieved post ID.
     * @since 1.0.0
     */
    static function get_post_id( array $attributes ): int
    {
        if ( !empty( $attributes[ 'post_id' ] ) ) {
            $post_id = (int)$attributes[ 'post_id' ];
        } else if ( !empty( $attributes[ 'group_id' ] ) ) {
            $args = array (
                'post_type'  => GroupService::MYCLUB_GROUPS,
                'meta_key'   => 'myclub_groups_id',
                'meta_value' => $attributes[ 'group_id' ]
            );
            $posts = get_posts( $args );

            // If posts were found.
            if ( !empty( $posts ) ) {
                $post_id = $posts[ 0 ]->ID;
            }
        }

        return empty( $post_id ) ? 0 : $post_id;
    }

    /*
     * Retrieves a list of post IDs matching specific content (shortcodes, blocks, or both).
     *
     * @param int|null $post_id The post ID to match in content (optional).
     * @param string|null $group_id The group ID to match in content (optional).
     * @param string $mode The mode of search: 'shortcode', 'block', or 'both' (default 'both').
     *
     * @return array An array of matching post IDs, or an empty array if no matches are found.
     */
    static function get_other_cached_posts( ?int $post_id = null, ?string $group_id = null, string $mode = 'both' ): array
    {
        global $wpdb;

        if ( !$post_id && !$group_id ) {
            return [];
        }

        $like_clauses = [];

        if ( $post_id ) {
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%post_id="' . esc_sql( $post_id ) . '"%'
            );
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%"post_id":"' . esc_sql( $post_id ) . '"%'
            );
        }

        if ( $group_id ) {
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%group_id="' . esc_sql( $group_id ) . '"%'
            );
            $like_clauses[] = $wpdb->prepare(
                "post_content LIKE %s",
                '%"group_id":"' . esc_sql( $group_id ) . '"%'
            );
        }

        // Combine conditions with 'OR' and prepare query
        $where_clause = implode( ' OR ', $like_clauses );
        $post_type_exclusion = esc_sql( GroupService::MYCLUB_GROUPS );
        $post_status = 'publish';

        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE ($where_clause) AND post_status = %s AND post_type != %s",
            $post_status,
            $post_type_exclusion
        );

        return $wpdb->get_col( $query );
    }

    /**
     * Sanitize an array by recursively sanitizing text fields.
     *
     * @param array $array The array to be sanitized.
     *
     * @return array The sanitized array.
     *
     * @since 1.0.0
     */
    static function sanitize_array( array $array ): array
    {
        foreach ( $array as $key => &$value ) {
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