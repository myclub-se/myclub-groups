jQuery(document).ready(function($) {
    function addNotice(text, type) {
        var $notice = $( '<div></div>' )
            .attr( 'role', 'alert' )
            .attr( 'tabindex', '-1' )
            .addClass( 'is-dismissible notice notice-' + type )
            .append( $( '<p></p>' ).html( '<strong>' + text + '</strong>' ) )
            .append(
                $( '<button></button>' )
                    .attr( 'type', 'button' )
                    .addClass( 'notice-dismiss myclub-dismiss' )
                    .append( $( '<span></span>' ).addClass( 'screen-reader-text' ).text( wp.i18n.__( 'Dismiss this notice.' ) ) )
            );

        $('.myclub-dismiss').parent().hide();
        $("#myclub-settings-form").find("h2").first().after($notice);
    }

    function setSortableItems() {
        const $sortList = $("#myclub_groups_show_items_order");
        const $items = $sortList.find("li input");
        const $selectedItems = $(".sort-item-setter");
        const sortedItems = $items.map((_, item) => $(item).val()).get();

        $selectedItems.each(function(_, item) {
            const $checkbox = $(item);
            const name = $checkbox.data('name');
            const displayName = $checkbox.data('display-name');

            if (!sortedItems.includes(name) && $checkbox.is(':checked')) {
                $sortList.append(`<li><input type="hidden" name="myclub_groups_show_items_order[]" value="${name}">${displayName}</li>`);
            }
        });

        sortedItems.forEach(value => {
            const $checkbox = $selectedItems.filter(`[data-name="${value}"]`);

            if ($checkbox.length === 0 || !$checkbox.is(':checked')) {
                $sortList.find('li input').filter((_, item) => $(item).val() === value).parent().remove();
            }
        });

        try {
            $sortList.sortable("refresh");
        } catch(e) {
            $sortList.sortable();
        }
    }

    $("#myclub-reload-groups-button").on("click", function() {
        addNotice(wp.i18n.__('Reloading groups', 'myclub-groups'), 'success');
        $("#myclub_groups_last_groups_sync").html(wp.i18n.__('The groups update task is currently running', 'myclub-groups'));

        $.ajax({
            url: ajaxurl,
            data: {
                "action": "myclub_reload_groups"
            },
            success: function(returned_data) {
                addNotice(returned_data.data.message, returned_data.success ? 'success' : 'error');
            },
            error: function(errorThrown) {
                addNotice(wp.i18n.__('Unable to reload groups', 'myclub-groups'), 'error');
                console.log(errorThrown);
            }
        });
    });

    $("#myclub-reload-news-button").on("click", function() {
        addNotice(wp.i18n.__('Reloading news', 'myclub-groups'), 'success');
        $("#myclub_groups_last_news_sync").html(wp.i18n.__('The news update task is currently running', 'myclub-groups'));

        $.ajax({
            url: ajaxurl,
            data: {
                "action": "myclub_reload_news"
            },
            success: function(returned_data) {
                addNotice(returned_data.data.message, returned_data.success ? 'success' : 'error');
            },
            error: function(errorThrown) {
                addNotice(wp.i18n.__('Unable to reload news', 'myclub-groups'), 'error');
                console.log(errorThrown);
            }
        });
    });

    $("#myclub-sync-club-calendar-button").on("click", function() {
        addNotice(wp.i18n.__('Synchronizing club calendar', 'myclub-groups'), 'success');
        $("#myclub_groups_last_club_calendar_sync").html(wp.i18n.__('Calendar synchronization is currently running', 'myclub-groups'));

        $.ajax({
            url: ajaxurl,
            data: {
                "action": "myclub_sync_club_calendar"
            },
            success: function(returned_data) {
                addNotice(returned_data.data.message, returned_data.success ? 'success' : 'error');
            },
            error: function(errorThrown) {
                addNotice(wp.i18n.__('Unable to synchronize club calendar', 'myclub-groups'), 'error');
                console.log(errorThrown);
            }
        })
    });

    $("#myclub-settings-form").on("click", ".myclub-dismiss", function() {
        $(this).parent().hide();
    });

    $('.sort-item-setter').on('change', function() {
        setSortableItems();
    });

    setSortableItems();
});