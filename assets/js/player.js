/* ============================================================
   BDMovieHub - Premium Video Player v5.0 (PRO)
   Advanced Features:
   - Custom Play/Pause/Stop controls
   - Volume control with mute toggle
   - Audio track selection (auto-detect language)
   - Quality selector (auto-detect HLS levels)
   - Playback speed control (0.25x - 4x)
   - Subtitle/caption support with upload
   - Picture-in-Picture mode
   - Fullscreen with toggle
   - P2P/WebTorrent support
   - Keyboard shortcuts
   - Settings panel
   - Progress bar with preview
   - Time display & duration
   - Auto-play next episode
   ============================================================ */

(function () {
    'use strict';

    // Prevent double-init
    if (window.BDMH_PLAYER_INIT) { return; }
    window.BDMH_PLAYER_INIT = true;

    /* ========== Configuration ========== */
    var CONFIG = {
        autoHideControls: 3000,
        volumeStep: 0.1,
        seekStep: 10,
        speeds: [0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 3, 4],
        defaultSpeed: 1,
        storagePrefix: 'bdmh_player_'
    };

    /* ========== State Management ========== */
    var playerState = {
        isPlaying: false,
        isMuted: false,
        isFullscreen: false,
        isPiP: false,
        currentVolume: 1,
        currentSpeed: CONFIG.defaultSpeed,
        currentQuality: -1, // Auto
        currentAudioTrack: -1,
        currentSubtitle: 'off',
        controlsVisible: true,
        hideControlsTimer: null,
        hlsInstance: null,
        webTorrentInstance: null
    };

    /* ========== DOM Elements Cache ========== */
    var elements = {};

    /* ========== Utility Functions ========== */
    function loadScript(url, onLoad, onError) {
        var s = document.createElement('script');
        s.src = url;
        s.async = true;
        s.onload = onLoad;
        s.onerror = onError || function () { console.error('Failed to load:', url); };
        document.head.appendChild(s);
    }

    function formatTime(seconds) {
        if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = Math.floor(seconds % 60);
        return h > 0 ? h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s : m + ':' + (s < 10 ? '0' : '') + s;
    }

    function saveSetting(key, value) {
        try { localStorage.setItem(CONFIG.storagePrefix + key, JSON.stringify(value)); } catch(e) {}
    }

    function getSetting(key, defaultValue) {
        try { 
            var val = localStorage.getItem(CONFIG.storagePrefix + key);
            return val !== null ? JSON.parse(val) : defaultValue; 
        } catch(e) { return defaultValue; }
    }

    function showPlayerError(message) {
        var errBox = document.querySelector('.player-error');
        if (errBox) {
            errBox.classList.add('show');
            var p = errBox.querySelector('p');
            if (p && message) p.textContent = message;
        }
        hideLoading();
        var v = document.getElementById('video-player');
        if (v) v.style.visibility = 'hidden';
    }

    function hideLoading() {
        var loading = document.querySelector('.player-loading');
        if (loading) loading.classList.add('hidden');
    }

    function showLoading() {
        var loading = document.querySelector('.player-loading');
        if (loading) loading.classList.remove('hidden');
    }

    /* ========== URL Type Detection ========== */
    function detectUrlType(rawUrl) {
        if (!rawUrl) return 'unknown';
        var url = String(rawUrl).trim();
        var lower = url.toLowerCase();

        // HLS streams
        if (/\.(m3u8)(\?.*)?(#.*)?$/i.test(url)) return 'hls';

        // Direct video files
        if (/\.(mp4|webm|ogg|ogv|mov|m4v|mkv)(\?.*)?(#.*)?$/i.test(url)) return 'video';

        // Magnet links / Torrent (P2P)
        if (/^magnet:\?/i.test(url)) return 'torrent';

        // YouTube
        var ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
        if (ytMatch) {
            return { type: 'iframe', src: 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=0&rel=0&modestbranding=1' };
        }

        // Vimeo
        var vimeoMatch = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
        if (vimeoMatch) {
            return { type: 'iframe', src: 'https://player.vimeo.com/video/' + vimeoMatch[1] };
        }

        // Dailymotion
        var dmMatch = url.match(/dailymotion\.com\/(?:video|embed\/video)\/([A-Za-z0-9]+)/);
        if (dmMatch) {
            return { type: 'iframe', src: 'https://www.dailymotion.com/embed/video/' + dmMatch[1] };
        }

        // Facebook
        if (/(?:^https?:\/\/)(?:www\.|m\.|web\.)?facebook\.com\//i.test(url) || /^https?:\/\/fb\.watch\//i.test(url)) {
            return {
                type: 'iframe',
                src: 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=false&width=560&autoplay=0'
            };
        }

        // Any other URL -> iframe embed
        if (/^https?:\/\//i.test(url)) {
            return { type: 'iframe', src: url };
        }

        return 'unknown';
    }

    /* ========== Build Premium Player UI ========== */
    function buildPremiumUI(container) {
        container.innerHTML = `
            <video id="video-player" playsinline preload="metadata"></video>
            
            <!-- Loading Spinner -->
            <div class="player-loading">
                <div class="player-spinner"></div>
                <span class="loading-text">Loading...</span>
            </div>

            <!-- Error Overlay -->
            <div class="player-error">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Playback Error</h3>
                <p>Unable to load this video stream.</p>
                <button class="btn-retry" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </div>

            <!-- Big Play Button (centered) -->
            <div class="big-play-btn" id="bigPlayBtn">
                <i class="fas fa-play"></i>
            </div>

            <!-- Video Overlay for click-to-play -->
            <div class="video-overlay" id="videoOverlay"></div>

            <!-- Custom Controls Bar -->
            <div class="premium-controls" id="premiumControls">
                <!-- Progress Bar -->
                <div class="progress-container" id="progressContainer">
                    <div class="progress-buffered" id="progressBuffered"></div>
                    <div class="progress-played" id="progressPlayed"></div>
                    <div class="progress-hover" id="progressHover"></div>
                    <input type="range" class="progress-slider" id="progressSlider" min="0" max="100" value="0" step="0.1">
                    <div class="preview-tooltip" id="previewTooltip"></div>
                </div>

                <!-- Controls Row -->
                <div class="controls-row">
                    <!-- Left Controls -->
                    <div class="controls-left">
                        <button class="ctrl-btn" id="playPauseBtn" title="Play/Pause (Space)">
                            <i class="fas fa-play"></i>
                        </button>
                        
                        <button class="ctrl-btn" id="skipBackBtn" title="Rewind 10s (←)">
                            <i class="fas fa-backward"></i>
                            <span class="skip-time">10</span>
                        </button>

                        <button class="ctrl-btn" id="skipForwardBtn" title="Forward 10s (→)">
                            <i class="fas fa-forward"></i>
                            <span class="skip-time">10</span>
                        </button>

                        <div class="volume-container" id="volumeContainer">
                            <button class="ctrl-btn" id="volumeBtn" title="Mute (M)">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <div class="volume-slider-wrap">
                                <input type="range" class="volume-slider" id="volumeSlider" min="0" max="1" value="1" step="0.05">
                                <div class="volume-track">
                                    <div class="volume-fill" id="volumeFill"></div>
                                </div>
                            </div>
                        </div>

                        <div class="time-display" id="timeDisplay">
                            <span class="current-time" id="currentTime">0:00</span>
                            <span class="time-separator">/</span>
                            <span class="duration" id="duration">0:00</span>
                        </div>
                    </div>

                    <!-- Right Controls -->
                    <div class="controls-right">
                        <!-- Settings Button -->
                        <div class="settings-container" id="settingsContainer">
                            <button class="ctrl-btn" id="settingsBtn" title="Settings">
                                <i class="fas fa-cog"></i>
                            </button>
                            
                            <!-- Settings Panel -->
                            <div class="settings-panel" id="settingsPanel">
                                <div class="settings-section">
                                    <h4 class="settings-title">Quality</h4>
                                    <div class="settings-options" id="qualityOptions">
                                        <button class="option-btn active" data-value="auto">Auto</button>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h4 class="settings-title">Speed</h4>
                                    <div class="settings-options" id="speedOptions"></div>
                                </div>

                                <div class="settings-section">
                                    <h4 class="settings-title">Audio Track</h4>
                                    <div class="settings-options" id="audioOptions">
                                        <button class="option-btn active" data-value="-1">Default</button>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h4 class="settings-title">Subtitles</h4>
                                    <div class="settings-options" id="subtitleOptions">
                                        <button class="option-btn active" data-value="off">Off</button>
                                        <label class="option-upload">
                                            <i class="fas fa-upload"></i> Upload .srt/.vtt
                                            <input type="file" accept=".srt,.vtt,.ass,.ssa" id="subtitleUpload">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subtitles Toggle -->
                        <button class="ctrl-btn" id="subtitleBtn" title="Subtitles (C)">
                            <i class="fas fa-closed-captioning"></i>
                        </button>

                        <!-- PiP Button -->
                        <button class="ctrl-btn" id="pipBtn" title="Picture-in-Picture (P)">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>

                        <!-- Fullscreen Button -->
                        <button class="ctrl-btn" id="fullscreenBtn" title="Fullscreen (F)">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- P2P Status Indicator -->
            <div class="p2p-status" id="p2pStatus" style="display:none;">
                <i class="fas fa-network-wired"></i>
                <span>P2P: <strong id="p2pPeers">0</strong> peers | <strong id="p2pSpeed">0 KB/s</strong></span>
            </div>
        `;

        // Cache DOM elements
        elements = {
            video: document.getElementById('video-player'),
            bigPlayBtn: document.getElementById('bigPlayBtn'),
            videoOverlay: document.getElementById('videoOverlay'),
            controls: document.getElementById('premiumControls'),
            playPauseBtn: document.getElementById('playPauseBtn'),
            skipBackBtn: document.getElementById('skipBackBtn'),
            skipForwardBtn: document.getElementById('skipForwardBtn'),
            volumeBtn: document.getElementById('volumeBtn'),
            volumeSlider: document.getElementById('volumeSlider'),
            volumeFill: document.getElementById('volumeFill'),
            timeDisplay: document.getElementById('timeDisplay'),
            currentTimeEl: document.getElementById('currentTime'),
            durationEl: document.getElementById('duration'),
            progressContainer: document.getElementById('progressContainer'),
            progressBuffered: document.getElementById('progressBuffered'),
            progressPlayed: document.getElementById('progressPlayed'),
            progressHover: document.getElementById('progressHover'),
            progressSlider: document.getElementById('progressSlider'),
            previewTooltip: document.getElementById('previewTooltip'),
            settingsBtn: document.getElementById('settingsBtn'),
            settingsPanel: document.getElementById('settingsPanel'),
            qualityOptions: document.getElementById('qualityOptions'),
            speedOptions: document.getElementById('speedOptions'),
            audioOptions: document.getElementById('audioOptions'),
            subtitleOptions: document.getElementById('subtitleOptions'),
            subtitleUpload: document.getElementById('subtitleUpload'),
            subtitleBtn: document.getElementById('subtitleBtn'),
            pipBtn: document.getElementById('pipBtn'),
            fullscreenBtn: document.getElementById('fullscreenBtn'),
            p2pStatus: document.getElementById('p2pStatus'),
            p2pPeers: document.getElementById('p2pPeers'),
            p2pSpeed: document.getElementById('p2pSpeed')
        };

initializeEventListeners();
        buildSpeedOptions();

        // Hide the PiP control where the API is unavailable
        // (Firefox without flag, iOS Safari, older browsers).
        if (!document.pictureInPictureEnabled && !document.webkitPictureInPictureEnabled) {
            elements.pipBtn.style.display = 'none';
        }
    }

    /* ========== Initialize Event Listeners ========== */
    function initializeEventListeners() {
        var video = elements.video;

        // Play/Pause
        elements.playPauseBtn.addEventListener('click', togglePlayPause);
        elements.bigPlayBtn.addEventListener('click', togglePlayPause);
        elements.videoOverlay.addEventListener('click', togglePlayPause);

        // Skip buttons
        elements.skipBackBtn.addEventListener('click', function() { seekRelative(-CONFIG.seekStep); });
        elements.skipForwardBtn.addEventListener('click', function() { seekRelative(CONFIG.seekStep); });

        // Volume
        elements.volumeBtn.addEventListener('click', toggleMute);
        elements.volumeSlider.addEventListener('input', handleVolumeChange);

        // Progress seeking
        elements.progressSlider.addEventListener('input', handleProgressSeek);
        elements.progressContainer.addEventListener('mousemove', handleProgressHover);
        elements.progressContainer.addEventListener('mouseleave', function() {
            elements.previewTooltip.style.display = 'none';
            elements.progressHover.style.width = '0%';
        });

        // Video events
        video.addEventListener('play', handlePlay);
        video.addEventListener('pause', handlePause);
        video.addEventListener('timeupdate', handleTimeUpdate);
        video.addEventListener('progress', handleProgress);
        video.addEventListener('loadedmetadata', handleMetadata);
        video.addEventListener('waiting', showLoading);
        video.addEventListener('canplay', hideLoading);
        video.addEventListener('error', handleError);
        video.addEventListener('volumechange', updateVolumeUI);

        // Fullscreen
        elements.fullscreenBtn.addEventListener('click', toggleFullscreen);

        // PiP
        elements.pipBtn.addEventListener('click', togglePiP);

        // Settings
        elements.settingsBtn.addEventListener('click', toggleSettings);
        
        // Subtitles
        elements.subtitleBtn.addEventListener('click', toggleSubtitleMenu);
        elements.subtitleUpload.addEventListener('change', handleSubtitleUpload);

        // Controls auto-hide
        var controlsWrap = elements.controls.parentElement;
        controlsWrap.addEventListener('mousemove', showControls);
        controlsWrap.addEventListener('mouseleave', startHideControlsTimer);
        elements.videoOverlay.addEventListener('mousemove', showControls);

        // Keyboard shortcuts
        document.addEventListener('keydown', handleKeyboardShortcuts);

        // Double-click fullscreen
        elements.videoOverlay.addEventListener('dblclick', toggleFullscreen);

        // Context menu (custom right-click menu can be added here)
        video.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            // Could add custom context menu here
        });
    }

    /* ========== Playback Controls ========== */
    function togglePlayPause() {
        var video = elements.video;
        if (video.paused || video.ended) {
            video.play().catch(function(e) { console.log('Autoplay prevented:', e); });
        } else {
            video.pause();
        }
    }

    function handlePlay() {
        playerState.isPlaying = true;
        updatePlayPauseUI();
        elements.bigPlayBtn.style.display = 'none';
        startHideControlsTimer();
    }

    function handlePause() {
        playerState.isPlaying = false;
        updatePlayPauseUI();
        elements.bigPlayBtn.style.display = 'flex';
        showControls();
    }

    function updatePlayPauseUI() {
        var icon = elements.playPauseBtn.querySelector('i');
        icon.className = playerState.isPlaying ? 'fas fa-pause' : 'fas fa-play';
        elements.playPauseBtn.title = playerState.isPlaying ? 'Pause (Space)' : 'Play (Space)';
    }

    /* ========== Volume Controls ========== */
    function handleVolumeChange(e) {
        var value = parseFloat(e.target.value);
        var video = elements.video;
        video.volume = value;
        video.muted = value === 0;
        playerState.isMuted = value === 0;
        playerState.currentVolume = value;
        saveSetting('volume', value);
        updateVolumeUI();
    }

    function toggleMute() {
        var video = elements.video;
        video.muted = !video.muted;
        playerState.isMuted = video.muted;
        if (!video.muted && video.volume === 0) {
            video.volume = 0.5;
        }
        saveSetting('muted', video.muted);
        updateVolumeUI();
    }

    function updateVolumeUI() {
        var video = elements.video;
        var icon = elements.volumeBtn.querySelector('i');
        var volume = video.muted ? 0 : video.volume;

        if (volume === 0) {
            icon.className = 'fas fa-volume-mute';
        } else if (volume < 0.5) {
            icon.className = 'fas fa-volume-down';
        } else {
            icon.className = 'fas fa-volume-up';
        }

        elements.volumeSlider.value = volume;
        elements.volumeFill.style.width = (volume * 100) + '%';
    }

    /* ========== Progress Bar ========== */
    function handleTimeUpdate() {
        var video = elements.video;
        if (!isNaN(video.duration) && isFinite(video.duration)) {
            var percent = (video.currentTime / video.duration) * 100;
            elements.progressPlayed.style.width = percent + '%';
            elements.progressSlider.value = percent;
            elements.currentTimeEl.textContent = formatTime(video.currentTime);
        }
    }

    function handleProgress() {
        var video = elements.video;
        if (video.buffered.length > 0) {
            var bufferedEnd = video.buffered.end(video.buffered.length - 1);
            var percent = (bufferedEnd / video.duration) * 100;
            elements.progressBuffered.style.width = percent + '%';
        }
    }

    function handleMetadata() {
        var video = elements.video;
        elements.durationEl.textContent = formatTime(video.duration);
        hideLoading();

        // Restore saved settings
        var savedVolume = getSetting('volume', 1);
        var savedMuted = getSetting('muted', false);
        var savedSpeed = getSetting('speed', 1);
        
        video.volume = savedVolume;
        video.muted = savedMuted;
        video.playbackRate = savedSpeed;
        playerState.currentSpeed = savedSpeed;
        updateVolumeUI();
        updateSpeedUI(savedSpeed);

        // Detect audio tracks
        detectAudioTracks(video);
    }

    function handleProgressSeek(e) {
        var video = elements.video;
        if (!video.duration || !isFinite(video.duration)) return;
        var percent = parseFloat(e.target.value);
        video.currentTime = (percent / 100) * video.duration;
        elements.progressPlayed.style.width = percent + '%';
    }

    function handleProgressHover(e) {
        var rect = elements.progressContainer.getBoundingClientRect();
        var percent = ((e.clientX - rect.left) / rect.width) * 100;
        var video = elements.video;
        
        if (percent >= 0 && percent <= 100 && !isNaN(video.duration)) {
            elements.progressHover.style.width = percent + '%';
            var hoverTime = (percent / 100) * video.duration;
            elements.previewTooltip.textContent = formatTime(hoverTime);
            elements.previewTooltip.style.display = 'block';
            elements.previewTooltip.style.left = Math.min(Math.max(e.clientX - rect.left, 0), rect.width - 50) + 'px';
        }
    }

    function seekRelative(seconds) {
        var video = elements.video;
        if (!video.duration || !isFinite(video.duration)) return;
        video.currentTime = Math.max(0, Math.min(video.duration, video.currentTime + seconds));
    }

    /* ========== Speed Control ========== */
    function buildSpeedOptions() {
        var html = '';
        CONFIG.speeds.forEach(function(speed) {
            var label = speed === 1 ? 'Normal' : speed + 'x';
            html += '<button class="option-btn" data-value="' + speed + '">' + label + '</button>';
        });
        elements.speedOptions.innerHTML = html;

        // Add click handlers
        elements.speedOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var speed = parseFloat(this.dataset.value);
                setPlaybackSpeed(speed);
            });
        });
    }

    function setPlaybackSpeed(speed) {
        var video = elements.video;
        video.playbackRate = speed;
        playerState.currentSpeed = speed;
        saveSetting('speed', speed);
        updateSpeedUI(speed);
    }

    function updateSpeedUI(activeSpeed) {
        elements.speedOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            var speed = parseFloat(btn.dataset.value);
            btn.classList.toggle('active', speed === activeSpeed);
            btn.textContent = speed === 1 ? 'Normal ✓' : (speed === activeSpeed ? speed + 'x ✓' : speed + 'x');
        });
    }

    /* ========== Quality Control (HLS) ========== */
    function buildQualityMenu(hls) {
        var levels = hls.levels;
        if (!levels || levels.length <= 1) return;

        var html = '<button class="option-btn active" data-value="-1">Auto</button>';
        levels.forEach(function(level, index) {
            var height = level.height ? level.height + 'p' : 'Level ' + (index + 1);
            var bitrate = level.bitrate ? Math.round(level.bitrate / 1000) + ' kbps' : '';
            html += '<button class="option-btn" data-value="' + index + '">' + height + ' (' + bitrate + ')</button>';
        });
        elements.qualityOptions.innerHTML = html;

        elements.qualityOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var level = parseInt(this.dataset.value);
                setQuality(hls, level);
            });
        });
    }

    function setQuality(hls, level) {
        hls.currentLevel = level;
        playerState.currentQuality = level;
        
        elements.qualityOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            var btnLevel = parseInt(btn.dataset.value);
            btn.classList.toggle('active', btnLevel === level);
            if (btnLevel === level) {
                btn.textContent += ' ✓';
            } else {
                btn.textContent = btn.textContent.replace(' ✓', '');
            }
        });
    }

    /* ========== Audio Track Detection ========== */
    function detectAudioTracks(video) {
        // For HLS audio tracks
        if (playerState.hlsInstance && playerState.hlsInstance.audioTrackController) {
            var audioTracks = playerState.hlsInstance.audioTracks;
            if (audioTracks && audioTracks.list && audioTracks.list.length > 0) {
                var html = '<button class="option-btn active" data-value="-1">Default</button>';
                audioTracks.list.forEach(function(track, index) {
                    var name = track.name || track.lang || ('Track ' + (index + 1));
                    var lang = track.lang ? '(' + track.lang.toUpperCase() + ')' : '';
                    html += '<button class="option-btn" data-value="' + index + '">' + name + ' ' + lang + '</button>';
                });
                elements.audioOptions.innerHTML = html;

                elements.audioOptions.querySelectorAll('.option-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var trackIndex = parseInt(this.dataset.value);
                        setAudioTrack(trackIndex);
                    });
                });
            }
        }

        // Native audio tracks (for MP4 with multiple audio)
        if (video.audioTracks && video.audioTracks.length > 0) {
            console.log('Native audio tracks found:', video.audioTracks.length);
        }
    }

    function setAudioTrack(index) {
        if (playerState.hlsInstance) {
            playerState.hlsInstance.audioTrack = index;
            playerState.currentAudioTrack = index;
        }
        
        elements.audioOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            var btnIndex = parseInt(btn.dataset.value);
            btn.classList.toggle('active', btnIndex === index);
        });
    }

    /* ========== Subtitle Support ========== */
    function toggleSubtitleMenu() {
        elements.settingsPanel.classList.toggle('show');
        // Scroll to subtitle section
        var subSection = elements.settingsPanel.querySelector('.settings-section:last-child');
        if (subSection) subSection.scrollIntoView({ behavior: 'smooth' });
    }

    function handleSubtitleUpload(e) {
        var file = e.target.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function(event) {
            var content = event.target.result;
            addSubtitleTrack(content, file.name);
        };
        reader.readAsText(file);
    }

    function addSubtitleTrack(content, filename) {
        var video = elements.video;
        var trackId = 'sub_' + Date.now();

        // Determine format from extension
        var ext = filename.split('.').pop().toLowerCase();
        var kind = 'subtitles';
        var label = filename.replace(/\.[^/.]+$/, '');

        // Parse and convert to VTT if needed
        if (ext === 'srt') {
            content = convertSRTtoVTT(content);
        }

        // Create blob URL for subtitle
        var blob = new Blob([content], { type: 'text/vtt' });
        var url = URL.createObjectURL(blob);

        // Add track element
        var track = document.createElement('track');
        track.kind = kind;
        track.label = label;
        track.srclang = 'custom';
        track.src = url;
        track.id = trackId;
        video.appendChild(track);

        // Enable the track
        if (video.textTracks.length > 0) {
            video.textTracks[video.textTracks.length - 1].mode = 'showing';
        }

        // Update UI
        var optionHtml = '<button class="option-btn active" data-value="' + trackId + '">' + label + ' ✓</button>';
        elements.subtitleOptions.insertAdjacentHTML('beforeend', optionHtml);

        // Bind event to new button
        var newBtn = elements.subtitleOptions.querySelector('[data-value="' + trackId + '"]');
        if (newBtn) {
            newBtn.addEventListener('click', function() {
                selectSubtitle(trackId);
            });
        }

        showNotification('Subtitle loaded: ' + label);
    }

    function convertSRTtoVTT(srtContent) {
        var vtt = 'WEBVTT\n\n';
        // Convert SRT timestamp format (00:00:00,000 --> 00:00:00,000) to VTT (00:00:00.000 --> 00:00:00.000)
        vtt += srtContent.replace(/(\d{2}:\d{2}:\d{2}),(\d{3})/g, '$1.$2');
        return vtt;
    }

    function selectSubtitle(trackId) {
        var video = elements.video;
        
        // Disable all tracks first
        for (var i = 0; i < video.textTracks.length; i++) {
            video.textTracks[i].mode = 'disabled';
        }

        // Enable selected or turn off
        if (trackId !== 'off') {
            var track = document.getElementById(trackId);
            if (track && track.track) {
                track.track.mode = 'showing';
            }
        }

        playerState.currentSubtitle = trackId;
        
        elements.subtitleOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.value === trackId);
        });
    }

    /* ========== Settings Panel ========== */
    function toggleSettings(e) {
        e.stopPropagation();
        elements.settingsPanel.classList.toggle('show');
    }

    // Close settings when clicking outside
    document.addEventListener('click', function(e) {
        if (elements.settingsPanel && !elements.settingsPanel.contains(e.target) && 
            !elements.settingsBtn.contains(e.target)) {
            elements.settingsPanel.classList.remove('show');
        }
    });

    /* ========== Fullscreen ========== */
    function toggleFullscreen() {
        var container = elements.controls.closest('.player-container') || elements.video.parentElement;

        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            } else if (container.msRequestFullscreen) {
                container.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }

    function handleFullscreenChange() {
        playerState.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
        var icon = elements.fullscreenBtn.querySelector('i');
        icon.className = playerState.isFullscreen ? 'fas fa-compress' : 'fas fa-expand';
        elements.fullscreenBtn.title = playerState.isFullscreen ? 'Exit Fullscreen (F)' : 'Fullscreen (F)';
    }

    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);

    /* ========== Picture-in-Picture ========== */
    async function togglePiP() {
        var video = elements.video;
        // Iframe mode: the premium <video> was replaced by the provider embed.
        if (!video || !video.isConnected) return;

        try {
            if (document.pictureInPictureElement) {
                await document.exitPictureInPicture();
            } else if (document.pictureInPictureEnabled) {
                await video.requestPictureInPicture();
            } else {
                showNotification('Picture-in-Picture not supported in this browser');
            }
        } catch(error) {
            console.error('PiP error:', error);
            showNotification('Could not enable Picture-in-Picture');
        }
    }

    /* ========== Controls Visibility ========== */
    function showControls() {
        playerState.controlsVisible = true;
        elements.controls.classList.add('visible');
        startHideControlsTimer();
    }

    function startHideControlsTimer() {
        clearTimeout(playerState.hideControlsTimer);
        if (playerState.isPlaying) {
            playerState.hideControlsTimer = setTimeout(function() {
                elements.controls.classList.remove('visible');
                playerState.controlsVisible = false;
            }, CONFIG.autoHideControls);
        }
    }

    /* ========== Keyboard Shortcuts ========== */
    function handleKeyboardShortcuts(e) {
        // Iframe mode: the premium <video> was replaced by the provider embed,
        // whose own player handles its shortcuts — do nothing here.
        if (!elements.video || !elements.video.isConnected) return;
        // Only when player is focused or visible
        var container = elements.video.closest('.player-wrapper');
        if (!container) return;

        var tag = e.target.tagName.toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

        switch(e.key.toLowerCase()) {
            case ' ':
            case 'k':
                e.preventDefault();
                togglePlayPause();
                break;
            case 'arrowleft':
                e.preventDefault();
                seekRelative(-CONFIG.seekStep);
                break;
            case 'arrowright':
                e.preventDefault();
                seekRelative(CONFIG.seekStep);
                break;
            case 'arrowup':
                e.preventDefault();
                changeVolume(CONFIG.volumeStep);
                break;
            case 'arrowdown':
                e.preventDefault();
                changeVolume(-CONFIG.volumeStep);
                break;
            case 'm':
                toggleMute();
                break;
            case 'f':
                toggleFullscreen();
                break;
            case 'p':
                togglePiP();
                break;
            case 'c':
                toggleSubtitleMenu();
                break;
            case ',':
            case '<':
                changePlaybackSpeed(-0.25);
                break;
            case '.':
            case '>':
                changePlaybackSpeed(0.25);
                break;
        }
    }

    function changeVolume(delta) {
        var video = elements.video;
        var newVol = Math.max(0, Math.min(1, video.volume + delta));
        video.volume = newVol;
        elements.volumeSlider.value = newVol;
        saveSetting('volume', newVol);
        updateVolumeUI();
    }

    function changePlaybackSpeed(delta) {
        var currentIndex = CONFIG.speeds.indexOf(playerState.currentSpeed);
        if (currentIndex === -1) currentIndex = CONFIG.speeds.indexOf(1);
        var newIndex = Math.max(0, Math.min(CONFIG.speeds.length - 1, currentIndex + (delta > 0 ? 1 : -1)));
        setPlaybackSpeed(CONFIG.speeds[newIndex]);
    }

    /* ========== Error Handling ========== */
    // Multi-source fallback: pages can declare alternates via
    // <video data-src="primary" data-alt-sources="url1|url2"> — on load/play
    // failure we walk the list before showing the error overlay.
    var sourceFallbackList = [];
    var sourceFallbackIndex = -1; // -1 = primary source

    function initSourceFallback(video) {
        try {
            var alts = (video.getAttribute('data-alt-sources') || '').split('|');
            sourceFallbackList = alts.map(function (s) { return s.trim(); }).filter(Boolean);
        } catch (e) { sourceFallbackList = []; }
        sourceFallbackIndex = -1;
    }

    function tryNextSource() {
        var video = elements.video;
        if (!video) { return false; }
        var next = sourceFallbackIndex + 1;
        if (next >= sourceFallbackList.length) { return false; }
        sourceFallbackIndex = next;
        showNotification('Trying backup source ' + (next + 1) + '…', 2500);
        video.src = sourceFallbackList[next];
        video.load();
        video.play().catch(function () {});
        return true;
    }

    function handleError(e) {
        var video = elements.video;
        // Iframe mode: ignore stale error events from the removed <video>
        if (!video || !video.isConnected) return;
        var message = 'An error occurred during playback.';

        switch (video.error ? video.error.code : 0) {
            case 1: message = 'Video loading aborted.'; break;
            case 2: message = 'Network error. Check your connection.'; break;
            case 3: message = 'Video decoding error.'; break;
            case 4: message = 'Video format not supported.'; break;
            default: message = 'Unable to load this video.'; break;
        }

        // Walk the fallback chain before giving up
        if (tryNextSource()) { return; }
        showPlayerError(message);
    }

    /* ========== Notification Toast ========== */
    function showNotification(message, duration) {
        duration = duration || 2000;
        var toast = document.createElement('div');
        toast.className = 'player-toast';
        toast.innerHTML = '<i class="fas fa-info-circle"></i> ' + message;
        toast.style.cssText = 
            'position:absolute;top:20px;left:50%;transform:translateX(-50%);' +
            'background:rgba(0,0,0,0.9);color:#fff;padding:12px 24px;border-radius:8px;' +
            'font-size:13px;z-index:100;display:flex;align-items:center;gap:8px;' +
            'animation:fadeInUp 0.3s ease;';
        
        var container = elements.controls ? elements.controls.closest('.player-wrapper') : null;
        if (container) {
            container.style.position = 'relative';
            container.appendChild(toast);
            setTimeout(function() {
                toast.style.animation = 'fadeOutDown 0.3s ease';
                setTimeout(function() { toast.remove(); }, 300);
            }, duration);
        }
    }

    /* ========== P2P / WebTorrent Support ========== */
    function initWebTorrent(magnetUri) {
        showLoading();
        showNotification('Initializing P2P download...');

        loadScript('https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js', function() {
            if (!window.WebTorrent) {
                showPlayerError('WebTorrent failed to load.');
                return;
            }

            var wt = new window.WebTorrent({
                tracker: {
                    rtcConfig: {
                        iceServers: [
                            { urls: 'stun:stun.l.google.com:19302' },
                            { urls: 'stun:global.stun.twilio.com:3478' }
                        ]
                    }
                }
            });

            playerState.webTorrentInstance = wt;

            wt.add(magnetUri, function(torrent) {
                // Find largest file (usually the video)
                torrent.files.sort(function(a, b) { return b.length - a.length; });
                var file = torrent.files[0];

                file.appendTo(elements.video, function(err) {
                    if (err) {
                        showPlayerError('Error playing torrent: ' + err.message);
                        return;
                    }
                    
                    hideLoading();
                    elements.p2pStatus.style.display = 'flex';
                    showNotification('Streaming via P2P!');

                    // Update P2P stats
                    setInterval(updateP2PStats, 1000);
                });

                // Update peers count
                torrent.on('wire', function() {
                    elements.p2pPeers.textContent = torrent.numPeers;
                });

                torrent.on('done', function() {
                    showNotification('Download complete!');
                });
            });

            wt.on('error', function(err) {
                showPlayerError('P2P Error: ' + err.message);
            });
        }, function() {
            showPlayerError('Failed to load WebTorrent library.');
        });
    }

    function updateP2PStats() {
        var wt = playerState.webTorrentInstance;
        if (!wt) return;

        var totalSpeed = 0;
        wt.torrents.forEach(function(t) {
            totalSpeed += t.uploadSpeed + t.downloadSpeed;
        });

        elements.p2pPeers.textContent = wt.torrents.reduce(function(sum, t) { return sum + t.numPeers; }, 0);
        elements.p2pSpeed.textContent = formatBytes(totalSpeed) + '/s';
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    /* ========== HLS Player Builder ========== */
    function buildHlsPlayer(video, src) {
        // Enable the backup-source chain for fatal HLS failures too
        initSourceFallback(video);

        // Native HLS support (Safari, iOS)
        if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = src;
            setupVideoEvents(video);
            return;
        }

        // HLS.js for other browsers
        loadScript('https://cdn.jsdelivr.net/npm/hls.js@latest', function() {
            if (!window.Hls || !window.Hls.isSupported()) {
                showPlayerError('Your browser does not support HLS playback.');
                return;
            }

            var hls = new window.Hls({
                enableWorker: true,
                lowLatencyMode: false,
                backBuffer: 30,
                maxBufferLength: 60,
                maxMaxBufferLength: 120,
                manifestLoadingTimeOut: 15000,
                manifestLoadingMaxRetry: 3,
                levelLoadingTimeOut: 15000,
                levelLoadingMaxRetry: 3,
                fragLoadingTimeOut: 25000,
                fragLoadingMaxRetry: 6
            });

            hls.loadSource(src);
            hls.attachMedia(video);
            playerState.hlsInstance = hls;

            hls.on(window.Hls.Events.MANIFEST_PARSED, function(event, data) {
                hideLoading();
                buildQualityMenu(hls);
                
                // Detect audio tracks in HLS
                if (hls.audioTracks && hls.audioTracks.length > 0) {
                    buildHLSAudioMenu(hls);
                }

                video.play().catch(function() {});
            });

            hls.on(window.Hls.Events.AUDIO_TRACKS_UPDATED, function() {
                buildHLSAudioMenu(hls);
            });

            hls.on(window.Hls.Events.LEVEL_SWITCHED, function(event, data) {
                var level = hls.levels[data.level];
                if (level) {
                    showNotification('Quality: ' + (level.height ? level.height + 'p' : 'Auto'));
                }
            });

            hls.on(window.Hls.Events.ERROR, function(event, data) {
                console.error('HLS error:', data);
                if (data.fatal) {
                    switch (data.type) {
                        case window.Hls.ErrorTypes.NETWORK_ERROR:
                            showNotification('Network error, retrying...');
                            hls.startLoad();
                            break;
                        case window.Hls.ErrorTypes.MEDIA_ERROR:
                            showNotification('Media error, recovering...');
                            hls.recoverMediaError();
                            break;
                        default:
                            hls.destroy();
                            // Walk backup sources before showing the error overlay
                            if (!tryNextSource()) {
                                showPlayerError('Fatal HLS error. Stream may be unavailable.');
                            }
                            break;
                    }
                }
            });
        }, function() {
            showPlayerError('Failed to load HLS player.');
        });
    }

    function buildHLSAudioMenu(hls) {
        var tracks = hls.audioTracks;
        if (!tracks || tracks.length === 0) return;

        var html = '<button class="option-btn active" data-value="-1">Default</button>';
        tracks.forEach(function(track, index) {
            var name = track.name || track.lang || ('Audio ' + (index + 1));
            var lang = track.lang ? ' [' + track.lang.toUpperCase() + ']' : '';
            html += '<button class="option-btn" data-value="' + index + '">' + name + lang + '</button>';
        });
        elements.audioOptions.innerHTML = html;

        elements.audioOptions.querySelectorAll('.option-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.value);
                hls.audioTrack = idx;
                playerState.currentAudioTrack = idx;
                
                elements.audioOptions.querySelectorAll('.option-btn').forEach(function(b) {
                    b.classList.toggle('active', b === btn);
                });
                
                showNotification('Audio: ' + btn.textContent.trim());
            });
        });
    }

    /* ========== Native Video Player ========== */
    function buildNativeVideoPlayer(video, src) {
        initSourceFallback(video);
        video.src = src;
        setupVideoEvents(video);
    }

    function setupVideoEvents(video) {
        // Core events (loadedmetadata/canplay/error) are ALREADY bound once in
        // initializeEventListeners(). Binding them again here made every error
        // fire twice, which skipped alternate sources during fallback.
        exposeNativeSubtitles(video);
        video.play().catch(function () {});
    }

    // Expose native <track> subtitle/caption tracks in the settings menu
    function exposeNativeSubtitles(video) {
        video.addEventListener('loadedmetadata', function () {
            try {
                var tracks = video.textTracks || [];
                if (elements.subtitleOptions && tracks.length > 0) {
                    for (var i = 0; i < tracks.length; i++) {
                        (function (idx) {
                            var track = tracks[idx];
                            if (track.mode === 'disabled') { track.mode = 'hidden'; }
                            var label = track.label || track.language || ('Track ' + (idx + 1));
                            if (elements.subtitleOptions.querySelector('[data-native-track="' + idx + '"]')) { return; }
                            var btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'option-btn';
                            btn.setAttribute('data-native-track', String(idx));
                            btn.textContent = label;
                            btn.addEventListener('click', function () {
                                for (var k = 0; k < tracks.length; k++) { tracks[k].mode = (k === idx) ? 'showing' : 'hidden'; }
                                playerState.currentSubtitle = label;
                                elements.subtitleOptions.querySelectorAll('.option-btn').forEach(function (b) {
                                    b.classList.toggle('active', b === btn);
                                });
                                showNotification('Subtitles: ' + label);
                            });
                            elements.subtitleOptions.appendChild(btn);
                        })(i);
                    }
                }
            } catch (e) { /* textTracks unsupported */ }
        });
    }

    /* ========== Iframe Player (Embed URLs) ========== */
    function buildIframePlayer(src) {
        var container = document.querySelector('.player-container');
        if (!container) return;

        // Clear existing content
        container.innerHTML = '';

        var iframe = document.createElement('iframe');
        iframe.id = 'video-player-iframe';
        iframe.src = src;
        iframe.setAttribute('allowfullscreen', 'allowfullscreen');
        iframe.setAttribute('webkitallowfullscreen', 'webkitallowfullscreen');
        iframe.setAttribute('mozallowfullscreen', 'mozallowfullscreen');
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('scrolling', 'no');
        iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture; encrypted-media; accelerometer; gyroscope; clipboard-write');
        iframe.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:0;background:#000;';
        
        container.appendChild(iframe);
        hideLoading();

        // Fallback overlay for slow loads
        var loaded = false;
        iframe.addEventListener('load', function() { loaded = true; });
        setTimeout(function() {
            if (!loaded && iframe.parentNode) {
                var existing = container.querySelector('.iframe-fallback-overlay');
                if (!existing) {
                    var overlay = document.createElement('div');
                    overlay.className = 'iframe-fallback-overlay';
                    overlay.style.cssText =
                        'position:absolute;bottom:12px;right:12px;z-index:8;' +
                        'background:rgba(0,0,0,0.85);color:#fff;padding:8px 14px;border-radius:6px;' +
                        'font-size:12px;display:flex;align-items:center;gap:8px;';
                    overlay.innerHTML =
                        '<span style="opacity:0.85;">Taking long?</span>' +
                        '<a href="' + src + '" target="_blank" rel="noopener noreferrer" ' +
                        'style="color:#469AFF;text-decoration:none;font-weight:600;">' +
                        '<i class="fas fa-external-link-alt"></i> Open in new tab</a>';
                    container.appendChild(overlay);
                }
            }
        }, 20000);
    }

    /* ========== Main Initialization ========== */
    function initPlayer() {
        var container = document.querySelector('.player-container');
        if (!container) return;

        var originalVideo = document.getElementById('video-player');
        var src = originalVideo ? originalVideo.getAttribute('data-src') : '';
        if (!src) {
            showPlayerError('No video source provided.');
            return;
        }

        // Build premium UI.
        // First preserve server-rendered <track> subtitle elements — the
        // rebuild below replaces the original <video> (and its children).
        var preservedTracks = [];
        if (originalVideo) {
            var __tr = originalVideo.querySelectorAll('track');
            for (var __ti = 0; __ti < __tr.length; __ti++) {
                preservedTracks.push(__tr[__ti].cloneNode(true));
            }
        }
        buildPremiumUI(container);
        for (var __tj = 0; __tj < preservedTracks.length; __tj++) {
            elements.video.appendChild(preservedTracks[__tj]);
        }

        var detected = detectUrlType(src);

        if (detected === 'hls') {
            buildHlsPlayer(elements.video, src);
        } else if (detected === 'video') {
            buildNativeVideoPlayer(elements.video, src);
        } else if (detected === 'torrent') {
            initWebTorrent(src);
        } else if (detected && typeof detected === 'object' && detected.type === 'iframe') {
            buildIframePlayer(detected.src);
        } else {
            // Unknown - try native, fallback to iframe
            console.warn('Unknown URL type, attempting native video:', src);
            buildNativeVideoPlayer(elements.video, src);
            
            var fallbackFired = false;
            var fallbackTimer = setTimeout(function() {
                if (!fallbackFired && (elements.video.readyState === 0 || elements.video.error)) {
                    fallbackFired = true;
                    buildIframePlayer(src);
                }
            }, 6000);

            elements.video.addEventListener('error', function() {
                // Only swap to the iframe embed once the backup-source chain
                // is exhausted (handleError walks it first).
                if (!fallbackFired && sourceFallbackIndex >= sourceFallbackList.length - 1) {
                    fallbackFired = true;
                    clearTimeout(fallbackTimer);
                    buildIframePlayer(src);
                }
            });
        }
    }

    /* ========== Start Player on DOM Ready ========== */
    document.addEventListener('DOMContentLoaded', initPlayer);

    // Also re-init if called after page load
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initPlayer();
    }

})();
