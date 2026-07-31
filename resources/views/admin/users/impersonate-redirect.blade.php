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
        ['shopowner_token', 'token', 'shopowner_user', 'shopowner_shop', 'shopowner_has_shop'].forEach(k => localStorage.removeItem(k));
        localStorage.setItem('shopowner_token', @json($token));
        window.location.href = '/shopowner/';
    </script>
    <noscript>
        JavaScript is required to continue. <a href="/shopowner/">Click here</a> if you are not redirected automatically.
    </noscript>
</body>
</html>
