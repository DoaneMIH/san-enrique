/* ============================================================
   San Enrique Tourism Hub  —  Live Update Engine  v3.0
   ============================================================
   Architecture:
     1. Poll api/changes.php every POLL_MS milliseconds.
     2. Compare per-section fingerprints against last known set.
     3. For changed sections, fetch only the HTML fragment from
        api/content.php and surgically swap it into the DOM.
     4. No full-page refetch, no innerHTML wipe, no flicker.
        Uses requestAnimationFrame + opacity transition for
        butter-smooth swaps the user barely notices.

   Pages covered:
     • index.php       — featured, categories, events, stats
     • explore.php     — listings grid
     • listing.php     — gallery slides/thumbs, reviews
     • admin/*         — dashboard stat badges, message badge
   ============================================================ */
(function () {
    'use strict';

    /* ── Config ─────────────────────────────────────── */
    var POLL_MS      = 12000;   // poll every 12 s
    var FADE_MS      = 280;     // cross-fade duration (ms)
    var BASE_URL     = (function () {
        var m = document.querySelector('meta[name="site-base"]');
        return m ? m.content.replace(/\/+$/, '') : '';
    })();
    var CHANGES_URL  = BASE_URL + '/api/changes.php';
    var CONTENT_URL  = BASE_URL + '/api/content.php';

    /* ── State ──────────────────────────────────────── */
    var knownFp    = {};        // fingerprint → known hash
    var timer      = null;
    var busy       = false;

    /* ── Detect which page we're on ─────────────────── */
    var PAGE = (function () {
        var p = window.location.pathname;
        if (/\/admin\//.test(p))      return 'admin';
        if (/listing\.php/.test(p))   return 'listing';
        if (/explore\.php/.test(p))   return 'explore';
        return 'index'; // index.php or root
    })();

    /* ── Listing slug (for listing.php) ─────────────── */
    var LISTING_SLUG = (function () {
        var m = window.location.search.match(/[?&]slug=([^&]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    })();

    /* ── Explore filters (for explore.php) ──────────── */
    var EXPLORE_CAT    = '';
    var EXPLORE_SEARCH = '';
    (function () {
        var m = window.location.search.match(/[?&]category=([^&]*)/);
        if (m) EXPLORE_CAT = decodeURIComponent(m[1]);
        m = window.location.search.match(/[?&]search=([^&]*)/);
        if (m) EXPLORE_SEARCH = decodeURIComponent(m[1]);
    })();

    /* ══════════════════════════════════════════════════
       CORE: seamless DOM swap with cross-fade
       ══════════════════════════════════════════════════ */
    function fadeSwap(container, newHtml, onDone) {
        if (!container) { if (onDone) onDone(); return; }

        // 1. Snapshot current height to prevent layout jump
        var h = container.offsetHeight;
        container.style.minHeight = h + 'px';

        // 2. Fade out
        container.style.transition = 'opacity ' + FADE_MS + 'ms ease';
        container.style.opacity    = '0';

        setTimeout(function () {
            // 3. Swap content while invisible
            container.innerHTML = newHtml;

            // 4. Re-trigger animate-on-scroll for any new elements
            container.querySelectorAll('.animate-on-scroll').forEach(function (el) {
                el.classList.remove('visible');
                if (el.getBoundingClientRect().top < window.innerHeight + 50) {
                    el.classList.add('visible');
                }
            });

            // 5. Re-bind category card clicks (index/explore pages)
            container.querySelectorAll('.category-card[data-slug]').forEach(function (el) {
                el.addEventListener('click', function () {
                    window.location.href = BASE_URL + '/explore.php?category=' + encodeURIComponent(el.dataset.slug);
                });
            });

            // 6. Fade back in
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    container.style.opacity    = '1';
                    container.style.minHeight  = '';
                    if (onDone) onDone();
                });
            });
        }, FADE_MS);
    }

    /* ══════════════════════════════════════════════════
       STAT COUNTER: smooth number roll-up
       ══════════════════════════════════════════════════ */
    function animateCount(el, target, duration) {
        if (!el) return;
        var start   = parseInt(el.textContent, 10) || 0;
        target      = parseInt(target, 10) || 0;
        if (start === target) return;
        var delta   = target - start;
        var startTs = null;

        function step(ts) {
            if (!startTs) startTs = ts;
            var prog = Math.min((ts - startTs) / duration, 1);
            var ease = 1 - Math.pow(1 - prog, 3); // ease-out-cubic
            el.textContent = Math.round(start + delta * ease);
            if (prog < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    /* ══════════════════════════════════════════════════
       SECTION UPDATERS — per section type
       ══════════════════════════════════════════════════ */

    function updateFeatured() {
        fetch(CONTENT_URL + '?type=featured', { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                var grid = document.getElementById('featuredGrid');
                if (grid) fadeSwap(grid, d.html);
            }).catch(function () {});
    }

    function updateCategories() {
        fetch(CONTENT_URL + '?type=categories', { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                // categories section inner .row
                var sec = document.getElementById('categories');
                if (!sec) return;
                var row = sec.querySelector('.row.g-4');
                if (row) fadeSwap(row, d.html);
            }).catch(function () {});
    }

    function updateEvents() {
        fetch(CONTENT_URL + '?type=events', { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                var grid = document.getElementById('eventsGrid');
                if (grid) fadeSwap(grid, d.html);
            }).catch(function () {});
    }

    function updateStats(data) {
        // data is the stats object from changes.php call (already fetched)
        fetch(CONTENT_URL + '?type=stats', { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                var s = d.data;
                animateCount(document.getElementById('statListings'),   s.listings,   800);
                animateCount(document.getElementById('statBarangays'),  s.barangays,  800);
                animateCount(document.getElementById('statEvents'),     s.events,     800);
                animateCount(document.getElementById('statCategories'), s.categories, 800);
            }).catch(function () {});
    }

    function updateExploreListings() {
        var url = CONTENT_URL + '?type=listings';
        if (EXPLORE_CAT)    url += '&category=' + encodeURIComponent(EXPLORE_CAT);
        if (EXPLORE_SEARCH) url += '&search='   + encodeURIComponent(EXPLORE_SEARCH);
        fetch(url, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                var container = document.getElementById('listingsContainer');
                if (!container) return;
                fadeSwap(container, d.html, function () {
                    // Update listing count label
                    var lc = document.getElementById('listingCount');
                    if (lc) {
                        lc.textContent = d.count + ' destination' + (d.count !== 1 ? 's' : '') + ' found';
                    }
                    // Re-bind explore search if present
                    var searchInput = document.getElementById('exploreSearch');
                    if (searchInput) {
                        searchInput.addEventListener('input', function () {
                            EXPLORE_SEARCH = searchInput.value;
                        });
                    }
                });
            }).catch(function () {});
    }

    function updateGallery() {
        if (!LISTING_SLUG) return;
        fetch(CONTENT_URL + '?type=gallery&slug=' + encodeURIComponent(LISTING_SLUG), { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success || !d.total) return;

                var slides  = document.getElementById('gcSlides');
                var thumbs  = document.getElementById('gcThumbs');
                var counter = document.getElementById('gcCounter');
                var stage   = document.getElementById('gcStage');
                if (!slides) return;

                // Only swap if count actually changed (avoid mid-view disruption)
                var currentTotal = parseInt(
                    (counter ? counter.textContent : '').split('/')[1] || '0', 10);
                if (currentTotal === d.total) return;

                // Update global JS vars referenced by gallery functions
                if (typeof gcPhotos !== 'undefined') {
                    window.gcPhotos = d.photos;
                    window.gcTotal  = d.total;
                    window.gcIdx    = 0;
                    window.gcLbIdx  = 0;
                }

                // Fade-swap slides
                if (slides) {
                    slides.style.transition = 'opacity ' + FADE_MS + 'ms ease';
                    slides.style.opacity    = '0';
                    setTimeout(function () {
                        slides.innerHTML   = d.slidesHtml;
                        slides.style.transform = 'translateX(0)';
                        slides.style.opacity   = '1';
                    }, FADE_MS);
                }

                // Thumbs
                if (thumbs && d.thumbsHtml) {
                    thumbs.style.opacity = '0';
                    setTimeout(function () {
                        thumbs.innerHTML   = d.thumbsHtml;
                        thumbs.style.opacity = '1';
                    }, FADE_MS);
                }

                // Counter
                if (counter) counter.textContent = '1 / ' + d.total;

                // Show/hide nav buttons
                if (stage) {
                    var prevBtn = stage.querySelector('.gc-btn.prev');
                    var nextBtn = stage.querySelector('.gc-btn.next');
                    if (prevBtn) prevBtn.style.display = d.total > 1 ? '' : 'none';
                    if (nextBtn) nextBtn.style.display = d.total > 1 ? '' : 'none';
                }
            }).catch(function () {});
    }

    function updateReviews() {
        if (!LISTING_SLUG) return;
        fetch(CONTENT_URL + '?type=reviews&slug=' + encodeURIComponent(LISTING_SLUG), { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                // Update review count badge if present
                var badge = document.querySelector('.review-count-badge, [data-review-count]');
                if (badge && typeof d.count !== 'undefined') {
                    badge.textContent = d.count + ' review' + (d.count !== 1 ? 's' : '');
                }
                // The review list itself is rendered server-side; only re-render
                // when count changes (a new review was submitted)
                var list = document.getElementById('reviewsList');
                if (!list) return;
                var currentCount = list.querySelectorAll('[data-review-id]').length;
                if (currentCount === d.count) return;

                // Build and swap review items
                var html = d.reviews.map(function (rv) {
                    var stars = '';
                    for (var s = 1; s <= 5; s++) {
                        stars += '<i class="fas fa-star" style="color:' + (s <= rv.rating ? '#d4a017' : '#ccc') + ';font-size:0.85rem;"></i>';
                    }
                    var dt = new Date(rv.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    return '<div class="review-item" data-review-id="' + rv.id + '">' +
                           '  <div class="review-header d-flex justify-content-between align-items-start">' +
                           '    <div><div class="reviewer-name fw-semibold">' + escHtml(rv.reviewer_name || 'Anonymous') + '</div>' +
                           '    <div class="review-stars">' + stars + '</div></div>' +
                           '    <div class="review-date text-muted small">' + dt + '</div>' +
                           '  </div>' +
                           '  <p class="review-text mt-2 mb-0">' + escHtml(rv.comment || '') + '</p>' +
                           '</div>';
                }).join('');
                fadeSwap(list, html);
            }).catch(function () {});
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ══════════════════════════════════════════════════
       ADMIN UPDATES
       ══════════════════════════════════════════════════ */
    function updateAdminBadges(fp) {
        // Update unread-message badge in topbar without re-rendering anything
        var badge = document.querySelector('.topbar-badge');
        var msgCount = parseInt(fp.admin_msgs, 10) || 0;
        if (badge) {
            var prev = parseInt(badge.textContent, 10) || 0;
            if (prev !== msgCount) {
                badge.textContent = Math.min(msgCount, 9);
                badge.style.display = msgCount > 0 ? '' : 'none';
                if (msgCount > prev) flashElement(badge); // new message arrived
            }
        } else if (msgCount > 0) {
            // Badge not present yet — create it
            var msgLink = document.querySelector('a[href*="messages.php"].topbar-icon-btn');
            if (msgLink) {
                var nb = document.createElement('span');
                nb.className = 'topbar-badge';
                nb.textContent = Math.min(msgCount, 9);
                msgLink.appendChild(nb);
            }
        }
    }

    /* ── Flash/pulse an element to draw attention ──── */
    function flashElement(el) {
        el.style.transition = 'transform 0.15s ease, box-shadow 0.15s ease';
        el.style.transform  = 'scale(1.35)';
        el.style.boxShadow  = '0 0 0 4px rgba(239,68,68,0.3)';
        setTimeout(function () {
            el.style.transform = '';
            el.style.boxShadow = '';
        }, 300);
    }

    /* ══════════════════════════════════════════════════
       POLL → DIFF → UPDATE
       ══════════════════════════════════════════════════ */
    function poll() {
        if (busy) return;
        busy = true;

        fetch(CHANGES_URL, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                busy = false;
                var fp = data.fingerprints || {};

                // First poll — just store fingerprints, nothing to diff yet
                if (Object.keys(knownFp).length === 0) {
                    knownFp = fp;
                    return;
                }

                // Diff fingerprints
                var changed = {};
                Object.keys(fp).forEach(function (key) {
                    if (knownFp[key] !== fp[key]) {
                        changed[key] = true;
                    }
                });

                // Nothing changed
                if (Object.keys(changed).length === 0) return;

                // Save new fingerprints
                knownFp = fp;

                // Dispatch updates per page context
                if (PAGE === 'index') {
                    if (changed.featured)    updateFeatured();
                    if (changed.categories)  updateCategories();
                    if (changed.events)      updateEvents();
                    if (changed.stats)       updateStats();
                }

                if (PAGE === 'explore') {
                    if (changed.listings || changed.categories) updateExploreListings();
                }

                if (PAGE === 'listing') {
                    if (changed.listings)    updateGallery();
                    if (changed.reviews)     updateReviews();
                }

                if (PAGE === 'admin') {
                    updateAdminBadges(fp);
                    // Admin pages that have their own fine-grained pollers
                    // (messages.php) keep their existing logic — we only
                    // update the badge count here from changes.php.
                }

            })
            .catch(function () { busy = false; });
    }

    /* ── Pause/resume when tab is hidden ────────────── */
    function start() {
        poll(); // immediate first poll
        timer = setInterval(poll, POLL_MS);
    }

    function stop() {
        clearInterval(timer);
        timer = null;
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stop();
        } else {
            // Resume and poll immediately so returning users
            // see fresh content without waiting POLL_MS
            start();
        }
    });

    /* ── Boot ───────────────────────────────────────── */
    if (document.readyState === 'complete') {
        start();
    } else {
        window.addEventListener('load', start);
    }

    /* ── Public API (for admin pages that want to force a poll) ── */
    window.liveUpdate = {
        poll:  poll,
        start: start,
        stop:  stop,
        page:  PAGE,
    };

})();
