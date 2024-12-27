jQuery(document).ready(function ($) {
    console.log("Coin Dashboard Initialized");

    function sortCoins(type, order) {
        console.log(`Sorting by ${type}, Order: ${order}`);

        $.post(paperTradeMeData.ajax_url, {
            action: 'sort_coin_dashboard',
            sort_type: type,
            sort_order: order,
            nonce: paperTradeMeData.nonce,
        })
            .done(function (response) {
                if (response.success) {
                    $('#coin-dashboard-grid').fadeOut(200, function () {
                        $(this).html(response.data.coins_html).fadeIn(200);
                    });
                } else {
                    console.error("Error:", response.data);
                }
            })
            .fail(function () {
                console.error("AJAX Request Failed.");
            });
    }

    // Sorting Links Click Handler
    $('#coin-sort-links').on('click', '.sort-link', function (e) {
        e.preventDefault();

        const sortType = $(this).data('sort');
        let sortOrder = $(this).data('order') === 'asc' ? 'desc' : 'asc';
        $(this).data('order', sortOrder);

        sortCoins(sortType, sortOrder);
    });

    // Trigger Default Sort on Page Load
    const defaultSortType = $('#coin-dashboard-container').data('default-sort');
    const defaultSortOrder = $('#coin-dashboard-container').data('default-order');
    sortCoins(defaultSortType, defaultSortOrder);
});
