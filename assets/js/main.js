/* ===================================================
   San Enrique Tourism Hub - Main JavaScript
   =================================================== */

$(document).ready(function () {

    // ---- PAGE LOADER ----
    setTimeout(function () {
        $('#pageLoader').addClass('hidden');
        setTimeout(() => $('#pageLoader').remove(), 600);
    }, 1200);

    // ---- NAVBAR SCROLL ----
    $(window).on('scroll', function () {
        const scrollTop = $(this).scrollTop();
        if (scrollTop > 60) {
            $('.navbar-main').addClass('scrolled');
            $('#backToTop').addClass('visible');
        } else {
            $('.navbar-main').removeClass('scrolled');
            $('#backToTop').removeClass('visible');
        }

        // Active nav link on scroll
        $('section[id]').each(function () {
            const sectionTop = $(this).offset().top - 100;
            const sectionId = $(this).attr('id');
            if (scrollTop >= sectionTop) {
                $('.nav-link-main').removeClass('active');
                $(`.nav-link-main[href="#${sectionId}"]`).addClass('active');
            }
        });
    });

    // ---- BACK TO TOP ----
    $('#backToTop').on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 600, 'swing');
    });

    // ---- ANIMATE ON SCROLL ----
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

    // ---- SMOOTH SCROLL for anchor links ----
    $('a[href^="#"]').on('click', function (e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: target.offset().top - 80 }, 700);
        }
    });

    // ---- COUNTER ANIMATION ----
    function animateCounter(el) {
        const target = parseInt($(el).data('target'));
        const duration = 1500;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            $(el).text(Math.floor(current) + ($(el).data('suffix') || ''));
        }, 16);
    }

    const counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.count-up').forEach(el => counterObserver.observe(el));

    // ---- PARTICLE GENERATION ----
    function createParticles() {
        const container = $('.hero-particles');
        if (!container.length) return;
        for (let i = 0; i < 20; i++) {
            const particle = $('<div class="particle"></div>');
            particle.css({
                left: Math.random() * 100 + '%',
                top: Math.random() * 100 + '%',
                animationDelay: Math.random() * 5 + 's',
                animationDuration: (5 + Math.random() * 6) + 's',
                width: (2 + Math.random() * 4) + 'px',
                height: (2 + Math.random() * 4) + 'px'
            });
            container.append(particle);
        }
    }
    createParticles();

    // ---- CATEGORY FILTER on Explore/Map page ----
    $(document).on('click', '.filter-pill[data-category]', function () {
        $('.filter-pill').removeClass('active');
        $(this).addClass('active');
        const cat = $(this).data('category');
        filterMapMarkers(cat);
    });

    // ---- CATEGORY CARD click (home page) ----
    $(document).on('click', '.category-card', function () {
        const cat = $(this).data('slug');
        window.location.href = `explore.php?category=${cat}`;
    });

    // ---- HERO LIVE SEARCH ----
    let searchTimer;
    $('#heroSearch').on('input', function () {
        clearTimeout(searchTimer);
        const query = $(this).val().trim();
        const dropdown = $('#heroSearchDropdown');

        if (!query) {
            dropdown.removeClass('show');
            return;
        }

        searchTimer = setTimeout(() => {
            $.ajax({
                url: 'api/search_listings.php',
                data: { q: query },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        dropdown.empty();
                        if (res.data.length > 0) {
                            res.data.forEach(item => {
                                const html = `
                                    <a href="listing.php?slug=${item.slug}" class="search-dropdown-item">
                                        <img src="${item.image}" alt="${item.name}" class="search-dropdown-img">
                                        <div class="search-dropdown-info">
                                            <h4 class="search-dropdown-title">${item.name}</h4>
                                            <div class="search-dropdown-meta">
                                                <span style="color:${item.color}"><i class="${item.icon}"></i> ${item.category_name}</span>
                                                ${item.barangay ? `<span><i class="fas fa-map-marker-alt"></i> ${item.barangay}</span>` : ''}
                                            </div>
                                        </div>
                                    </a>
                                `;
                                dropdown.append(html);
                            });
                        } else {
                            dropdown.html(`
                                <div class="search-empty">
                                    <i class="fas fa-search"></i>
                                    <h5>No destinations found</h5>
                                    <p>Try adjusting your search terms.</p>
                                </div>
                            `);
                        }
                        dropdown.addClass('show');
                    }
                }
            });
        }, 200);
    });

    // Close dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#heroSearchForm').length) {
            $('#heroSearchDropdown').removeClass('show');
        }
    });

    $('#heroSearchForm').on('submit', function (e) {
        e.preventDefault();
        const query = $('#heroSearch').val().trim();
        if (query) {
            window.location.href = `explore.php?search=${encodeURIComponent(query)}`;
        }
    });

    // ---- EXPLORE LIVE SEARCH ----
    let exploreTimer;
    $('#exploreSearch').on('input', function () {
        clearTimeout(exploreTimer);
        const query = $(this).val();
        const category = $('input[name="category"]').val();

        // Update URL to reflect current search visually without reloading
        const newUrl = new URL(window.location);
        if (query.trim()) {
            newUrl.searchParams.set('search', query.trim());
        } else {
            newUrl.searchParams.delete('search');
        }
        window.history.replaceState({}, '', newUrl);

        exploreTimer = setTimeout(() => {
            $.ajax({
                url: 'api/search_listings.php',
                data: { q: query.trim(), c: category },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        const container = $('#listingsContainer');
                        if (res.data.length > 0) {
                            let html = '<div class="row g-4" id="listingsGrid">';
                            res.data.forEach((item, i) => {
                                const delayClass = `delay-${(i % 4) + 1}`;
                                html += `
                                <div class="col-md-6 col-xl-4 animate-on-scroll ${delayClass} visible">
                                  <div class="listing-card">
                                    <div class="listing-card-img">
                                      <img src="${item.image}" alt="${item.name}" loading="lazy" onerror="this.src='https://placehold.co/600x400/1b4332/ffffff?text=No+Image'">
                                      <div class="listing-badge" style="color:${item.color}">
                                        <i class="${item.icon}"></i> ${item.category_name}
                                      </div>
                                      ${item.is_featured ? '<div class="featured-badge">★ Featured</div>' : ''}
                                    </div>
                                    <div class="listing-card-body">
                                      <h3 class="listing-card-title">${item.name}</h3>
                                      <p class="listing-card-desc">${item.description}</p>
                                      <div class="listing-card-meta">
                                        ${item.barangay ? `<span><i class="fas fa-map-marker-alt"></i> ${item.barangay}</span>` : ''}
                                        ${item.entrance_fee ? `<span><i class="fas fa-ticket-alt"></i> ${item.entrance_fee}</span>` : ''}
                                      </div>
                                      <a href="listing.php?slug=${item.slug}" class="btn-card">
                                        View Details <i class="fas fa-arrow-right"></i>
                                      </a>
                                    </div>
                                  </div>
                                </div>`;
                            });
                            html += '</div>';
                            container.html(html);
                        } else {
                            container.html(`
                              <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h4 style="color:var(--primary);margin-bottom:0.5rem;">No destinations found</h4>
                                <p>Try adjusting your search or filter criteria.</p>
                                <a href="explore.php" class="btn-primary-main mt-3">Clear Filters</a>
                              </div>
                            `);
                        }
                    }
                }
            });
        }, 200);
    });

    // ---- CONTACT FORM ----
    $('#contactForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('.btn-submit');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');

        $.ajax({
            url: 'api/contact.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Message Sent!',
                        text: 'Thank you for reaching out. We will get back to you soon.',
                        confirmButtonColor: '#1b4332',
                        confirmButtonText: 'Great!'
                    });
                    $('#contactForm')[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: res.message || 'Something went wrong. Please try again.',
                        confirmButtonColor: '#1b4332'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to send message. Please try again later.',
                    confirmButtonColor: '#1b4332'
                });
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Send Message');
            }
        });
    });

    // ---- REVIEW FORM ----
    $('#reviewForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');

        $.ajax({
            url: 'api/review.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Review Submitted!',
                        text: 'Thank you for sharing your experience.',
                        confirmButtonColor: '#1b4332'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, confirmButtonColor: '#1b4332' });
                }
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fas fa-star me-2"></i>Submit Review');
            }
        });
    });

    // ---- STAR RATING ----
    $(document).on('mouseenter', '.star-input', function () {
        const val = $(this).val();
        $('.star-input').each(function () {
            $(this).toggleClass('hovered', parseInt($(this).val()) <= parseInt(val));
        });
    }).on('mouseleave', '.star-input', function () {
        updateStarDisplay();
    }).on('change', '.star-input', function () {
        updateStarDisplay();
    });

    function updateStarDisplay() {
        const selected = $('input[name="rating"]:checked').val() || 0;
        $('.star-label').each(function () {
            const val = $(this).prev('input').val();
            $(this).toggleClass('selected', parseInt(val) <= parseInt(selected));
        });
    }

});

