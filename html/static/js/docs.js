function onDocReady(callback, timeout = 0) {
    if (/complete|interactive|loaded/.test(document.readyState)) {
        setTimeout(callback, timeout);
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(callback, timeout);
        });
    }
}

var video = {
    show: (id) => {
        document.querySelector('.video-container iframe').src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1';
        document.body.classList.add('video-lightbox');
    },
    hide: () => {
        document.querySelector('.video-container iframe').src = 'about:blank';
        document.body.classList.remove('video-lightbox');
    }
};

function codeBlockWidth() {
    // return;
    const blocks = document.querySelectorAll('.code-toolbar');
    const section = document.querySelector('article section');
    if (!blocks.length || !section) {
        return;
    }
    const zoom = 1;
    blocks.forEach((block) => {
        block.style.display = 'none';
    });
    const maxWidth = section.offsetWidth;
    blocks.forEach((block) => {
        // block.style.minWidth = (maxWidth / zoom) + 'px';
        block.style.zoom = zoom;
        block.style.display = 'block';
        block.style.maxWidth = 'calc(100vw - 5.5rem)';
    });

    const prism = document.getElementById('prism');
    if (prism && prism.parentNode) {
        prism.parentNode.removeChild(prism);
    }

    // codeBlockWidth();
}

function refreshCodeBlocks(container) {
    if (window.Prism) {
        if (container && window.Prism.highlightAllUnder) {
            window.Prism.highlightAllUnder(container);
        } else if (window.Prism.highlightAll) {
            window.Prism.highlightAll();
        }
    }
    // Wait for Prism toolbar injection + layout before sizing.
    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(codeBlockWidth);
    });
}

window.__docsState = window.__docsState || {};
window.__docsState.codeBlockResizeTimer = window.__docsState.codeBlockResizeTimer || null;
window.__docsState.codeBlockResizeBound = window.__docsState.codeBlockResizeBound || false;
window.__docsState.docHandlersBound = window.__docsState.docHandlersBound || false;

function bindCodeBlockResize() {
    if (window.__docsState.codeBlockResizeBound) {
        return;
    }
    window.__docsState.codeBlockResizeBound = true;
    window.addEventListener('resize', () => {
        if (window.__docsState.codeBlockResizeTimer) {
            clearTimeout(window.__docsState.codeBlockResizeTimer);
        }
        window.__docsState.codeBlockResizeTimer = setTimeout(codeBlockWidth, 50);
    });
}

var tabGroups = {
    showTabPref: (lastTabPref) => {
        if (lastTabPref) {
            const tabGroupsWithPref = document.querySelectorAll(`.tab-group:has([data-id="${lastTabPref}"])`);
            tabGroupsWithPref.forEach((tabGroup) => {
                const tabs = tabGroup.querySelectorAll('nav button');
                const panels = tabGroup.querySelectorAll('div');
                tabs.forEach((tab) => {
                    tab.classList.remove('active-tab');
                });
                panels.forEach((panel) => {
                    panel.classList.remove('active-tab');
                });
                tabGroup.querySelector(`button[data-target="${lastTabPref}"]`).classList.add('active-tab');
                tabGroup.querySelector(`div[data-id="${lastTabPref}"]`).classList.add('active-tab');
            });
        }
    },
    listenForClick: () => {
        document.querySelectorAll('.tab-group nav button').forEach((tab) => {
            tab.addEventListener('click', () => {
                localStorage.setItem('lastTabPref', tab.getAttribute('data-target'));
                tabGroups.showTabPref(tab.getAttribute('data-target'));
            });
        });
    },
    init: () => {
        tabGroups.listenForClick();
        tabGroups.showTabPref(localStorage.getItem('lastTabPref') || null);
    }
};

