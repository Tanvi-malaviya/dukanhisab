<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting…</title>
</head>
<body>
    <script>
        // Clear any previously cached shop-owner session (e.g. from a prior
        // "Login As" or a different account on this browser) so the SPA is
        // forced to fetch fresh profile/shop data for this user instead of
        // reusing stale cached values.
        ['shopowner_token', 'token', 'shopowner_user', 'shopowner_shop', 'shopowner_has_shop', 'lifetime_offer_dismissed'].forEach(k => localStorage.removeItem(k));
        localStorage.setItem('shopowner_token', @json($token));
        // Go through /shop/login (not /shop/) so the auth SPA re-verifies this
        // user's shop status from the server before deciding where to land.
        // /shop/ (the dashboard app) trusts the cached "has shop" flag at load
        // time, which was just cleared above, so it would otherwise flash the
        // shop-setup screen and then bounce back to login before settling.
        window.location.href = '/shop/login';
    </script>
    <noscript>
        JavaScript is required to continue. <a href="/shop/login">Click here</a> if you are not redirected automatically.
    </noscript>
</body>
</html>
