jQuery(document).ready(function ($) {
    console.log("Coin Selector Initialized");

    $('#coin-selector-form').on('submit', function (e) {
        e.preventDefault();

        const selectedCoins = $('#coin-selector-list')
            .find('.coin-selector-checkbox:checked')
            .map(function () {
                return $(this).closest('.coin-selector-item').data('coin-id');
            })
            .get();

        console.log("Selected Coins:", selectedCoins);

        $.post(paperTradeMeData.ajax_url, {
            action: 'save_user_coin_selector',
            coins: selectedCoins,
            nonce: paperTradeMeData.nonce,
        })
            .done(function (response) {
                const messageBox = $('#coin-selector-message');

                if (response.success) {
                    messageBox
                        .text(response.data)
                        .removeClass('error')
                        .addClass('success')
                        .fadeIn();

                    // Redirect to the Coin Dashboard after success
                    setTimeout(function () {
                        window.location.href = '/coin-dashboard'; // Replace with your actual coin dashboard URL
                    }, 500); // Add a slight delay for the message to display
                } else {
                    messageBox
                        .text(response.data)
                        .removeClass('success')
                        .addClass('error')
                        .fadeIn();
                }
            })
            .fail(function () {
                $('#coin-selector-message')
                    .text('Error saving coin selection.')
                    .removeClass('success')
                    .addClass('error')
                    .fadeIn();
            });
    });
});
