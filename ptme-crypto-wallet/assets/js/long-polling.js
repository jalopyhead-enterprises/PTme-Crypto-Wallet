jQuery(document).ready(function ($) {
    console.log("Long Polling Initialized");

    function formatMarketCap(number) {
        if (number >= 1_000_000_000_000) {
            return (number / 1_000_000_000_000).toFixed(2) + ' T';
        } else if (number >= 1_000_000_000) {
            return (number / 1_000_000_000).toFixed(2) + ' B';
        } else if (number >= 1_000_000) {
            return (number / 1_000_000).toFixed(2) + ' M';
        } else {
            return number.toLocaleString();
        }
    }

    function formatPrice(price) {
        if (price >= 1) {
            // For whole numbers and larger values, show 2 decimal places
            return price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            // For smaller values (<1), show up to 4 decimal places
            return price.toLocaleString(undefined, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
        }
    }

    function formatTimestamp(timestamp) {
        const date = new Date(timestamp * 1000); // Convert seconds to milliseconds
        const options = {
            month: 'long',    // Full month name
            day: 'numeric',   // Day of the month
            year: 'numeric',  // Full year
            hour: 'numeric',  // Hour
            minute: '2-digit', // Minute
            second: '2-digit', // Second
            hour12: true      // Use 12-hour clock with lowercase am/pm
        };
        return date.toLocaleString(undefined, options).replace('AM', 'am').replace('PM', 'pm');
    }

    function formatInitialData() {
        $('.coin-dashboard-item').each(function () {
            const $marketCapElement = $(this).find('.coin-market-cap');
            const $priceElement = $(this).find('.coin-price');

            const rawMarketCap = parseFloat($(this).data('market-cap'));
            if (!isNaN(rawMarketCap)) {
                const formattedMarketCap = formatMarketCap(rawMarketCap);
                $marketCapElement.text(`Market Cap: ${formattedMarketCap}`);
            }

            const rawPrice = parseFloat($(this).data('price'));
            if (!isNaN(rawPrice)) {
                const formattedPrice = formatPrice(rawPrice);
                $priceElement.text(`Price: $${formattedPrice}`);
            }
        });
    }

    function checkCacheAndUpdate() {
        const $dashboardContainer = $('#coin-dashboard-container');
        const nextUpdate = parseInt($dashboardContainer.data('next-update'), 10);
        const now = Math.floor(Date.now() / 1000);

        if (nextUpdate > now) {
            console.log(`Waiting for next update at ${nextUpdate} (current time: ${now})`);
            setTimeout(checkCacheAndUpdate, (nextUpdate - now) * 1000);
            return;
        }

        console.log("Polling server for updated data...");
        $.post(paperTradeMeData.ajax_url, {
            action: 'fetch_updated_coin_data',
            nonce: paperTradeMeData.nonce,
        })
            .done(function (response) {
                if (response.success) {
                    const coins = response.data.coins;
                    const $dashboardGrid = $('#coin-dashboard-grid');
                    $dashboardGrid.empty();

                    coins.forEach(coin => {
                        const coinHtml = `
                            <li class="coin-dashboard-item" data-market-cap="${coin.market_cap}" data-price="${coin.current_price}">
                                <a href="/coin/${coin.id}/" class="coin-dashboard-link">
                                    <img src="${coin.image}" alt="${coin.name} logo" class="coin-logo">
                                    <h3 class="coin-name">${coin.name} (${coin.symbol.toUpperCase()})</h3>
                                    <p class="coin-price">Price: $${formatPrice(coin.current_price)}</p>
                                    <p class="coin-market-cap">Market Cap: ${formatMarketCap(coin.market_cap)}</p>
                                    <p class="coin-change" style="color:${coin.price_change_percentage_24h >= 0 ? 'green' : 'red'};">
                                        24h Change: ${coin.price_change_percentage_24h.toFixed(2)}%
                                    </p>
                                </a>
                            </li>`;
                        $dashboardGrid.append(coinHtml);
                    });

                    const formattedTime = formatTimestamp(response.data.last_updated);
                    $('#last-updated-time').text(formattedTime); // Only update the timestamp

                    $dashboardContainer.data('last-cached', response.data.last_updated);

                    const newNextUpdate = Math.floor(Date.now() / 1000) + 300;
                    $dashboardContainer.data('next-update', newNextUpdate);

                    console.log(`Next update scheduled at ${newNextUpdate}`);
                    setTimeout(checkCacheAndUpdate, 3000);
                } else {
                    console.error("Failed to fetch updated coin data. Retrying...");
                    setTimeout(checkCacheAndUpdate, 5000);
                }
            })
            .fail(function () {
                console.error("AJAX request failed. Retrying...");
                setTimeout(checkCacheAndUpdate, 5000);
            });
    }

    formatInitialData();
    checkCacheAndUpdate();
});
