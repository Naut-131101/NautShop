<?php
$brandUrl = app_home_url();
$profileCompleted = has_completed_profile();
$cartItemsCount = cart_count();
$user = auth() ?? [];
$enableGlobalMusic = is_auth();
$currentLocale = current_locale();
$nextLocale = next_locale();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptBasePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

if ($scriptBasePath !== '/' && $scriptBasePath !== '' && str_starts_with($currentPath, $scriptBasePath)) {
    $currentPath = substr($currentPath, strlen($scriptBasePath));
}

if ($currentPath === '') {
    $currentPath = '/';
}

$isProductsPage = $currentPath === '/products';
$isCartPage = $currentPath === '/cart';
$isOrdersPage = $currentPath === '/orders';
?>
<!DOCTYPE html>
<html lang="<?= e($currentLocale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Naut Shop') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('images/logo.png')) ?>">
</head>
<body class="app-body" data-theme="light">
    <?php if ($flashSuccess = flash('success')): ?>
        <div class="flash-toast-wrap">
            <div class="flash-toast success" id="flashToast" role="status" aria-live="polite">
                <span class="flash-toast-icon-badge" aria-hidden="true">i</span>
                <div class="flash-toast-content">
                    <span class="flash-toast-message"><?= e((string) $flashSuccess) ?></span>
                </div>
                <button type="button" class="flash-toast-close" id="flashToastClose" aria-label="<?= e(t('layout.flash_close')) ?>">x</button>
                <span class="flash-toast-progress" aria-hidden="true"></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="site-shell">
        <header class="site-header">
            <div class="site-header-inner">
                <div class="brand-block">
                    <a href="<?= e($brandUrl) ?>" class="brand-logo">
                        <img src="<?= e(asset('images/logo.png')) ?>" alt="NautShop" class="brand-logo-img">
                    </a>
                </div>

                <nav class="site-nav">
                    <button
                        type="button"
                        class="theme-toggle"
                        id="themeToggle"
                        aria-label="<?= e(t('layout.theme_toggle_aria')) ?>"
                        data-icon-light="<?= e(t('layout.theme_icon_light')) ?>"
                        data-label-light="<?= e(t('layout.theme_label_light')) ?>"
                        data-icon-dark="<?= e(t('layout.theme_icon_dark')) ?>"
                        data-label-dark="<?= e(t('layout.theme_label_dark')) ?>"
                    >
                        <span class="theme-toggle-icon" id="themeToggleIcon"><?= e(t('layout.theme_icon_light')) ?></span>
                        <span class="theme-toggle-label" id="themeToggleLabel"><?= e(t('layout.theme_label_light')) ?></span>
                    </button>

                    <a href="<?= e(localized_url($nextLocale)) ?>" class="nav-pill nav-pill-language" aria-label="<?= e(t('layout.language_toggle_aria')) ?>">
                        <?= e(t('layout.language_toggle')) ?>
                    </a>

                    <?php if (is_auth()): ?>
                        <?php if (is_admin()): ?>
                            <a href="<?= e(url('/admin')) ?>" class="nav-pill nav-pill-strong"><?= e(t('layout.admin')) ?></a>
                        <?php endif; ?>
                        <?php if ($profileCompleted): ?>
                            <?php if (!$isOrdersPage): ?>
                                <a href="<?= e(url('/orders')) ?>" class="nav-pill"><?= e(t('layout.orders')) ?></a>
                            <?php endif; ?>

                            <?php if (!$isCartPage): ?>
                                <a href="<?= e(url('/cart')) ?>" class="nav-pill nav-pill-cart">
                                    <?= e(t('layout.cart')) ?>
                                    <span class="nav-count" id="siteCartCount"><?= e((string) $cartItemsCount) ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if ($isProductsPage || $isCartPage): ?>
                                <a href="<?= e(url('/checkout')) ?>" class="nav-pill"><?= e(t('layout.checkout')) ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= e(url('/complete-profile')) ?>" class="nav-pill"><?= e(t('layout.complete_profile')) ?></a>
                        <?php endif; ?>

                        <span class="nav-user"><?= e(t('layout.hi', ['name' => (string) ($user['name'] ?? 'Guest')])) ?></span>
                        <a href="<?= e(url('/logout')) ?>" class="nav-pill nav-pill-strong"><?= e(t('layout.logout')) ?></a>
                    <?php else: ?>
                        <a href="<?= e(url('/login')) ?>" class="nav-pill"><?= e(t('layout.login')) ?></a>
                        <a href="<?= e(url('/register')) ?>" class="nav-pill nav-pill-strong"><?= e(t('layout.register')) ?></a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="page-container">
            <?php if ($enableGlobalMusic): ?>
                <script>window.__nautshopMusicManaged = true;</script>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>

    <?php if ($enableGlobalMusic): ?>
        <div id="globalStorePlaylistYoutube" class="music-youtube-host" aria-hidden="true"></div>
        <script type="application/json" id="globalStorePlaylistData"><?= json_encode(store_playlist(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>

    <script>
        (function () {
            const body = document.body;
            const toggle = document.getElementById('themeToggle');
            const label = document.getElementById('themeToggleLabel');
            const icon = document.getElementById('themeToggleIcon');
            const flashToast = document.getElementById('flashToast');
            const flashToastClose = document.getElementById('flashToastClose');
            const inlineAlerts = document.querySelectorAll('.alert');

            const applyThemeLabel = function (theme) {
                if (!toggle || !label || !icon) {
                    return;
                }

                if (theme === 'dark') {
                    label.textContent = toggle.dataset.labelDark || '';
                    icon.textContent = toggle.dataset.iconDark || '';
                } else {
                    label.textContent = toggle.dataset.labelLight || '';
                    icon.textContent = toggle.dataset.iconLight || '';
                }
            };

            const savedTheme = localStorage.getItem('nautshop-theme');
            if (savedTheme === 'dark') {
                body.setAttribute('data-theme', 'dark');
            }
            applyThemeLabel(body.getAttribute('data-theme') || 'light');

            if (toggle) {
                toggle.addEventListener('click', function () {
                    const current = body.getAttribute('data-theme') || 'light';
                    const next = current === 'light' ? 'dark' : 'light';

                    body.setAttribute('data-theme', next);
                    localStorage.setItem('nautshop-theme', next);
                    applyThemeLabel(next);
                });
            }

            const showFlashToast = function (message, kind) {
                if (!message) {
                    return;
                }

                let toastWrap = document.querySelector('.flash-toast-wrap');
                if (!toastWrap) {
                    toastWrap = document.createElement('div');
                    toastWrap.className = 'flash-toast-wrap';
                    document.body.appendChild(toastWrap);
                }

                const toast = document.createElement('div');
                toast.className = 'flash-toast' + (kind === 'error' ? ' error' : ' success');
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');

                const badge = document.createElement('span');
                badge.className = 'flash-toast-icon-badge';
                badge.setAttribute('aria-hidden', 'true');
                badge.textContent = 'i';

                const content = document.createElement('div');
                content.className = 'flash-toast-content';
                const msgSpan = document.createElement('span');
                msgSpan.className = 'flash-toast-message';
                msgSpan.textContent = message;
                content.appendChild(msgSpan);

                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'flash-toast-close';
                closeBtn.setAttribute('aria-label', <?= json_encode(t('layout.alert_close'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
                closeBtn.textContent = 'x';

                const progress = document.createElement('span');
                progress.className = 'flash-toast-progress';
                progress.setAttribute('aria-hidden', 'true');

                toast.appendChild(badge);
                toast.appendChild(content);
                toast.appendChild(closeBtn);
                toast.appendChild(progress);
                toastWrap.appendChild(toast);

                const hideToast = function () {
                    toast.classList.add('is-hiding');
                    window.setTimeout(function () {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 260);
                };

                window.setTimeout(hideToast, 6500);
                closeBtn.addEventListener('click', hideToast);
            };

            window.showFlashToast = showFlashToast;

            if (flashToast) {
                const hideToast = function () {
                    flashToast.classList.add('is-hiding');
                    window.setTimeout(function () {
                        if (flashToast.parentNode) {
                            flashToast.parentNode.removeChild(flashToast);
                        }
                    }, 260);
                };

                window.setTimeout(hideToast, 6500);

                if (flashToastClose) {
                    flashToastClose.addEventListener('click', hideToast);
                }
            }

            inlineAlerts.forEach(function (alertNode) {
                // Lấy text nội dung alert
                const message = alertNode.textContent.trim();
                const isSuccess = alertNode.classList.contains('success');
                const isError = alertNode.classList.contains('error');

                // Xoá alert gốc khỏi DOM ngay
                if (alertNode.parentNode) {
                    alertNode.parentNode.removeChild(alertNode);
                }

                showFlashToast(message, isError ? 'error' : (isSuccess ? 'success' : 'success'));
            });
        })();
    </script>

    <?php if ($enableGlobalMusic): ?>
    <script>
        (function () {
            window.__nautshopMusicManaged = true;

            const playerHost = document.getElementById('globalStorePlaylistYoutube');
            const playlistNode = document.getElementById('globalStorePlaylistData');
            const playerStateKey = 'nautshop_player_state_v3';

            if (!playerHost || !playlistNode) {
                return;
            }

            let playlist = [];

            try {
                playlist = JSON.parse(playlistNode.textContent || '[]');
            } catch (error) {
                playlist = [];
            }

            if (!playlist.length) {
                return;
            }

            let ytPlayer = null;
            let playerReady = false;
            let visualizerFrame = 0;
            let currentTrackIndex = 0;
            let pendingSeek = 0;
            let isSwitchingTrack = false;
            let currentUi = null;

            const readPlayerState = function () {
                try {
                    const stored = window.localStorage.getItem(playerStateKey);
                    const parsed = stored ? JSON.parse(stored) : null;
                    return parsed && typeof parsed === 'object' ? parsed : null;
                } catch (error) {
                    return null;
                }
            };

            const writePlayerState = function (state) {
                window.localStorage.setItem(playerStateKey, JSON.stringify(state));
            };

            const formatTime = function (seconds) {
                if (!Number.isFinite(seconds) || seconds < 0) {
                    return '00:00';
                }

                const minutes = Math.floor(seconds / 60);
                const remainder = Math.floor(seconds % 60);
                return String(minutes).padStart(2, '0') + ':' + String(remainder).padStart(2, '0');
            };

            const getCurrentTrack = function () {
                return playlist[currentTrackIndex];
            };

            const getUi = function () {
                const musicCard = document.getElementById('heroMusicCard');

                if (!musicCard) {
                    return null;
                }

                return {
                    musicCard: musicCard,
                    title: document.getElementById('musicTrackTitle'),
                    artist: document.getElementById('musicTrackArtist'),
                    currentTimeNode: document.getElementById('musicCurrentTime'),
                    durationNode: document.getElementById('musicDuration'),
                    progress: document.getElementById('musicProgress'),
                    volume: document.getElementById('musicVolume'),
                    playBtn: document.getElementById('musicPlayBtn'),
                    prevBtn: document.getElementById('musicPrevBtn'),
                    nextBtn: document.getElementById('musicNextBtn'),
                    trackCounter: document.getElementById('musicTrackCounter'),
                    rhythmBars: Array.prototype.slice.call(document.querySelectorAll('#musicRhythmMark span')),
                };
            };

            const enrichUi = function (ui) {
                if (!ui) {
                    return null;
                }

                ui.baseRhythmHeights = ui.rhythmBars.map(function (_, index) {
                    const midpoint = (ui.rhythmBars.length - 1) / 2;
                    const distance = Math.abs(index - midpoint);
                    const ratio = midpoint > 0 ? 1 - (distance / midpoint) : 1;
                    return Math.round(32 + Math.max(0.22, ratio) * 78);
                });

                return ui;
            };

            const setRhythmHeights = function (levels) {
                if (!currentUi || !currentUi.rhythmBars.length) {
                    return;
                }

                currentUi.rhythmBars.forEach(function (bar, index) {
                    const nextHeight = levels[index] || currentUi.baseRhythmHeights[index] || 32;
                    const scale = 0.94 + (nextHeight / 170) * 0.12;
                    bar.style.height = nextHeight + 'px';
                    bar.style.opacity = String(Math.min(1, 0.55 + nextHeight / 190));
                    bar.style.transform = 'scaleY(' + scale.toFixed(3) + ')';
                });
            };

            const resetRhythmBars = function () {
                if (!currentUi) {
                    return;
                }

                setRhythmHeights(currentUi.baseRhythmHeights);
            };

            const renderRhythmFromTime = function (relativeTime) {
                if (!currentUi || !currentUi.rhythmBars.length) {
                    return;
                }

                const midpoint = (currentUi.rhythmBars.length - 1) / 2;
                const levels = currentUi.rhythmBars.map(function (_, index) {
                    const distance = Math.abs(index - midpoint);
                    const spread = 1 - Math.min(1, distance / Math.max(1, midpoint));
                    const waveA = (Math.sin(relativeTime * 4.1 + index * 0.42) + 1) / 2;
                    const waveB = (Math.sin(relativeTime * 2.3 - index * 0.21) + 1) / 2;
                    const waveC = (Math.sin(relativeTime * 1.25 + index * 0.12) + 1) / 2;
                    const intensity = (waveA * 0.52) + (waveB * 0.28) + (waveC * 0.2);
                    return Math.round((currentUi.baseRhythmHeights[index] || 32) + intensity * (28 + spread * 58));
                });

                setRhythmHeights(levels);
            };

            const renderPlayIcon = function (playing) {
                if (!currentUi || !currentUi.playBtn) {
                    return;
                }

                currentUi.playBtn.dataset.playing = playing ? 'true' : 'false';
                currentUi.playBtn.setAttribute('aria-label', playing ? (currentUi.musicCard.dataset.pauseLabel || '') : (currentUi.musicCard.dataset.playLabel || ''));
                currentUi.playBtn.innerHTML = playing
                    ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h3v12H8V6Zm5 0h3v12h-3V6Z"></path></svg>'
                    : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 7 8 5-8 5V7Z"></path></svg>';
            };

            const getRelativeTime = function () {
                if (!playerReady || !ytPlayer) {
                    return pendingSeek || 0;
                }

                const track = getCurrentTrack();
                const absoluteTime = Number(ytPlayer.getCurrentTime()) || track.start || 0;
                return Math.max(0, Math.min(track.duration || 0, absoluteTime - (track.start || 0)));
            };

            const playerState = readPlayerState() || {
                trackIndex: 0,
                currentTime: 0,
                volume: 1,
                isPlaying: true,
            };

            if (typeof playerState.trackIndex !== 'number' || playerState.trackIndex < 0 || playerState.trackIndex >= playlist.length) {
                playerState.trackIndex = 0;
            }

            if (typeof playerState.currentTime !== 'number' || playerState.currentTime < 0) {
                playerState.currentTime = 0;
            }

            if (typeof playerState.volume !== 'number' || playerState.volume < 0 || playerState.volume > 1) {
                playerState.volume = 1;
            }

            if (typeof playerState.isPlaying !== 'boolean') {
                playerState.isPlaying = true;
            }

            currentTrackIndex = playerState.trackIndex;
            pendingSeek = playerState.currentTime;

            const saveState = function () {
                const currentVolume = playerReady && ytPlayer && typeof ytPlayer.getVolume === 'function'
                    ? ytPlayer.getVolume() / 100
                    : playerState.volume;

                writePlayerState({
                    trackIndex: currentTrackIndex,
                    currentTime: getRelativeTime(),
                    volume: currentVolume,
                    isPlaying: playerReady && ytPlayer && typeof ytPlayer.getPlayerState === 'function'
                        ? ytPlayer.getPlayerState() === window.YT.PlayerState.PLAYING
                        : playerState.isPlaying,
                });
            };

            const syncTrackMeta = function () {
                if (!currentUi) {
                    return;
                }

                const track = getCurrentTrack();

                if (currentUi.title) {
                    currentUi.title.textContent = track.title;
                }

                if (currentUi.artist) {
                    currentUi.artist.textContent = track.artist;
                }

                if (currentUi.durationNode) {
                    currentUi.durationNode.textContent = track.length || formatTime(track.duration || 0);
                }

                if (currentUi.currentTimeNode) {
                    currentUi.currentTimeNode.textContent = '00:00';
                }

                if (currentUi.progress) {
                    currentUi.progress.value = '0';
                }

                if (currentUi.volume) {
                    currentUi.volume.value = String(playerState.volume);
                }

                if (currentUi.trackCounter) {
                    currentUi.trackCounter.textContent = (currentUi.musicCard.dataset.trackLabel || 'Track') + ' ' + (currentTrackIndex + 1) + ' / ' + playlist.length;
                }
            };

            const syncProgressUi = function () {
                if (!currentUi) {
                    return getRelativeTime();
                }

                const track = getCurrentTrack();
                const relativeTime = getRelativeTime();

                if (currentUi.currentTimeNode) {
                    currentUi.currentTimeNode.textContent = formatTime(relativeTime);
                }

                if (currentUi.durationNode) {
                    currentUi.durationNode.textContent = track.length || formatTime(track.duration || 0);
                }

                if (currentUi.progress) {
                    currentUi.progress.value = track.duration ? String((relativeTime / track.duration) * 100) : '0';
                }

                return relativeTime;
            };

            const stopVisualizer = function () {
                if (visualizerFrame) {
                    window.cancelAnimationFrame(visualizerFrame);
                    visualizerFrame = 0;
                }

                resetRhythmBars();
            };

            const syncUiState = function () {
                currentUi = enrichUi(getUi());
                syncTrackMeta();
                syncProgressUi();
                renderPlayIcon(playerState.isPlaying);
                resetRhythmBars();
            };

            const loadTrack = function (index, autoplay, seekSeconds) {
                currentTrackIndex = index;
                pendingSeek = typeof seekSeconds === 'number' && seekSeconds > 0 ? seekSeconds : 0;
                isSwitchingTrack = true;
                syncTrackMeta();

                if (!playerReady || !ytPlayer) {
                    return;
                }

                const track = getCurrentTrack();
                const videoOptions = {
                    videoId: track.videoId,
                    startSeconds: track.start,
                    endSeconds: track.end,
                    suggestedQuality: 'small',
                };

                if (autoplay) {
                    ytPlayer.loadVideoById(videoOptions);
                } else {
                    ytPlayer.cueVideoById(videoOptions);
                    renderPlayIcon(false);
                }

                saveState();
            };

            const goToAdjacentTrack = function (direction) {
                const nextIndex = direction === 'prev'
                    ? (currentTrackIndex === 0 ? playlist.length - 1 : currentTrackIndex - 1)
                    : (currentTrackIndex === playlist.length - 1 ? 0 : currentTrackIndex + 1);

                playerState.isPlaying = true;
                loadTrack(nextIndex, true, 0);
            };

            const startVisualizer = function () {
                if (!playerReady || !ytPlayer || visualizerFrame || !currentUi) {
                    return;
                }

                const renderBars = function () {
                    if (!playerReady || !ytPlayer || ytPlayer.getPlayerState() !== window.YT.PlayerState.PLAYING) {
                        visualizerFrame = 0;
                        resetRhythmBars();
                        return;
                    }

                    const relativeTime = syncProgressUi();
                    renderRhythmFromTime(relativeTime);

                    if (relativeTime >= (getCurrentTrack().duration - 0.18)) {
                        visualizerFrame = 0;
                        goToAdjacentTrack('next');
                        return;
                    }

                    visualizerFrame = window.requestAnimationFrame(renderBars);
                };

                visualizerFrame = window.requestAnimationFrame(renderBars);
            };

            const loadYouTubeApi = function () {
                if (window.YT && window.YT.Player) {
                    return Promise.resolve(window.YT);
                }

                if (!window.__nautshopYouTubeApiPromise) {
                    window.__nautshopYouTubeApiPromise = new Promise(function (resolve) {
                        const previousReady = window.onYouTubeIframeAPIReady;

                        window.onYouTubeIframeAPIReady = function () {
                            if (typeof previousReady === 'function') {
                                previousReady();
                            }

                            resolve(window.YT);
                        };

                        if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
                            const script = document.createElement('script');
                            script.src = 'https://www.youtube.com/iframe_api';
                            document.head.appendChild(script);
                        }
                    });
                }

                return window.__nautshopYouTubeApiPromise;
            };

            const bindUiEvents = function () {
                if (!currentUi || currentUi.musicCard.dataset.musicBound === 'true') {
                    return;
                }

                currentUi.musicCard.dataset.musicBound = 'true';

                if (currentUi.playBtn) {
                    currentUi.playBtn.addEventListener('click', function () {
                        if (!playerReady || !ytPlayer) {
                            return;
                        }

                        const state = ytPlayer.getPlayerState();
                        if (state === window.YT.PlayerState.PLAYING || state === window.YT.PlayerState.BUFFERING) {
                            ytPlayer.pauseVideo();
                            playerState.isPlaying = false;
                            return;
                        }

                        playerState.isPlaying = true;
                        ytPlayer.playVideo();
                    });
                }

                if (currentUi.prevBtn) {
                    currentUi.prevBtn.addEventListener('click', function () {
                        goToAdjacentTrack('prev');
                    });
                }

                if (currentUi.nextBtn) {
                    currentUi.nextBtn.addEventListener('click', function () {
                        goToAdjacentTrack('next');
                    });
                }

                if (currentUi.progress) {
                    currentUi.progress.addEventListener('input', function () {
                        if (!playerReady || !ytPlayer) {
                            return;
                        }

                        const track = getCurrentTrack();
                        const relativeTime = (parseFloat(currentUi.progress.value) / 100) * track.duration;
                        pendingSeek = 0;
                        ytPlayer.seekTo(track.start + relativeTime, true);
                        syncProgressUi();
                        renderRhythmFromTime(relativeTime);
                        saveState();
                    });
                }

                if (currentUi.volume) {
                    currentUi.volume.addEventListener('input', function () {
                        playerState.volume = parseFloat(currentUi.volume.value || '1');

                        if (!playerReady || !ytPlayer) {
                            saveState();
                            return;
                        }

                        ytPlayer.setVolume(Math.round(playerState.volume * 100));
                        saveState();
                    });
                }
            };

            syncUiState();
            bindUiEvents();

            loadYouTubeApi().then(function () {
                ytPlayer = new window.YT.Player(playerHost, {
                    width: '1',
                    height: '1',
                    videoId: getCurrentTrack().videoId,
                    playerVars: {
                        autoplay: 0,
                        controls: 0,
                        disablekb: 1,
                        fs: 0,
                        iv_load_policy: 3,
                        modestbranding: 1,
                        playsinline: 1,
                        rel: 0,
                        origin: window.location.origin,
                    },
                    events: {
                        onReady: function () {
                            playerReady = true;
                            ytPlayer.setVolume(Math.round(playerState.volume * 100));
                            loadTrack(currentTrackIndex, playerState.isPlaying, pendingSeek);
                        },
                        onStateChange: function (event) {
                            const state = event.data;

                            if (state === window.YT.PlayerState.PLAYING) {
                                const track = getCurrentTrack();

                                if (pendingSeek > 0) {
                                    ytPlayer.seekTo(track.start + Math.min(pendingSeek, Math.max(0, track.duration - 0.2)), true);
                                    pendingSeek = 0;
                                }

                                isSwitchingTrack = false;
                                playerState.isPlaying = true;
                                renderPlayIcon(true);
                                syncProgressUi();
                                startVisualizer();
                                saveState();
                                return;
                            }

                            if (state === window.YT.PlayerState.PAUSED || state === window.YT.PlayerState.CUED) {
                                playerState.isPlaying = false;
                                renderPlayIcon(false);
                                syncProgressUi();
                                stopVisualizer();
                                saveState();
                                return;
                            }

                            if (state === window.YT.PlayerState.ENDED && !isSwitchingTrack) {
                                goToAdjacentTrack('next');
                            }
                        },
                        onError: function () {
                            playerState.isPlaying = false;
                            renderPlayIcon(false);
                            stopVisualizer();
                        },
                    },
                });
            }).catch(function () {
                renderPlayIcon(false);
            });

            window.addEventListener('pagehide', function () {
                stopVisualizer();
                saveState();
            });
        })();
    </script>
    <?php endif; ?>
</body>
</html>
