<?php

namespace MyClub\MyClubGroups\Tasks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use MyClub\MyClubGroups\BackgroundProcessing\Background_Process;
use MyClub\MyClubGroups\Services\MemberService;
use MyClub\MyClubGroups\Utils;

/**
 * Class ImageTask
 *
 * Represents an image task that creates images from external links for different types of items (group, member, news).
 */
class ImageTask extends Background_Process {
    protected $prefix = 'myclub_groups';
    protected $action = 'image_task';

    private static ?ImageTask $instance = null;

    /**
     * Initializes the ImageTask class and returns an instance of it.
     *
     * @return ImageTask The initialized instance of the ImageTask class.
     * @since 1.0.0
     */
    public static function init(): ImageTask {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create an image from an external link.
     *
     * @param mixed $item The image to be processed.
     *
     * @return bool Indicates whether the task should be processed further.
     * @since 1.0.0
     */
    protected function task ( $item ): bool
    {
        $decoded_item = json_decode( $item );

        if ( property_exists( $decoded_item, 'post_id' ) && property_exists( $decoded_item, 'image' ) ) {
            if ( $decoded_item ) {
                switch ( $decoded_item->type ) {
                    case 'group':
                        $this->addGroupImage( $decoded_item );
                        break;
                    case 'member':
                        $this->addMemberImage( $decoded_item );
                        break;
                    case 'news':
                        $this->addNewsImage( $decoded_item );
                        break;
                }
            }
        }

        return false;
    }

    /**
     * Adds a group image to the featured images of a post.
     *
     * @param object $item The item containing the necessary data for adding the image.
     *
     * @return void
     * @since 1.0.0
     */
    private function addGroupImage( object $item )
    {
        if ( property_exists( $item, 'group_id' ) ) {
            Utils::addFeaturedImage( $item->post_id, $item->image, 'group_' . $item->group_id . '_');
        }
    }

    /**
     * Adds a member image to the specified member item.
     *
     * @param object $item The item containing the necessary data for adding the image.
     *
     * @return void
     * @since 1.0.0
     */
    private function addMemberImage( object $item )
    {
        if ( property_exists( $item, 'member_id' ) ) {
            $member_item = MemberService::getMember( $item->post_id, $item->member_id );
            $url = $item->image->raw->url;

            if ( $item->image->member_default_image ) {
                // Save non-personal image (reuse image if present)
                $member_image = Utils::addImage( $url );
            } else {
                // Save image and save attachment id
                $member_image = Utils::addImage( $url, 'member_' . $member_item->member_id . '_' );
            }

            if ( $member_image && $member_item->image_id !== $member_image[ 'id' ] ) {
                $member_item->image_id = $member_image[ 'id' ];
                $member_item->image_url = $member_image[ 'url' ];
                MemberService::createOrUpdateMember( $item->post_id, $member_item );
            }
        }
    }

    /**
     * Adds a news image to the specified news item.
     *
     * @param object $item The item containing the necessary data for adding the image.
     *
     * @return void
     * @since 1.0.0
     */
    private function addNewsImage( object $item )
    {
        if ( property_exists( $item, 'news_id' ) ) {
            Utils::addFeaturedImage( $item->post_id, $item->image, 'news_' . $item->news_id . '_', $item->caption );
        }
    }
}
