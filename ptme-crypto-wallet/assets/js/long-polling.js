jQuery(document).ready(function ($) {
    console.log("Long Polling Initialized");

    function checkCacheAndUpdate() {
        const $dashboardContainer = $('#coin-dashboard-container');
        const nextUpdate = parseInt($dashboardContainer.data('next-update'), 10);
        const now = Math.floor(Date.now() / 1000);

        if (nextUpdate > now) {
            console.log(`Waiting for next update at ${nextUpdate} (current time: ${now})`);
            setTimeout(checkCacheAndUpdate, (nextUpdate - now) * 1000);
            return;
        }

        // Poll the server for updated data
        console.log("Polling server for updated data...");
        $.post(paperTradeMeData.ajax_url, {
            action: 'fetch_updated_coin_data',
            nonce: paperTradeMeData.nonce,
        })
            .done(function (response) {
                if (response.success) {
                    const { cache_ready, coins, last_updated } = response.data;

                    if (cache_ready) {
                        console.log("Cache ready. Updating dashboard...");

                        // Update the coin dashboard UI
                        const $dashboardGrid = $('#coin-dashboard-grid');
                        $dashboardGrid.empty();

                        coins.forEach(coin => {
                            const coinHtml = `
                                <li class="coin-dashboard-item">
                                    <a href="/coin/${coin.id}/" class="coin-dashboard-link">
                                        <img src="${coin.image}" alt="${coin.name} logo" class="coin-logo">
                                        <h3 class="coin-name">${coin.name} (${coin.symbol.toUpperCase()})</h3>
                                        <p class="coin-price">Price: $${coin.current_price.toFixed(2)}</p>
                                        <p class="coin-market-cap">Market Cap: ${coin.market_cap.toLocaleString()}</p>
                                        <p class="coin-change" style="color:${coin.price_change_percentage_24h >= 0 ? 'green' : 'red'};">
                                            24h Change: ${coin.price_change_percentage_24h.toFixed(2)}%
                                        </p>
                                    </a>
                                </li>`;
                            $dashboardGrid.append(coinHtml);
                        });

                        // Update the "Last Updated" timestamp
                        const formattedTime = new Date(last_updated * 1000).toLocaleString(); // Convert timestamp to human-readable format
                        $('#last-updated-time').text(`Last Updated: ${formattedTime}`);
                        $dashboardContainer.data('last-cached', last_updated);

                        // Recalculate the next update time and restart polling
                        const newNextUpdate = Math.floor(Date.now() / 1000) + 300; // 5 minutes
                        $dashboardContainer.data('next-update', newNextUpdate);

                        console.log(`Next update scheduled at ${newNextUpdate}`);
                        setTimeout(checkCacheAndUpdate, 3000); // Small delay before next check
                    } else {
                        console.log("Cache not ready yet. Retrying...");
                        setTimeout(checkCacheAndUpdate, 3000); // Retry after 3 seconds
                    }
                } else {
                    console.error("Failed to fetch updated coin data. Retrying...");
                    setTimeout(checkCacheAndUpdate, 5000); // Retry after 5 seconds on failure
                }
            })
            .fail(function () {
                console.error("AJAX request for updated coin data failed. Retrying...");
                setTimeout(checkCacheAndUpdate, 5000); // Retry after 5 seconds on failure
            });
    }

    // Start the long polling process
    checkCacheAndUpdate();
});
