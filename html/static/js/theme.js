/* jshint esversion: 9 */

if (window.navigator.standalone) {
  document.querySelector('html').classList.add('pwa');
}
if ((window.navigator.userAgentData?.platform) == 'macOS' || (window.navigator.platform || '') == 'MacIntel') {
  document.querySelector('html').classList.add('mac');
}
if (window.navigator.userAgentData?.mobile) {
  document.querySelector('html').classList.add('mobile');
}
if (window.screen.availWidth >= 1920) {
  document.querySelector('html').classList.add('screen-3xl');
}

(function (theme, undefined) {
  theme.getSetting = () => localStorage.getItem('theme') || 'system';
  theme.get = () => {
    const theme = localStorage.getItem('theme') || 'system';
    if (theme == 'system' || theme == null) {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return theme;
  }

  theme.settings = () => {
    return localStorage.getItem('theme') || 'system';
  };

  theme.force = () => {
    init();
  };

  theme.set = (theme) => {
    const html = document.querySelector('html');
    html.classList.remove('system', 'light', 'dark');
    if (theme === 'dark') {
      localStorage.setItem('theme', 'dark');
      document.querySelector('html').classList.remove('light');
      document.querySelector('html').classList.add('dark');

      // support scalar api reference
      if (document.body) {
        document.body.classList.add('dark-mode');
        document.body.classList.remove('light-mode');
      }
    } else  if (theme === 'light') {
      localStorage.setItem('theme', 'light');
      document.querySelector('html').classList.remove('dark');
      document.querySelector('html').classList.add('light');

      // support scalar api reference
      if (document.body) {
        document.body.classList.add('light-mode');
        document.body.classList.remove('dark-mode');
      }
    } else {
      localStorage.setItem('theme', 'system');
      const prefersDarkColorScheme = window && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      html.classList.add('system');
      if (prefersDarkColorScheme) {
        html.classList.remove('light');
        html.classList.add('dark');

        // support scalar api reference
        if (document.body) {
          document.body.classList.add('dark-mode');
          document.body.classList.remove('light-mode');
        }
      } else {
        html.classList.remove('dark');
        html.classList.add('light');

        // support scalar api reference
        if (document.body) {
          document.body.classList.add('light-mode');
          document.body.classList.remove('dark-mode');
        }
      }
    }

    // init();
  };

  function init() {
    theme.set(localStorage.getItem('theme') || 'system');
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', ({ matches }) => {
      theme.set(localStorage.getItem('theme') || 'system');
    });

    try {
      charting.recolorAll();
    } catch (e) {}

    try { reRenderMermaid(); } catch (e) {}
  }

  init();

}((window.theme = window.theme || {})));
