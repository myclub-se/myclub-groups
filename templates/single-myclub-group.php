<?php
/*
 * Template Name: MyClub Group Page
 * Template Post Type: myclub-groups
 * Description: Template used to display the group pages in a non-block based theme.
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

the_post();

the_content();

get_footer();