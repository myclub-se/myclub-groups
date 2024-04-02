<?php
$post_id = $attributes[ 'post_id' ] ?? null;

if ( empty( $post_id ) ) {
    $post_id = get_the_ID();
}

$post = get_post( $post_id );

$blocks = parse_blocks($post->post_content);

$myclub_blocks = array();

foreach($blocks as $block) {
    $block_name = $block[ 'blockName'];

    // Check if $blockName starts with "myclub-groups/"
    if(!empty($block_name) && strpos($block_name, 'myclub-groups/') === 0) {
        $blockParts = explode("/", $block_name);
        $myclub_blocks[] = end($blockParts);
    }
}

$calendar_name = get_option ( 'myclub_groups_calendar_title' );
$coming_games_name = get_option( 'myclub_groups_coming_games_title' );
$leaders_name = get_option( 'myclub_groups_leaders_title' );
$members_name = get_option( 'myclub_groups_members_title' );
$news_name = get_option( 'myclub_groups_news_title' );

$block_link_contents = [
    'calendar'     => [
        '<img src="' . plugins_url( '../../../resources/images/calendar.svg', __FILE__ ) . '" alt="' . $calendar_name . '"><div>' . $calendar_name . '</div>',
        $calendar_name
    ],
    'coming-games' => [
        '<img src="' . plugins_url( '../../../resources/images/coming-games.svg', __FILE__ ) . '" alt="' . $coming_games_name . '"><div>' . $coming_games_name . '</div>',
        $coming_games_name,
    ],
    'members'      => [
        '<img src="' . plugins_url( '../../../resources/images/members.svg', __FILE__ ) . '" alt="' . $members_name . '"><div>' . $members_name . '</div>',
        $members_name
    ],
    'leaders'      => [
        '<img src="' . plugins_url( '../../../resources/images/leaders.svg', __FILE__ ) . '" alt="' . $leaders_name . '"><div>' . $leaders_name . '</div>',
        $leaders_name
    ],
    'news'         => [
        '<img src="' . plugins_url( '../../../resources/images/news.svg', __FILE__ ) . '" alt="' . $news_name . '"><div>' . $news_name . '</div>',
        $news_name
    ]
];

?>


<div class="myclub-groups-navigation">
    <div class="myclub-groups-navigation-container">
        <div class="myclub-groups-navigation-icons">
            <?php foreach ( $myclub_blocks as $myclub_block ) {
                if ( in_array( $myclub_block, array_keys( $block_link_contents ) ) ) {
                ?><a href="#<?= $myclub_block ?>" title="<?= $block_link_contents[ $myclub_block ][ 1] ?>" ><?= $block_link_contents[ $myclub_block ][ 0] ?></a><?php
                }
            } ?>
        </div>
    </div>
</div>