function spyScrolling() {
    const sections = Array.from(document.querySelectorAll('.anchor'));
    const tocLinks = Array.from(document.querySelectorAll('#toc li a'));
    if (!sections.length || !tocLinks.length) {
        return;
    }

    const tocList = document.querySelector('#toc .toc');
    const tocById = new Map();
    tocLinks.forEach((link) => {
        const href = link.getAttribute('href') || '';
        const hashIndex = href.indexOf('#');
        if (hashIndex !== -1) {
            tocById.set(href.slice(hashIndex + 1), link);
        }
    });

    const indexById = new Map();
    sections.forEach((section, index) => {
        if (section.id) {
            indexById.set(section.id, index);
        }
    });

    let activeLink = tocLinks.find((link) => link.parentElement && link.parentElement.classList.contains('active')) || null;
    const updateTocIndicator = (link) => {
        if (!tocList || !link) {
            return;
        }
        const li = link.closest('li');
        if (!li) {
            return;
        }
        const listRect = tocList.getBoundingClientRect();
        const liRect = li.getBoundingClientRect();
        const top = liRect.top - listRect.top + tocList.scrollTop;
        tocList.style.setProperty('--toc-indicator-y', `${top}px`);
        tocList.style.setProperty('--toc-indicator-h', `${liRect.height}px`);
        tocList.style.setProperty('--toc-indicator-opacity', '1');
    };
    const setActiveById = (id) => {
        const nextLink = tocById.get(id);
        if (!nextLink || nextLink === activeLink) {
            return;
        }
        if (activeLink && activeLink.parentElement) {
            activeLink.parentElement.classList.remove('active');
        }
        activeLink = nextLink;
        if (activeLink.parentElement) {
            activeLink.parentElement.classList.add('active');
        }
        updateTocIndicator(activeLink);
    };

    const hash = window.location.hash ? window.location.hash.slice(1) : '';
    if (hash && tocById.has(hash)) {
        setActiveById(hash);
    } else if (!activeLink && tocLinks[0] && tocLinks[0].parentElement) {
        activeLink = tocLinks[0];
        activeLink.parentElement.classList.add('active');
        updateTocIndicator(activeLink);
    }

    window.__docsState = window.__docsState || {};
    if (window.__docsState.tocObserver) {
        window.__docsState.tocObserver.disconnect();
        window.__docsState.tocObserver = null;
    }
    if (window.__docsState.tocScrollHandler) {
        window.removeEventListener('scroll', window.__docsState.tocScrollHandler);
        window.__docsState.tocScrollHandler = null;
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            let bestId = null;
            let bestIndex = -1;
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                const id = entry.target.id;
                const index = indexById.get(id);
                if (index != null && index > bestIndex) {
                    bestIndex = index;
                    bestId = id;
                }
            });
            if (bestId) {
                setActiveById(bestId);
            }
        }, { rootMargin: '0px 0px -50% 0px', threshold: 0 });
        sections.forEach((section) => observer.observe(section));
        window.__docsState.tocObserver = observer;
        return;
    }

    let ticking = false;
    const onScroll = () => {
        if (ticking) {
            return;
        }
        ticking = true;
        window.requestAnimationFrame(() => {
            ticking = false;
            const scrollPos = document.documentElement.scrollTop || document.body.scrollTop;
            let id;
            for (let i = 0; i < sections.length; i++) {
                if (sections[i].offsetTop <= scrollPos + window.innerHeight / 2) {
                    id = sections[i].id;
                }
            }
            if (id) {
                setActiveById(id);
            }
        });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.__docsState.tocScrollHandler = onScroll;
    onScroll();
}

