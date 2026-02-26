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

    // ---- SEARCH FORM ----
    $('#heroSearchForm').on('submit', function (e) {
        e.preventDefault();
        const query = $('#heroSearch').val().trim();
        if (query) {
            window.location.href = `explore.php?search=${encodeURIComponent(query)}`;
        }
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

    const mapContainer = document.getElementById('fullMap') || document.getElementById('interactiveMap');
    if (!mapContainer) return;

    map = new google.maps.Map(mapContainer, {
        center: sanEnrique,
        zoom: 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        styles: mapStyles,
        gestureHandling: 'cooperative'
    });

    infoWindow = new google.maps.InfoWindow();

    // Load markers from PHP-injected data
    if (typeof mapListings !== 'undefined') {
        mapListings.forEach(function (listing) {
            addMarker(listing);
        });
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
        category: listing.cat_slug
    });

    const img = listing.featured_image || `https://placehold.co/280x120/1b4332/ffffff?text=${encodeURIComponent(listing.name)}`;

    const infoContent = `
    <div style="font-family:'Nunito',sans-serif;max-width:250px;padding:4px;">
      <img src="${img}" style="width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
      <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:${color};margin-bottom:3px;">
        <i class="${listing.icon}" style="margin-right:3px;"></i> ${listing.category_name}
      </div>
      <div style="font-family:'Playfair Display',serif;font-size:0.92rem;font-weight:700;color:#1b4332;margin-bottom:4px;">${listing.name}</div>
      <div style="font-size:0.78rem;color:#5a7564;margin-bottom:8px;">${listing.address || ''}</div>
      <a href="listing.php?slug=${listing.slug}"
         style="display:inline-block;background:#1b4332;color:white;font-size:0.75rem;font-weight:700;padding:5px 12px;border-radius:6px;text-decoration:none;">
         View Details &rarr;
      </a>
    </div>`;

    marker.addListener('click', function () {
        infoWindow.setContent(infoContent);
        infoWindow.open(map, marker);
        map.panTo(marker.getPosition());
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
    const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
    const detailMap = new google.maps.Map(document.getElementById('detailMap'), {
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
}

// Admin map picker
function initAdminMapPicker(lat, lng) {
    const pos = { lat: parseFloat(lat) || 10.9178, lng: parseFloat(lng) || 122.8845 };
    const adminMap = new google.maps.Map(document.getElementById('adminMapPicker'), {
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
        $('#latitude').val(position.lat().toFixed(8));
        $('#longitude').val(position.lng().toFixed(8));
    }

    updateCoords(marker.getPosition());

    marker.addListener('dragend', function () {
        updateCoords(marker.getPosition());
    });

    adminMap.addListener('click', function (e) {
        marker.setPosition(e.latLng);
        updateCoords(e.latLng);
    });
}

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
