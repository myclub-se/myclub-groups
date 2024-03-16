<?php

namespace MyClub\MyClubGroups\Tasks;

use MyClub\MyClubGroups\Services\GroupService;
use MyClub\MyClubGroups\Utils;
use WP_Background_Process;

class ImageTask extends WP_Background_Process {
    protected $action = 'myclub_image_task';

    private static $instance = null;

    public static function init() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function task ( $item ): bool
    {
        $decodedItem = json_decode( $item );

        if ( $decodedItem ) {
            switch ( $decodedItem->type) {
                case 'group':
                    $this->addGroupImage( $decodedItem );
                    break;
                case 'member':
                    $this->addMemberImage( $decodedItem );
                    break;
                case 'news':
                    $this->addNewsImage( $decodedItem );
                    break;
            }
        }

        return false;
    }

    private function addGroupImage( $item )
    {
        Utils::addFeaturedImage( $item->postId, $item->image, 'group_' . $item->groupId . '_');
    }

    private function addMemberImage( $item )
    {
        $memberItems = json_decode( get_post_meta( $item->postId, 'members', true ) );
        $memberType = $item->memberType;
        $members = $memberItems->$memberType;
        $memberUpdated = false;

        if ( isset( $members ) ) {
            foreach ( $members as $member ) {
                if ( $member->id === $item->memberId ) {
                    $url = $item->image->raw->url;

                    if ( in_array( $url, GroupService::DEFAULT_PICTURES ) ) {
                        // Save non personal image (reuse image if present)
                        $member_image = Utils::addImage( $url );
                    } else {
                        // Save image and save attachment id
                        $member_image = Utils::addImage( $url, 'member_' . $member->id . '_' );
                    }

                    if ( !property_exists( $member, 'member_image' ) || ( $member->member_image->id !== $member_image[ 'id' ] ) ) {
                        if( property_exists( $member, 'member_image' ) && isset( $member->member_image->id ) ) {
                            wp_delete_attachment( $member->member_image->id );
                        }
                        $member->member_image = $member_image;
                        $memberUpdated = true;
                    }

                    break;
                }
            }
        }

        if ( $memberUpdated ) {
            $memberItems->$memberType = $members;
            update_post_meta( $item->postId, 'members', wp_json_encode( $memberItems, JSON_UNESCAPED_UNICODE ) );
        }
    }

    private function addNewsImage( $item )
    {
        Utils::addFeaturedImage( $item->postId, $item->image, 'news_' . $item->newsId . '_');
    }
}

