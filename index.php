<?php
require_once 'includes/functions.php';
$db = getDB();
$categories = getCategories();
$featured = getFeaturedListings(6);
$events = getUpcomingEvents(3);
$stats = getStats();
$mapListings = getAllListingsForMap();

// Embed initial DB timestamp for the live-update poller
$initTs = 0;
foreach (['listings', 'categories', 'events'] as $_t) {
  $r = $db->query("SELECT UNIX_TIMESTAMP(MAX(created_at)) as t FROM `$_t`");
  if ($r) {
    $v = (int) $r->fetch_assoc()['t'];
    if ($v > $initTs)
      $initTs = $v;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SITE_NAME ?> - <?= SITE_TAGLINE ?></title>
  <meta name="description"
    content="Discover the hidden paradise of San Enrique, Iloilo. Explore resorts, cultural sites, local food, and beautiful barangays.">
  <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

  <!-- Fonts & Icons -->
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- PAGE LOADER -->
  <div id="pageLoader" class="page-loader">
    <div class="brand-logo"
      style="width:60px;height:60px;background:linear-gradient(135deg,#52b788,#d4a017);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2rem;">
      🌿</div>
    <div class="loader-logo"><?= SITE_NAME ?></div>
    <div class="loader-bar">
      <div class="loader-bar-fill"></div>
    </div>
  </div>

  <!-- BACK TO TOP -->
  <button id="backToTop" class="back-to-top" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
  </button>

  <!-- NAVBAR -->
  <nav class="navbar-main">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between w-100">
        <a href="index.php" class="navbar-brand-wrap text-decoration-none">
          <div class="brand-logo">🌿</div>
          <div class="brand-text-wrap">
            <div class="brand-name">San Enrique</div>
            <div class="brand-sub">Tourism Hub</div>
          </div>
        </a>
        <!-- Desktop Nav -->
        <div class="d-none d-lg-flex align-items-center gap-1">
          <a href="#home" class="nav-link-main active">Home</a>
          <a href="#categories" class="nav-link-main">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a>
          <a href="#events" class="nav-link-main">Events</a>
          <a href="#about" class="nav-link-main">About</a>
          <a href="#contact" class="nav-link-main">Contact</a>
          <!-- <a href="admin/login.php" class="btn-nav-admin ms-3">
          <i class="fas fa-shield-alt me-1"></i> Admin
        </a> -->
        </div>
        <!-- Mobile Toggle -->
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <!-- Mobile Nav -->
      <div class="collapse" id="mobileNav">
        <div class="d-flex flex-column gap-1 py-2">
          <a href="#home" class="nav-link-main">Home</a>
          <a href="#categories" class="nav-link-main">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a>
          <a href="#events" class="nav-link-main">Events</a>
          <a href="#about" class="nav-link-main">About</a>
          <a href="#contact" class="nav-link-main">Contact</a>
          <a href="admin/login.php" class="btn-nav-admin mt-2 text-center" style="max-width:140px;">
            <i class="fas fa-shield-alt me-1"></i> Admin
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section id="home" class="hero-section">
    <div class="hero-bg-pattern"></div>
    <div class="hero-particles"></div>
    
    <!-- Decorative floating shapes -->
    <div class="hero-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>

    <div class="container position-relative" style="z-index:2;padding-top:20px;">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="hero-content">
            <div class="hero-badge">
              <i class="fas fa-map-marker-alt"></i>
              <?= MUNICIPALITY ?>
            </div>
            <h1 class="hero-title">
              Discover the<br>
              <span class="highlight">Hidden Paradise</span><br>
              of San Enrique
            </h1>
            <p class="hero-subtitle">
              Your all-in-one guide to resorts, cultural heritage, local cuisine, agri-tourism, and vibrant barangays.
              Explore San Enrique like never before.
            </p>

            <!-- Search Bar -->
            <form id="heroSearchForm" class="search-hero-bar mb-4">
              <i class="fas fa-search" style="color:#5a7564;padding:0 0.5rem;"></i>
              <input type="text" id="heroSearch" placeholder="Search resorts, places, food..." autocomplete="off">
              <button type="submit"><i class="fas fa-arrow-right"></i> Explore</button>
            </form>

            <div class="hero-actions">
              <a href="explore.php" class="btn-hero-primary">
                <i class="fas fa-compass"></i> Explore Destinations
              </a>
              <a href="map.php" class="btn-hero-secondary">
                <i class="fas fa-map"></i> View Map
              </a>
            </div>

            <div class="hero-stats">
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['listings'] ?>"><?= $stats['listings'] ?></span>+</div>
                <div class="stat-label">Destinations</div>
              </div>
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['categories'] ?>"><?= $stats['categories'] ?></span></div>
                <div class="stat-label">Categories</div>
              </div>
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['barangays'] ?>"><?= $stats['barangays'] ?></span>+</div>
                <div class="stat-label">Barangays</div>
              </div>
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['events'] ?>"><?= $stats['events'] ?></span></div>
                <div class="stat-label">Events</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="hero-map-preview" style="position:relative;">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15702.12!2d122.8845!3d10.9178!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33afc56e00000001%3A0x1!2sSan%20Enrique%2C%20Iloilo!5e0!3m2!1sen!2sph!4v1700000000000!5m2!1sen!2sph"
              width="100%" height="420" style="border:0;display:block;border-radius:var(--radius-lg);"
              allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              title="San Enrique, Iloilo Map">
            </iframe>
            <!-- Open Full Map button overlay -->
            <a href="map.php" style="position:absolute;bottom:1.2rem;left:50%;transform:translateX(-50%);
                    background:var(--primary);color:white;padding:10px 24px;border-radius:10px;
                    font-weight:700;font-size:0.88rem;border:2px solid rgba(255,255,255,0.3);
                    text-decoration:none;display:inline-flex;align-items:center;gap:6px;
                    box-shadow:0 4px 16px rgba(26,58,110,0.4);white-space:nowrap;z-index:10;">
              <i class="fas fa-map-marked-alt"></i> Open Full Interactive Map
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="scroll-indicator">
      <span style="font-size:0.7rem;letter-spacing:0.1em;">SCROLL</span>
      <i class="fas fa-chevron-down"></i>
    </div>
  </section>

  <!-- CATEGORIES SECTION -->
  <section id="categories" class="categories-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="text-center mb-5 animate-on-scroll">
        <div class="section-label justify-content-center"><span></span>Browse by Category<span></span></div>
        <h2 class="section-title">What Would You Like to Explore?</h2>
        <p class="section-subtitle mx-auto">From pristine beaches to heritage sites, discover everything San Enrique has
          to offer.</p>
      </div>
      <div class="row g-4 align-items-stretch">
        <?php foreach ($categories as $i => $cat): ?>
          <div class="col-6 col-md-4 col-lg-2 animate-on-scroll delay-<?= ($i % 4) + 1 ?>">
            <div class="category-card" data-slug="<?= htmlspecialchars($cat['slug']) ?>" role="button"
              style="height:100%;min-height:170px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
              <div class="cat-icon"
                style="background: linear-gradient(135deg, <?= htmlspecialchars($cat['color']) ?>, <?= htmlspecialchars($cat['color']) ?>88);flex-shrink:0;">
                <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
              </div>
              <div class="cat-name" style="min-height:2.8em;display:flex;align-items:center;justify-content:center;">
                <?= htmlspecialchars($cat['name']) ?></div>
              <div class="cat-count">Explore &rarr;</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FEATURED LISTINGS -->
  <section id="featured" style="background:var(--gray-50);position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="d-flex align-items-end justify-content-between mb-5">
        <div class="animate-on-scroll">
          <div class="section-label">Featured Destinations</div>
          <h2 class="section-title mb-1">Must-Visit Places</h2>
          <p class="section-subtitle">Handpicked favorites by the San Enrique LGU</p>
        </div>
        <a href="explore.php" class="btn-outline-main d-none d-md-inline-flex animate-on-scroll">
          View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="row g-4" id="featuredGrid">
        <?php foreach ($featured as $i => $listing): ?>
          <div class="col-md-6 col-lg-4 animate-on-scroll delay-<?= ($i % 4) + 1 ?>">
            <div class="listing-card">
              <div class="listing-card-img">
                <img src="<?= htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 600, 400)) ?>"
                  alt="<?= htmlspecialchars($listing['name']) ?>" loading="lazy"
                  onerror="this.src='https://placehold.co/600x400/1b4332/ffffff?text=<?= urlencode($listing['name']) ?>'">
                <div class="listing-badge" style="color:<?= htmlspecialchars($listing['color']) ?>">
                  <i class="<?= htmlspecialchars($listing['icon']) ?>"></i>
                  <?= htmlspecialchars($listing['category_name']) ?>
                </div>
                <?php if ($listing['is_featured']): ?>
                  <div class="featured-badge">★ Featured</div>
                <?php endif; ?>
              </div>
              <div class="listing-card-body">
                <h3 class="listing-card-title"><?= htmlspecialchars($listing['name']) ?></h3>
                <p class="listing-card-desc"><?= htmlspecialchars($listing['description']) ?></p>
                <div class="listing-card-meta">
                  <?php if ($listing['barangay']): ?>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($listing['barangay']) ?></span>
                  <?php endif; ?>
                  <?php if ($listing['entrance_fee']): ?>
                    <span><i class="fas fa-ticket-alt"></i> <?= htmlspecialchars($listing['entrance_fee']) ?></span>
                  <?php endif; ?>
                </div>
                <a href="listing.php?slug=<?= urlencode($listing['slug']) ?>" class="btn-card">
                  View Details <i class="fas fa-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-4 d-md-none">
        <a href="explore.php" class="btn-primary-main">View All Destinations <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </section>

  <!-- MAP TEASER SECTION -->
  <section id="map-teaser" class="map-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="row align-items-center g-5 mb-4">
        <div class="col-lg-6 animate-on-scroll">
          <div class="section-label">Interactive Map</div>
          <h2 class="section-title">Find Any Destination<br>with Ease</h2>
          <p class="section-subtitle mb-3">
            Our Google Maps integration pins every resort, barangay, cultural site, and food spot. Filter by category
            and get real-time directions in one click.
          </p>
          <div class="map-filter-pills">
            <span class="filter-pill active" data-category="all">
              <i class="fas fa-map"></i> All
            </span>
            <?php foreach ($categories as $cat): ?>
              <span class="filter-pill" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                <i class="<?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
              </span>
            <?php endforeach; ?>
          </div>
          <a href="map.php" class="btn-primary-main mt-2">
            <i class="fas fa-map-marked-alt me-1"></i> Open Full Map
          </a>
        </div>
        <div class="col-lg-6 animate-on-scroll delay-2">
          <div id="interactiveMap"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- EVENTS SECTION -->
  <section id="events" class="events-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="d-flex align-items-end justify-content-between mb-5">
        <div class="animate-on-scroll">
          <div class="section-label">Upcoming Events</div>
          <h2 class="section-title mb-1">What's Happening in San Enrique</h2>
          <p class="section-subtitle">Join the celebrations and community events</p>
        </div>
      </div>
      <div class="row g-4" id="eventsGrid">
        <?php if ($events): ?>
          <?php foreach ($events as $i => $event): ?>
            <div class="col-md-4 animate-on-scroll delay-<?= $i + 1 ?>">
              <div class="event-card">
                <div class="event-date-badge">
                  <i class="fas fa-calendar-alt"></i>
                  <?= date('F j, Y', strtotime($event['event_date'])) ?>
                  <?php if ($event['end_date'] && $event['end_date'] !== $event['event_date']): ?>
                    – <?= date('F j', strtotime($event['end_date'])) ?>
                  <?php endif; ?>
                </div>
                <h4 class="event-title"><?= htmlspecialchars($event['title']) ?></h4>
                <p class="event-desc"><?= htmlspecialchars(substr($event['description'], 0, 120)) ?>...</p>
                <?php if ($event['location']): ?>
                  <div class="event-location">
                    <i class="fas fa-map-pin"></i> <?= htmlspecialchars($event['location']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="empty-state">
              <i class="fas fa-calendar-times"></i>
              <p>No upcoming events at the moment. Check back soon!</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- STATS SECTION -->
  <section class="stats-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="row g-0">
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll">
            <i class="fas fa-umbrella-beach stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statListings"
                data-target="<?= $stats['listings'] ?>"><?= $stats['listings'] ?></span>+</div>
            <div class="stat-title">Listed Destinations</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll delay-1">
            <i class="fas fa-home stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statBarangays"
                data-target="<?= $stats['barangays'] ?>"><?= $stats['barangays'] ?></span></div>
            <div class="stat-title">Barangays</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll delay-2">
            <i class="fas fa-calendar-alt stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statEvents"
                data-target="<?= $stats['events'] ?>"><?= $stats['events'] ?></span></div>
            <div class="stat-title">Annual Events</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll delay-3">
            <i class="fas fa-th-large stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statCategories"
                data-target="<?= $stats['categories'] ?>"><?= $stats['categories'] ?></span></div>
            <div class="stat-title">Categories</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section id="about" class="about-strip" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="row align-items-center g-5">
        <div class="col-lg-5 animate-on-scroll">
          <div class="about-img-wrap">
            <img src="assets/images/San Enrique LGU.png" alt="San Enrique" loading="lazy">
            <div class="about-highlight">
              <div
                style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">
                LGU Initiative</div>
              <div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--primary);font-weight:700;">
                Digital Tourism Platform</div>
              <div style="font-size:0.82rem;color:var(--text-muted);">Powered by San Enrique LGU</div>
            </div>
          </div>
        </div>
        <div class="col-lg-7 animate-on-scroll delay-2">
          <div class="section-label">About San Enrique</div>
          <h2 class="section-title">A Gem in the Heart<br>of Iloilo Province</h2>
          <p style="color:var(--text-muted);margin-bottom:1.5rem;line-height:1.8;">
            San Enrique is a municipality in the province of Iloilo, Philippines, known for its warm hospitality, rich
            cultural heritage, and breathtaking natural landscapes. This digital platform is an initiative by the Local
            Government Unit to promote local tourism and support the community.
          </p>
          <ul class="feature-list">
            <li>
              <div class="fi-icon"><i class="fas fa-map-marked-alt"></i></div>
              <div>
                <strong style="color:var(--primary);display:block;margin-bottom:2px;">GPS-Powered Navigation</strong>
                <span style="color:var(--text-muted);font-size:0.88rem;">Every listing is pinned with precise GPS
                  coordinates for easy navigation and real-time directions.</span>
              </div>
            </li>
            <li>
              <div class="fi-icon"><i class="fas fa-leaf"></i></div>
              <div>
                <strong style="color:var(--primary);display:block;margin-bottom:2px;">Eco & Agri Tourism</strong>
                <span style="color:var(--text-muted);font-size:0.88rem;">Supporting sustainable tourism with organic
                  farms, eco-trails, and community-led experiences.</span>
              </div>
            </li>
            <li>
              <div class="fi-icon"><i class="fas fa-landmark"></i></div>
              <div>
                <strong style="color:var(--primary);display:block;margin-bottom:2px;">Rich Cultural Heritage</strong>
                <span style="color:var(--text-muted);font-size:0.88rem;">Historic churches, traditional festivals, and
                  vibrant local arts celebrating Visayan culture.</span>
              </div>
            </li>
          </ul>
          <a href="explore.php" class="btn-primary-main mt-2">
            <i class="fas fa-compass me-1"></i> Explore San Enrique
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section id="contact" class="contact-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="text-center mb-5 animate-on-scroll">
        <div class="section-label justify-content-center"><span></span>Get In Touch<span></span></div>
        <h2 class="section-title">Contact San Enrique LGU</h2>
        <p class="section-subtitle mx-auto">Have questions or need assistance? Reach out to the Tourism Office.</p>
      </div>
      <div class="row g-4">
        <div class="col-lg-4 animate-on-scroll">
          <div class="contact-info-card">
            <h4 style="font-family:'Playfair Display',serif;color:white;font-size:1.2rem;margin-bottom:2rem;">Tourism
              Office Information</h4>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <div class="ci-label">Address</div>
                <div class="ci-value">Municipal Hall, Poblacion, San Enrique, Iloilo, Philippines</div>
              </div>
            </div>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-phone"></i></div>
              <div>
                <div class="ci-label">Phone</div>
                <div class="ci-value">(033) 123-4567</div>
              </div>
            </div>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-envelope"></i></div>
              <div>
                <div class="ci-label">Email</div>
                <div class="ci-value">tourism@sanenrique.gov.ph</div>
              </div>
            </div>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-clock"></i></div>
              <div>
                <div class="ci-label">Office Hours</div>
                <div class="ci-value">Mon–Fri, 8:00 AM – 5:00 PM</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-8 animate-on-scroll delay-2">
          <div class="contact-form-wrap">
            <h4
              style="font-family:'Playfair Display',serif;color:var(--primary);font-size:1.2rem;margin-bottom:1.75rem;">
              Send Us a Message</h4>
            <form id="contactForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label-main">Full Name *</label>
                  <input type="text" name="name" class="form-control-main" placeholder="Juan dela Cruz" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label-main">Email Address *</label>
                  <input type="email" name="email" class="form-control-main" placeholder="juan@example.com" required>
                </div>
                <div class="col-12">
                  <label class="form-label-main">Subject *</label>
                  <input type="text" name="subject" class="form-control-main"
                    placeholder="Inquiry about San Enrique Tourism" required>
                </div>
                <div class="col-12">
                  <label class="form-label-main">Message *</label>
                  <textarea name="message" class="form-control-main" rows="5"
                    placeholder="Tell us how we can help you..." required style="resize:vertical;"></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i>Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer-main">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-4">
          <div class="footer-logo">
            <div class="d-flex align-items-center gap-3">
              <div class="brand-logo"
                style="width:44px;height:44px;background:linear-gradient(135deg,#52b788,#d4a017);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">
                🌿</div>
              <div>
                <div style="font-family:'Playfair Display',serif;color:white;font-size:1.1rem;font-weight:700;">San
                  Enrique Tourism Hub</div>
                <div
                  style="font-size:0.7rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.08em;">
                  Official LGU Tourism Platform</div>
              </div>
            </div>
          </div>
          <p class="footer-desc">
            Your official digital gateway to the beauty, culture, and hospitality of San Enrique, Iloilo. A proud
            initiative of the San Enrique Local Government Unit.
          </p>
          <div class="footer-social">
            <a href="#" class="social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-btn" title="Twitter"><i class="fab fa-twitter"></i></a>
          </div>
        </div>

        <div class="col-6 col-lg-2">
          <h5 class="footer-heading">Explore</h5>
          <ul class="footer-links">
            <?php foreach ($categories as $cat): ?>
              <li><a href="explore.php?category=<?= htmlspecialchars($cat['slug']) ?>"><i
                    class="fas fa-chevron-right"></i> <?= htmlspecialchars($cat['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="col-6 col-lg-2">
          <h5 class="footer-heading">Quick Links</h5>
          <ul class="footer-links">
            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
            <li><a href="explore.php"><i class="fas fa-chevron-right"></i> All Listings</a></li>
            <li><a href="map.php"><i class="fas fa-chevron-right"></i> Interactive Map</a></li>
            <li><a href="#events"><i class="fas fa-chevron-right"></i> Events</a></li>
            <li><a href="#about"><i class="fas fa-chevron-right"></i> About</a></li>
            <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
          </ul>
        </div>

        <div class="col-lg-4">
          <h5 class="footer-heading">Contact Us</h5>
          <div style="display:flex;flex-direction:column;gap:0.9rem;">
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <i class="fas fa-map-marker-alt" style="color:var(--accent);margin-top:3px;flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">Municipal Hall, Poblacion, San Enrique,
                Iloilo</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <i class="fas fa-phone" style="color:var(--accent);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">(033) 123-4567</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <i class="fas fa-envelope" style="color:var(--accent);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">tourism@sanenrique.gov.ph</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <i class="fas fa-clock" style="color:var(--accent);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">Mon–Fri, 8:00 AM – 5:00 PM</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <span>&copy; <?= date('Y') ?> San Enrique Tourism Hub. All rights reserved. | San Enrique LGU</span>
          <span>Developed for San Enrique, Iloilo, Philippines 🌿</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Map Data -->
  <script>
    const mapListings = <?= json_encode($mapListings) ?>;

    // Live-update poller bootstrap
    window.pageLoadTimestamp = <?= $initTs ?>;
    window.liveUpdateConfig = { page: 'home' };
  </script>

  <!-- Custom JS -->
  <script src="assets/js/main.js"></script>

  <!-- Google Maps API - Replace YOUR_GOOGLE_MAPS_API_KEY -->
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap&libraries=places">
    </script>
</body>

</html>