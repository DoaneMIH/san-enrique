/* ===================================================
   San Enrique Tourism Hub — Live Update Poller
   Checks api/changes.php every 30 s. When the admin
   publishes new content, a non-intrusive toast appears
   giving the visitor the option to refresh.
   =================================================== */

(function () {
    'use strict';

    // ── Config ──────────────────────────────────────────
    const POLL_INTERVAL = 30000;   // 30 seconds
    const API_URL = (function () {
        // Works whether the script is loaded from root or a sub-folder
        const base = document.querySelector('meta[name="site-base"]');
        return base ? base.content.replace(/\/$/, '') + '/api/changes.php'
            : '/san-enrique/api/changes.php';
    })();

    // ── State ────────────────────────────────────────────
    let knownTimestamp = null;
    let toastShown = false;
    let timer = null;

    // ── Toast markup ─────────────────────────────────────
    function createToast() {
        const el = document.createElement('div');
        el.id = 'liveUpdateToast';
        el.innerHTML =
            '<div class="lut-icon"><i class="fas fa-sync-alt"></i></div>' +
            '<div class="lut-body">' +
            '  <div class="lut-title">New content available!</div>' +
            '  <div class="lut-sub">The admin just updated the page.</div>' +
            '</div>' +
            '<div class="lut-actions">' +
            '  <button class="lut-btn-refresh" id="lutRefreshBtn">Refresh</button>' +
            '  <button class="lut-btn-dismiss" id="lutDismissBtn" title="Dismiss">✕</button>' +
            '</div>';
        document.body.appendChild(el);

        // Animate in
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { el.classList.add('lut-visible'); });
        });

        document.getElementById('lutRefreshBtn').addEventListener('click', function () {
            window.location.reload();
        });
        document.getElementById('lutDismissBtn').addEventListener('click', function () {
            hideToast();
            // After dismiss, wait 5 minutes before showing again
            toastShown = true;
            setTimeout(function () { toastShown = false; }, 5 * 60 * 1000);
        });
    }

    function showToast() {
        if (toastShown) return;
        toastShown = true;
        if (!document.getElementById('liveUpdateToast')) {
            createToast();
        } else {
            document.getElementById('liveUpdateToast').classList.add('lut-visible');
        }

        // Auto-refresh countdown (60 s) displayed in the sub-text
        let countdown = 60;
        const subEl = document.querySelector('.lut-sub');
        const countTimer = setInterval(function () {
            countdown--;
            if (subEl) {
                subEl.textContent = countdown > 0
                    ? 'Auto-refreshing in ' + countdown + ' s…'
                    : 'Refreshing…';
            }
            if (countdown <= 0) {
                clearInterval(countTimer);
                window.location.reload();
            }
        }, 1000);

        // Store so dismiss can clear it
        document.getElementById('lutDismissBtn').addEventListener('click', function () {
            clearInterval(countTimer);
        }, { once: true });
    }

    function hideToast() {
        const el = document.getElementById('liveUpdateToast');
        if (el) {
            el.classList.remove('lut-visible');
            setTimeout(function () { el.remove(); }, 400);
        }
    }

    // ── Polling ──────────────────────────────────────────
    function poll() {
        fetch(API_URL, { cache: 'no-store' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                const ts = data.timestamp;
                if (knownTimestamp === null) {
                    // First check — just record the baseline
                    knownTimestamp = ts;
                } else if (ts > knownTimestamp) {
                    // Content changed — show toast
                    knownTimestamp = ts;
                    showToast();
                }
            })
            .catch(function () {
                // Silently ignore network errors (e.g. offline)
            });
    }

    // ── Boot ─────────────────────────────────────────────
    function start() {
        poll();                              // immediate first check
        timer = setInterval(poll, POLL_INTERVAL);
    }

    // Start after page fully loads so it doesn't compete with page resources
    if (document.readyState === 'complete') {
        start();
    } else {
        window.addEventListener('load', start);
    }

    // Pause polling when tab is hidden, resume when visible
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearInterval(timer);
        } else {
            poll();
            timer = setInterval(poll, POLL_INTERVAL);
        }
    });

})();