/* ===================================================
   GOOGLE MAPS INTEGRATION
   =================================================== */

let map, markers = [], infoWindow;

function initMap() {
    const sanEnrique = { lat: 10.9178, lng: 122.8845 };

    // ---- Full-page map (map.php uses #fullMap) ----
    // ---- Homepage section map uses #interactiveMap ----
    const mapEl = document.getElementById('fullMap') || document.getElementById('interactiveMap');

    if (mapEl) {
        // Ensure the element has an explicit pixel height — required for Google Maps to render
        if (mapEl.offsetHeight === 0) {
            mapEl.style.height = mapEl.id === 'fullMap' ? '100vh' : '600px';
        }

        map = new google.maps.Map(mapEl, {
            center: sanEnrique,
            zoom: 13,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            styles: mapStyles,
            gestureHandling: 'cooperative'
        });

        infoWindow = new google.maps.InfoWindow();

        // Load markers
        if (typeof mapListings !== 'undefined' && mapListings.length > 0) {
            mapListings.forEach(function (listing) {
                addMarker(listing);
            });
        }

        // Trigger resize to fix blank/grey tile issue after DOM settles
        setTimeout(function () {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(sanEnrique);
        }, 400);
    }

    // ---- Detail page single marker map ----
    if (typeof detailMapData !== 'undefined' && detailMapData) {
        initDetailMap(detailMapData.lat, detailMapData.lng, detailMapData.name);
    }

    // ---- Admin GPS picker map ----
    if (typeof adminMapData !== 'undefined' && adminMapData) {
        initAdminMapPicker(adminMapData.lat, adminMapData.lng);
    }
}

