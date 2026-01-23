<script>
window.cookieConsent = window.cookieConsent || {
    accepted: () => {
        // ...load marketing/tracking pixels here
    },
    revoke() {
        document.cookie = 'cookie_consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        alert("Cookie consent removed!\n\nTo opt-in again, please refresh the page to click accept all on the cookie consent banner.");
        // const confirmed = confirm("Cookie consent removed!\n\nTo opt-in again, please refresh the page to click accept all on the cookie consent banner.\n\nReload the page now?");
        // if (confirmed) {
        //     location.reload();
        // }
    }
};
</script>
<?php
if (isset($_COOKIE['cookie_consent'])) {
    $cookies = json_decode($_COOKIE['cookie_consent']);
    if (in_array('marketing', $cookies)) {
        echo '<script> cookieConsent.accepted(); </script>';
    }
} else {
?>

    <!-- cookie consent -->
    <script>
        function cookieConsentModal() {
            return {
                settings: false,
                cookies: {
                    // list cookies here
                    necessary: true,
                    analytics: true,
                    marketing: false,
                },
                saveCookies() {
                    const ele = document.getElementById('cookie-consent');
                    ele.parentNode.removeChild(ele);

                    const cookies = JSON.stringify(Object.keys(this.cookies).filter(key => this.cookies[key]));
                    document.cookie = 'cookie_consent=' + cookies + '; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=<?php echo tiny::config()->cookie_path; ?>; SameSite=Lax';
                    // document.cookie = 'cookie_consent=' + cookies + '; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=<?php echo tiny::config()->cookie_path; ?>; domain=<?php echo tiny::config()->cookie_domain; ?>; SameSite=Lax';

                    if (cookies.indexOf('marketing') > -1) {
                        window.cookieConsent.accepted();
                    }
                },
                accept() {
                    if (!this.settings) { // accept all
                        Object.keys(this.cookies).forEach(key => {
                            this.cookies[key] = true;
                        });
                    }
                    this.saveCookies();
                },
                reject() {
                    this.cookies = {
                        necessary: true,
                        analytics: true,
                        marketing: false,
                    }
                    this.saveCookies();
                }
            }
        }
    </script>

    <div x-data="cookieConsentModal" id="cookie-consent">
        <div>
            <h3>We use cookies</h3>
            <p class="notice">
                We use essential and anonymized analytics cookies, plus optional marketing cookies, in accordance with our <a target="legal" href="https://muxi.org/privacy" class="link">Privacy policy</a> and GDPR/CCPA laws.
            </p>

            <div x-auto-animate>
                <template x-if="settings">
                    <div class="cookie-settings">
                        <label>
                            <span class="cookie-toggle-mandatory"></span>
                            <dl>
                                <dt>Strictly necessary cookies</dt>
                                <dd>These cookies keep the site functioning - things like remembering your session and security settings.</dd>
                            </dl>
                        </label>
                        <label>
                            <span class="cookie-toggle-mandatory"></span>
                            <dl>
                                <dt>Anonymized analytics cookies</dt>
                                <dd>We'll collect anonymous data about how the site is used - what's working, what's broken, and what we should improve. <strong>No personal data, no tracking across sites.</strong></dd>
                            </dl>
                        </label>
                        <label>
                            <input type="checkbox" @change="cookies.marketing=!cookies.marketing">
                            <span class="cookie-toggle"></span>
                            <dl>
                                <dt>Marketing cookies</dt>
                                <dd>
                                    Marketing cookies let us track conversions from ads we might be running.
                                    This helps us know which marketing efforts work, but also means you might see our ads "following you" around the internet.
                                </dd>
                            </dl>
                        </label>
                    </div>
                </template>
            </div>

            <div class="cookie-buttons">
                <div>
                    <div>
                        <button role="accept-all" x-text="settings?'Save settings':'Accept all'" @click.prevent="accept">Accept all</button>
                        <button role="reject-non-essential" @click.prevent="reject" x-show="!settings">Reject non-essential</button>
                    </div>
                    <button role="link" @click="settings=!settings" x-text="settings?'Close settings':'Cookie settings'">Cookie settings</button>
                </div>
            </div>
        </div>
    </div>
<?php } // end if cookie consent is set
?>
