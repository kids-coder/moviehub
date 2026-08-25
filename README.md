# BDMovieHub - Free Movies & Anime Streaming Platform

A complete PHP-based movie and anime streaming website with no database (uses JSON file storage). Designed to run on InfinityFree and similar free PHP hosts.

## Features

### Frontend
- **Homepage** with hero slider, featured movies, trending, top rated, recent movies, latest anime, quick nav
- **Movies listing** with genre filter and pagination
- **Anime listing** with genre filter and pagination
- **Movie detail page** with banner, info, HLS.js video player, related movies, comments, share, report broken video
- **Anime watch page** with episode player, episode list, next/prev navigation, related anime, comments, share, report
- **Trending page** (sorted by views + rating)
- **Top Rated page**
- **Browse by Genre** (combined movies + anime)
- **Search page** with tabs for Movies / Anime and pagination
- **Anime search** (anime-only)
- **Anime schedule** (weekly grid)
- **Favorites page** (stored in browser localStorage)
- **Contact form** (saves messages for admin to read)
- **Live search dropdown** in navbar (auto-suggest as you type)
- **Custom 404 error page**
- **XML sitemap** (`sitemap.php`)
- **RSS feed** (`rss.php`)
- **robots.txt** for SEO
- **Legal pages**: DMCA, Privacy Policy, Terms of Service, Disclaimer

### Admin Panel (`/admin/`)
- **Dashboard** with stats (movies, anime, episodes, pages counts) and recent items
- **Movies**: add, edit, delete, list (with poster/banner, genres, quality, rating, stream URL, trailer, download URL, status)
- **Anime**: add, edit, delete, list (similar fields + episode count, studio, status)
- **Episodes**: add, edit, delete, list (per anime)
- **Featured manager**: add/remove movies/anime from featured
- **Hero Slides manager**: add/delete hero banner slides (with order)
- **Pages**: add, edit, delete static pages (with HTML content)
- **Categories**: add, delete categories
- **Schedule**: weekly anime schedule (Mon-Sun)
- **Messages & Comments**: view contact messages, approve/delete user comments, view broken video reports
- **Import**: bulk JSON import (paste JSON array of movies/anime/episodes/pages/slides)
- **Export**: download full backup (single JSON) or individual data files
- **Backup & Restore**: timestamped snapshots of all JSON data
- **Settings**: site name, URL, description, logo, footer text, contact info, theme colors, social media links, SEO keywords, Open Graph image, analytics code (Google Analytics etc.), custom CSS, custom JS, auto-approve comments toggle
- **Users**: admin user management

### Technical
- **No database** - all data stored in `/data/*.json` files (auto-created on first run)
- **HLS.js** for video streaming (.m3u8 streams) with quality selector
- **Session-based admin auth** (default: admin/admin123)
- **Responsive design** - works on desktop, tablet, mobile
- **Dark/light theme** toggle (saved in localStorage)
- **Favorites** stored in localStorage (no login needed)
- **Watch history** (continue watching) stored in localStorage
- **SEO**: Open Graph tags, Twitter Cards, JSON-LD structured data, canonical URLs, sitemap, RSS
- **Security**: data directory protected via .htaccess, error handling with friendly messages
- **InfinityFree-compatible**: NO short PHP tags, NO double-curly braces, alternative syntax throughout

## Installation

1. Upload all files to your web hosting (e.g., InfinityFree `htdocs/`)
2. Make sure the `/data/` directory is writable (CHMOD 755 or 777 if needed)
3. Visit your site at the root URL
4. Login to admin at `/admin/login.php` with username `admin` and password `admin123`
5. **CHANGE THE ADMIN PASSWORD** immediately via the Users page

## File Structure

```
bdmoviehub/
├── index.php                  # Homepage
├── config.php                 # Site configuration
├── functions.php              # Helper functions
├── bootstrap.php              # Auto-create data files
├── header.php                 # Frontend header partial
├── footer.php                 # Frontend footer partial
├── search.php                 # Movies + Anime search
├── movie.php                  # Single movie page
├── anime.php                  # Anime listing
├── anime-watch.php            # Watch anime episode
├── anime-search.php           # Anime-only search
├── anime-schedule.php         # Weekly anime schedule
├── genres.php                 # Browse by genre
├── trending.php               # Trending items
├── top-rated.php              # Top rated items
├── favorites.php              # User favorites (localStorage)
├── contact.php                # Contact form
├── page.php                   # Custom static page
├── 404.php                    # 404 error page
├── sitemap.php                # XML sitemap
├── rss.php                    # RSS feed
├── api-search.php             # AJAX live search endpoint
├── api-report.php             # AJAX broken video report
├── dmca.php                   # DMCA policy
├── privacy.php                # Privacy policy
├── terms.php                  # Terms of service
├── disclaimer.php             # Disclaimer
├── diagnostics.php            # System diagnostics
├── test.php                   # Test page
├── debug.php                  # Debug page
├── setup.php                  # Setup wizard
├── robots.txt                 # Robots
├── .htaccess                  # Apache config
├── admin/
│   ├── index.php              # Dashboard
│   ├── login.php / logout.php # Auth
│   ├── header.php / footer.php
│   ├── movies.php             # List movies
│   ├── movie-add.php / movie-edit.php / movie-delete.php
│   ├── anime.php              # List anime
│   ├── anime-add.php / anime-edit.php / anime-delete.php
│   ├── episodes.php
│   ├── episode-add.php / episode-edit.php / episode-delete.php
│   ├── pages.php / page-add.php / page-edit.php / page-delete.php
│   ├── schedule.php
│   ├── featured.php           # Featured items manager
│   ├── slides.php / slide-add.php / slide-delete.php
│   ├── categories.php / category-add.php / category-delete.php
│   ├── comments.php / comment-delete.php / comment-approve.php
│   ├── settings.php           # Site settings
│   ├── users.php              # User management
│   ├── import.php             # Bulk JSON import
│   ├── export.php             # Export data
│   └── backup.php             # Backup & restore
├── assets/
│   ├── css/
│   │   ├── style.css          # Main stylesheet (movies theme)
│   │   ├── anime.css          # Anime page overrides
│   │   └── player.css         # Video player styles
│   └── js/
│       ├── ui.js              # UI: mobile menu, scroll-to-top, modals
│       ├── features.js        # Theme, favorites, live search, share
│       └── player.js          # HLS.js video player
└── data/                      # JSON storage (auto-created)
    ├── settings.json
    ├── movies.json
    ├── anime.json
    ├── episodes.json
    ├── pages.json
    ├── schedule.json
    ├── users.json
    ├── categories.json
    ├── featured.json
    ├── comments.json
    ├── slides.json
    ├── genres.json
    ├── contacts.json          # Contact form messages
    ├── reports.json           # Broken video reports
    └── backups/               # Snapshots
```

## Default Admin Login
- Username: `admin`
- Password: `admin123`

**Change this immediately after installation!**

## Theme Colors
- **Movies (Primary)**: #469AFF (Blue)
- **Accent**: #FF6B6B (Red)
- **Anime**: #9b59b6 (Purple)

All colors are customizable from the admin Settings page.

## Video Streaming
The video player uses [HLS.js](https://github.com/video-dev/hls.js/) to play `.m3u8` streams.
Add stream URLs in the admin panel when creating/editing movies or anime episodes.

## License
This project is provided as-is for educational purposes. Please ensure you have the rights to stream any content you add.
