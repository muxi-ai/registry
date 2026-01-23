<?php
tiny::header('Content-Type: application/javascript');
/**
 * Sync uic cookie with localStorage
 */
if (isset($_COOKIE['uic'])) {
    echo '!localStorage.getItem("uic") && localStorage.setItem("uic", "' . $_COOKIE['uic'] . '");';
} else {
    // echo 'const uic = localStorage.getItem("uic"); uic && (document.cookie = `uic=${uic}; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=' . tiny::config()->cookie_path . '; SameSite=Lax`);';
    echo 'const uic = localStorage.getItem("uic"); uic && (document.cookie = `uic=${uic}; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=' . tiny::config()->cookie_path . '; domain=' . tiny::config()->cookie_domain . '; SameSite=Lax`);';
}

// --------- MixPanel Analytics ---------
if (@$_SERVER['ENV'] != 'local') {
?>
(function(e,c){if(!c.__SV){var l,h;window.mixpanel=c;c._i=[];c.init=function(q,r,f){function t(d,a){var g=a.split(".");2==g.length&&(d=d[g[0]],a=g[1]);d[a]=function(){d.push([a].concat(Array.prototype.slice.call(arguments,0)))}}var b=c;"undefined"!==typeof f?b=c[f]=[]:f="mixpanel";b.people=b.people||[];b.toString=function(d){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);d||(a+=" (stub)");return a};b.people.toString=function(){return b.toString(1)+".people (stub)"};l="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders start_session_recording stop_session_recording people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" ");
for(h=0;h<l.length;h++)t(b,l[h]);var n="set set_once union unset remove delete".split(" ");b.get_group=function(){function d(p){a[p]=function(){b.push([g,[p].concat(Array.prototype.slice.call(arguments,0))])}}for(var a={},g=["get_group"].concat(Array.prototype.slice.call(arguments,0)),m=0;m<n.length;m++)d(n[m]);return a};c._i.push([q,r,f])};c.__SV=1.2;var k=e.createElement("script");k.type="text/javascript";k.async=!0;k.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===
e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";e=e.getElementsByTagName("script")[0];e.parentNode.insertBefore(k,e)}})(document,window.mixpanel||[])

// Init Mixpanel
mixpanel.init('58e9a00f77c9a76fbfac1c654bcb51f1', {autocapture: true, record_sessions_percent: 100});

// Identity: link to install_hash if available
localStorage.getItem("uic") && mixpanel.identify(localStorage.getItem("uic"));

// Track page view (Mixpanel handles analysis/filtering by path)
const params = new URLSearchParams(window.location.search);
mixpanel.track('page_view', {
    site: window.location.hostname,
    page_path: window.location.pathname,
    referrer: document.referrer || null,
    utm_source: params.get('utm_source'),
    utm_campaign: params.get('utm_campaign'),
    utm_medium: params.get('utm_medium'),
    utm_content: params.get('utm_content'),
    utm_term: params.get('utm_term'),
});

// Track install intent
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-install-cta]');
    if (btn) {
        mixpanel.track('install_cta_click', {
            target: btn.dataset.installCta || 'unknown'
        });
    }
});
<?php
}
?>