function addMarker(listing) {
    if (!listing.latitude || !listing.longitude) return;

    const color = listing.color || '#2d6a4f';

    const svgIcon = {
        path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 2,
        scale: 1.8,
        anchor: new google.maps.Point(12, 22)
    };

    const marker = new google.maps.Marker({
        position: { lat: parseFloat(listing.latitude), lng: parseFloat(listing.longitude) },
        map: map,
        title: listing.name,
        icon: svgIcon,
        animation: google.maps.Animation.DROP,
        category: listing.cat_slug,
        listingId: listing.id   // stored so map.php sidebar can find markers by id
    });

    const img = listing.featured_image
        ? listing.featured_image
        : 'https://placehold.co/280x120/1b4332/ffffff?text=' + encodeURIComponent(listing.name);

    const directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + listing.latitude + ',' + listing.longitude;

    const infoContent =
        '<div style="font-family:\'Nunito\',sans-serif;max-width:250px;padding:4px;">' +
        '<img src="' + img + '" style="width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;" onerror="this.style.display=\'none\'">' +
        '<div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:' + color + ';margin-bottom:3px;">' +
        '<i class="' + listing.icon + '" style="margin-right:3px;"></i> ' + listing.category_name +
        '</div>' +
        '<div style="font-family:\'Playfair Display\',serif;font-size:0.92rem;font-weight:700;color:#1b4332;margin-bottom:4px;">' + listing.name + '</div>' +
        '<div style="font-size:0.78rem;color:#5a7564;margin-bottom:8px;">' + (listing.address || '') + '</div>' +
        '<a href="listing.php?slug=' + listing.slug + '" style="display:inline-block;background:#1b4332;color:white;font-size:0.75rem;font-weight:700;padding:5px 12px;border-radius:6px;text-decoration:none;margin-right:4px;">View Details &rarr;</a>' +
        '<a href="' + directionsUrl + '" target="_blank" style="display:inline-block;background:#2d6a4f;color:white;font-size:0.75rem;font-weight:700;padding:5px 12px;border-radius:6px;text-decoration:none;">Directions</a>' +
        '</div>';

    marker.addListener('click', function () {
        infoWindow.setContent(infoContent);
        infoWindow.open(map, marker);
        map.panTo(marker.getPosition());
        // Highlight sidebar item on map.php if present
        var items = document.querySelectorAll('.map-listing-item');
        items.forEach(function (el) { el.classList.remove('active'); });
        var activeItem = document.querySelector('.map-listing-item[data-id="' + listing.id + '"]');
        if (activeItem) {
            activeItem.classList.add('active');
            activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    markers.push(marker);
}

function filterMapMarkers(category) {
    markers.forEach(function (marker) {
        if (category === 'all' || marker.category === category) {
            marker.setVisible(true);
        } else {
            marker.setVisible(false);
        }
    });
    infoWindow.close();
}

// Detail page single marker
function initDetailMap(lat, lng, name) {
    const detailEl = document.getElementById('detailMap');
    if (!detailEl) return;
    const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
    const detailMap = new google.maps.Map(detailEl, {
        center: pos, zoom: 15,
        mapTypeControl: false,
        streetViewControl: false,
        styles: mapStyles
    });

    new google.maps.Marker({
        position: pos,
        map: detailMap,
        title: name,
        animation: google.maps.Animation.DROP,
        icon: {
            path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
            fillColor: '#1b4332',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            scale: 2,
            anchor: new google.maps.Point(12, 22)
        }
    });

    setTimeout(function () {
        google.maps.event.trigger(detailMap, 'resize');
        detailMap.setCenter(pos);
    }, 300);
}

// Admin map picker
function initAdminMapPicker(lat, lng) {
    const pickerEl = document.getElementById('adminMapPicker');
    if (!pickerEl) return;
    const pos = { lat: parseFloat(lat) || 10.9178, lng: parseFloat(lng) || 122.8845 };
    const adminMap = new google.maps.Map(pickerEl, {
        center: pos, zoom: 13,
        mapTypeControl: false,
        streetViewControl: false
    });

    const marker = new google.maps.Marker({
        position: pos,
        map: adminMap,
        draggable: true,
        animation: google.maps.Animation.DROP
    });

    function updateCoords(position) {
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');
        if (latEl) latEl.value = position.lat().toFixed(8);
        if (lngEl) lngEl.value = position.lng().toFixed(8);
    }

    updateCoords(marker.getPosition());

    marker.addListener('dragend', function () {
        updateCoords(marker.getPosition());
    });

    adminMap.addListener('click', function (e) {
        marker.setPosition(e.latLng);
        updateCoords(e.latLng);
    });

    // Trigger resize twice — once early, once after any CSS transitions settle
    setTimeout(function () {
        google.maps.event.trigger(adminMap, 'resize');
        adminMap.setCenter(pos);
    }, 300);
    setTimeout(function () {
        google.maps.event.trigger(adminMap, 'resize');
        adminMap.setCenter(pos);
    }, 800);
}

/* ===================================================
   LIVE UPDATE MODULE
   Polls api/changes.php every 15 s.
   If the DB timestamp is newer than what PHP embedded
   on page load, fetches fresh content from api/content.php
   and smoothly patches the relevant DOM sections.
   =================================================== */
(function () {
    'use strict';

    // PHP embeds window.pageLoadTimestamp and window.liveUpdateConfig
    // via an inline <script> in index.php / explore.php.
    if (typeof window.pageLoadTimestamp === 'undefined') return;

    var lastKnownTs = window.pageLoadTimestamp;
    var config = window.liveUpdateConfig || { page: 'home' };
    var pollInterval = 15000; // 15 seconds
    var timer = null;
    var isUpdating = false;

    /* ---- Toast notification ---- */
    function showToast(msg) {
        var existing = document.getElementById('liveUpdateToast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'liveUpdateToast';
        toast.innerHTML =
            '<i class="fas fa-sync-alt me-2" style="animation:spin .6s linear 1;"></i>' + msg;
        Object.assign(toast.style, {
            position: 'fixed',
            bottom: '24px',
            right: '24px',
            background: 'linear-gradient(135deg,#2d6a4f,#52b788)',
            color: 'white',
            padding: '10px 20px',
            borderRadius: '40px',
            fontSize: '0.82rem',
            fontWeight: '700',
            fontFamily: "'Nunito', sans-serif",
            boxShadow: '0 4px 20px rgba(27,67,50,.35)',
            zIndex: '99999',
            display: 'flex',
            alignItems: 'center',
            opacity: '0',
            transform: 'translateY(12px)',
            transition: 'opacity .3s, transform .3s'
        });
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(12px)';
            setTimeout(function () { toast.remove(); }, 350);
        }, 3000);
    }

    /* ---- Fade-swap helper: replace el innerHTML then fade in ---- */
    function fadeSwap(el, newHtml, callback) {
        if (!el) return;
        el.style.transition = 'opacity .3s';
        el.style.opacity = '0';
        setTimeout(function () {
            el.innerHTML = newHtml;
            el.style.opacity = '1';
            if (callback) callback(el);
        }, 300);
    }

    /* ---- Render helpers ---- */

    function renderFeaturedCard(l) {
        var img = l.image_url ||
            'https://placehold.co/600x400/1b4332/ffffff?text=' + encodeURIComponent(l.name);
        return '<div class="listing-card">' +
            '<div class="listing-card-img">' +
            '<img src="' + img + '" alt="' + escHtml(l.name) +
            '" loading="lazy" onerror="this.src=\'https://placehold.co/600x400/1b4332/ffffff?text=No+Image\'">' +
            '<div class="listing-badge" style="color:' + escHtml(l.color) + '">' +
            '<i class="' + escHtml(l.icon) + '"></i> ' + escHtml(l.category_name) +
            '</div>' +
            (l.is_featured ? '<div class="featured-badge">★ Featured</div>' : '') +
            '</div>' +
            '<div class="listing-card-body">' +
            '<h3 class="listing-card-title">' + escHtml(l.name) + '</h3>' +
            '<p class="listing-card-desc">' + escHtml(l.description) + '</p>' +
            '<div class="listing-card-meta">' +
            (l.barangay ? '<span><i class="fas fa-map-marker-alt"></i> ' + escHtml(l.barangay) + '</span>' : '') +
            (l.entrance_fee ? '<span><i class="fas fa-ticket-alt"></i> ' + escHtml(l.entrance_fee) + '</span>' : '') +
            '</div>' +
            '<a href="listing.php?slug=' + encodeURIComponent(l.slug) + '" class="btn-card">' +
            'View Details <i class="fas fa-arrow-right"></i>' +
            '</a>' +
            '</div></div>';
    }

    function renderExploreCard(l, i) {
        var img = l.image_url ||
            'https://placehold.co/600x400/1b4332/ffffff?text=' + encodeURIComponent(l.name);
        var delay = 'delay-' + ((i % 4) + 1);
        return '<div class="col-md-6 col-xl-4 animate-on-scroll ' + delay + ' visible">' +
            '<div class="listing-card">' +
            '<div class="listing-card-img">' +
            '<img src="' + img + '" alt="' + escHtml(l.name) +
            '" loading="lazy" onerror="this.src=\'https://placehold.co/600x400/1b4332/ffffff?text=No+Image\'">' +
            '<div class="listing-badge" style="color:' + escHtml(l.color) + '">' +
            '<i class="' + escHtml(l.icon) + '"></i> ' + escHtml(l.category_name) +
            '</div>' +
            (l.is_featured ? '<div class="featured-badge">★ Featured</div>' : '') +
            '</div>' +
            '<div class="listing-card-body">' +
            '<h3 class="listing-card-title">' + escHtml(l.name) + '</h3>' +
            '<p class="listing-card-desc">' + escHtml(l.description) + '</p>' +
            '<div class="listing-card-meta">' +
            (l.barangay ? '<span><i class="fas fa-map-marker-alt"></i> ' + escHtml(l.barangay) + '</span>' : '') +
            (l.entrance_fee ? '<span><i class="fas fa-ticket-alt"></i> ' + escHtml(l.entrance_fee) + '</span>' : '') +
            '</div>' +
            '<a href="listing.php?slug=' + encodeURIComponent(l.slug) + '" class="btn-card">' +
            'View Details <i class="fas fa-arrow-right"></i>' +
            '</a>' +
            '</div></div></div>';
    }

    function renderEventCard(ev) {
        var date = formatDate(ev.event_date);
        return '<div class="event-card">' +
            '<div class="event-date-badge">' +
            '<i class="fas fa-calendar-alt"></i> ' + date +
            '</div>' +
            '<h4 class="event-title">' + escHtml(ev.title) + '</h4>' +
            '<p class="event-desc">' + escHtml((ev.description || '').substring(0, 120)) + '...</p>' +
            (ev.location ? '<div class="event-location"><i class="fas fa-map-pin"></i> ' + escHtml(ev.location) + '</div>' : '') +
            '</div>';
    }

    function formatDate(str) {
        if (!str) return '';
        var d = new Date(str.replace(' ', 'T'));
        var months = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ---- Update HOME page sections ---- */
    function updateHome(ts) {
        var updated = false;
        var remaining = 3; // featured + events + stats

        function done() {
            remaining--;
            if (remaining === 0 && updated) {
                showToast('Content Updated');
            }
            isUpdating = false;
        }

        // Featured Listings
        $.getJSON('api/content.php?type=featured', function (res) {
            if (res.success && res.data.length) {
                var grid = document.getElementById('featuredGrid');
                if (grid) {
                    var html = '';
                    res.data.forEach(function (l, i) {
                        html += '<div class="col-md-6 col-lg-4 animate-on-scroll visible">' +
                            renderFeaturedCard(l) + '</div>';
                    });
                    fadeSwap(grid, html);
                    updated = true;
                }
            }
        }).always(done);

        // Events
        $.getJSON('api/content.php?type=events', function (res) {
            if (res.success) {
                var grid = document.getElementById('eventsGrid');
                if (grid) {
                    var html = '';
                    if (res.data.length) {
                        res.data.forEach(function (ev, i) {
                            html += '<div class="col-md-4 animate-on-scroll visible">' +
                                renderEventCard(ev) + '</div>';
                        });
                    } else {
                        html = '<div class="col-12"><div class="empty-state">' +
                            '<i class="fas fa-calendar-times"></i>' +
                            '<p>No upcoming events at the moment. Check back soon!</p>' +
                            '</div></div>';
                    }
                    fadeSwap(grid, html);
                    updated = true;
                }
            }
        }).always(done);

        // Stats
        $.getJSON('api/content.php?type=stats', function (res) {
            if (res.success && res.data) {
                var d = res.data;
                var map = {
                    'statListings': d.listings,
                    'statBarangays': d.barangays,
                    'statEvents': d.events,
                    'statCategories': d.categories
                };
                Object.keys(map).forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el && parseInt(el.textContent) !== map[id]) {
                        el.style.transition = 'opacity .3s';
                        el.style.opacity = '0';
                        setTimeout(function () {
                            el.textContent = map[id];
                            el.style.opacity = '1';
                        }, 300);
                        updated = true;
                    }
                });
            }
        }).always(done);

        lastKnownTs = ts;
    }

    /* ---- Update EXPLORE page ---- */
    function updateExplore(ts) {
        var cat = config.category || '';
        var search = config.search || '';
        var url = 'api/content.php?type=listings' +
            (cat ? '&category=' + encodeURIComponent(cat) : '') +
            (search ? '&search=' + encodeURIComponent(search) : '');

        $.getJSON(url, function (res) {
            if (res.success) {
                var container = document.getElementById('listingsContainer');
                var countEl = document.getElementById('listingCount');
                if (container) {
                    var html = '';
                    if (res.data && res.data.length) {
                        html = '<div class="row g-4" id="listingsGrid">';
                        res.data.forEach(function (l, i) {
                            html += renderExploreCard(l, i);
                        });
                        html += '</div>';
                    } else {
                        html = '<div class="empty-state">' +
                            '<i class="fas fa-search"></i>' +
                            '<h4 style="color:var(--primary);margin-bottom:.5rem;">No destinations found</h4>' +
                            '<p>Try adjusting your search or filter criteria.</p>' +
                            '<a href="explore.php" class="btn-primary-main mt-3">Clear Filters</a>' +
                            '</div>';
                    }
                    fadeSwap(container, html);
                }
                if (countEl) {
                    var c = res.count || 0;
                    countEl.textContent = c + ' destination' + (c !== 1 ? 's' : '') + ' found';
                }
                showToast('Content Updated');
            }
        }).always(function () {
            isUpdating = false;
        });

        lastKnownTs = ts;
    }

    /* ---- Poll loop ---- */
    function poll() {
        if (isUpdating) return;
        if (document.hidden) return; // Don't poll when tab is hidden

        $.getJSON('api/changes.php', function (res) {
            if (res && res.timestamp && res.timestamp > lastKnownTs) {
                isUpdating = true;
                if (config.page === 'home') {
                    updateHome(res.timestamp);
                } else if (config.page === 'explore') {
                    updateExplore(res.timestamp);
                }
            }
        });
    }

    // Start polling after the page is fully rendered
    $(document).ready(function () {
        timer = setInterval(poll, pollInterval);
    });

    // Pause when tab is hidden, resume when visible
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            poll(); // immediate check on tab focus
        }
    });

}());

/* ---- CUSTOM MAP STYLES (nature/green theme) ---- */
const mapStyles = [
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#74c69d' }, { lightness: 30 }] },
    { featureType: 'landscape', elementType: 'geometry', stylers: [{ color: '#e8f5e9' }] },
    { featureType: 'road.highway', elementType: 'geometry.fill', stylers: [{ color: '#b7e4c7' }] },
    { featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [{ color: '#52b788' }, { lightness: -10 }] },
    { featureType: 'road.arterial', elementType: 'geometry', stylers: [{ color: '#d8f3dc' }] },
    { featureType: 'road.local', elementType: 'geometry', stylers: [{ color: '#f0f7f2' }] },
    { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#95d5b2' }] },
    { featureType: 'transit', elementType: 'geometry', stylers: [{ color: '#d8f3dc' }] },
    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#40916c' }, { lightness: 17 }, { weight: 1.2 }] },
    { elementType: 'labels.text.stroke', stylers: [{ color: '#ffffff' }, { lightness: 16 }] },
    { elementType: 'labels.text.fill', stylers: [{ saturation: 36 }, { color: '#1b4332' }, { lightness: 40 }] },
    { elementType: 'labels.icon', stylers: [{ visibility: 'off' }] }
];