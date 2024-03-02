<?php
$postId = $attributes[ 'postId' ] ?? null;

if ( empty( $postId ) ) {
    $postId = get_the_ID();
}

$post = get_post( $postId );

$blocks = parse_blocks($post->post_content);

$myClubBlocks = array();

foreach($blocks as $block) {
    $blockName = $block['blockName'];

    // Check if $blockName starts with "myclub-groups/"
    if(!empty($blockName) && strpos($blockName, 'myclub-groups/') === 0) {
        $blockParts = explode("/", $blockName);
        $myClubBlocks[] = end($blockParts);
    }
}

$calendarName = get_option ( 'myclub_groups_calendar_title' );
$comingGamesName = get_option( 'myclub_groups_coming_games_title' );
$leadersName = get_option( 'myclub_groups_leaders_title' );
$membersName = get_option( 'myclub_groups_members_title' );
$newsName = get_option( 'myclub_groups_news_title' );

$blockLinkContents = [
    'calendar'     => [
        '<img src="' . plugins_url( '../../../assets/images/calendar.svg', __FILE__ ) . '" alt="' . $calendarName . '"><div>' . $calendarName . '</div>',
        $calendarName
    ],
    'coming-games' => [
        '<img src="' . plugins_url( '../../../assets/images/coming-games.svg', __FILE__ ) . '" alt="' . $comingGamesName . '"><div>' . $comingGamesName . '</div>',
        $comingGamesName,
    ],
    'members'      => [
        '<img src="' . plugins_url( '../../../assets/images/members.svg', __FILE__ ) . '" alt="' . $membersName . '"><div>' . $membersName . '</div>',
        $membersName
    ],
    'leaders'      => [
        '<img src="' . plugins_url( '../../../assets/images/leaders.svg', __FILE__ ) . '" alt="' . $leadersName . '"><div>' . $leadersName . '</div>',
        $leadersName
    ],
    'news'         => [
        '<img src="' . plugins_url( '../../../assets/images/news.svg', __FILE__ ) . '" alt="' . $newsName . '"><div>' . $newsName . '</div>',
        $newsName
    ]
];

?>


<div class="myclub-groups-navigation">
    <div class="myclub-groups-navigation-icons">
        <?php foreach ( $myClubBlocks as $myClubBlock ) {
            if ( in_array( $myClubBlock, array_keys( $blockLinkContents ) ) ) {
            ?><a href="#<?= $myClubBlock ?>" title="<?= $blockLinkContents[ $myClubBlock ][1] ?>" ><?= $blockLinkContents[ $myClubBlock ][0] ?></a><?php
            }
        } ?>
    </div>
</div>