function initNav() {
    try {
        const docList = {}
        document.querySelectorAll('aside.docs-sidebar nav.section-links li a:not([href^="#"]):not([target])').forEach((ele) => {
            ele.classList.remove('active-doc');
            if (!ele.getAttribute('href').startsWith('/docs/api')) {
                docList[ele.getAttribute('href')] = ele.innerHTML.trim();
            }
        });

        const activeDoc =
            document.querySelector('aside.docs-sidebar nav.section-links li a[href="' + window.location.pathname + '"]') ||
            document.querySelector('aside.docs-sidebar nav.section-links li a[href="' + window.location.pathname.replace(/\/[^\/]+$/, '/') + '"]');
        if (activeDoc) {
            activeDoc.classList.add('active-doc');
            setTimeout(() => {
                if (window.innerHeight - activeDoc.getBoundingClientRect().top < 300) {
                    activeDoc.scrollIntoView({ behavior: "instant", block: "start" });
                }
            }, 100);
        }

        const keys = Object.keys(docList);
        const currIndex = keys.indexOf(window.location.pathname) !== -1 ? keys.indexOf(window.location.pathname) : keys.indexOf(window.location.pathname.replace(/\/[^\/]+$/, '/'));

        document.getElementById('prev-page').classList.add('inactive');
        document.getElementById('next-page').classList.add('inactive');

        if (currIndex > 0) {
            const url = keys.slice(currIndex - 1, currIndex)[0];
            document.getElementById('prev-page').href = url;
            document.querySelector('#prev-page div:first-of-type').innerHTML = docList[url];
            document.getElementById('prev-page').classList.remove('inactive');
        }
        if (currIndex < keys.length - 1) {
            const url = keys.slice(currIndex + 1, currIndex + 2)[0];
            document.getElementById('next-page').href = url;
            document.querySelector('#next-page div:first-of-type').innerHTML = docList[url];
            document.getElementById('next-page').classList.remove('inactive');
        }
    } catch (er) {
        console.log(er);
    }

    // collapsable sidebar nav sections (delegated to avoid per-item listeners)
    const sidebarNav = document.querySelector('aside.docs-sidebar nav.section-links');
    if (sidebarNav) {
        sidebarNav.addEventListener('click', (e) => {
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1) {
                return;
            }
            const link = e.target.closest('a');
            if (link && !link.href.startsWith('/docs/api')) {
                return;
            }
            if (link && sidebarNav.contains(link)) {
                sidebarNav.querySelectorAll('a.active-doc').forEach((a) => {
                    a.classList.remove('active-doc');
                });
                link.classList.add('active-doc');
            }
            const target = e.target instanceof Element ? e.target : e.target.parentElement;
            const li = target?.closest('li');
            if (!li || !sidebarNav.contains(li)) {
                return;
            }
            if (target?.closest('a')) {
                return;
            }
            if (!li.querySelector('ul')) {
                return;
            }
            e.preventDefault();
            li.classList.toggle('opened');
        });
    }
}

function navigateAway() {
    document.getElementById('article-container').classList.add('opacity-0');
    document.documentElement.scrollIntoView({ behavior: "instant", block: "start" });
    document.getElementById('loading-skeleton').classList.remove('hidden');
}

function initDoc() {
    initNav();
    refreshCodeBlocks(document.getElementById('article-container') || document);
    bindCodeBlockResize();
    tabGroups.init();
    // document.getElementById('loading-skeleton').classList.add('hidden');
    // document.getElementById('article-container').classList.remove('opacity-0');

    spyScrolling();

    // fix links to API reference pages
    window.name = 'muxi-docs';
    document.querySelectorAll('a[href*="/api/"]').forEach((ele) => {
        ele.setAttribute('target', '_blank');
        ele.setAttribute('hx-boost', 'false');
        ele.addEventListener('click', (e) => {
            e.preventDefault();
            // window.location = ele.getAttribute('href').replace('/reference/api/', '/api/');
            window.open(ele.getAttribute('href').replace('/reference/api/', '/api/'), 'muxi-api');
        });
    });
}

onDocReady(() => {
    initDoc();
    document.getElementById('article-container').classList.remove('opacity-0');

    // Mermaid initialization handled in layout module

    if (!window.__docsState.docHandlersBound) {
        window.__docsState.docHandlersBound = true;
        document.body.addEventListener('click', (e) => {
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1) {
                return;
            }
            const link = e.target.closest('.prev-next-nav a, aside.docs-sidebar nav.section-links li a:not([href^="#"]):not([target])');
            if (!link) {
                return;
            }
            navigateAway();
        });
        // Re-render Mermaid + code blocks after HTMX swaps content
        document.body.addEventListener('htmx:afterSwap', function (evt) {
            if (evt.detail.target.id === 'article-container' || evt.detail.target.closest('#article-container')) {
                onDocReady(() => {
                    // const container = evt.detail.target.closest('#article-container') || evt.detail.target;
                    initNav()
                    spyScrolling();
                    refreshCodeBlocks();
                    tabGroups.init();
                    // Mermaid re-render handled in layout module
                    document.getElementById('loading-skeleton').classList.add('hidden');
                    document.getElementById('article-container').classList.remove('opacity-0');
                }, 100);
            }
        });
    }
}, 100)
