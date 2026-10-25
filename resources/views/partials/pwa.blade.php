<style>
    #pwaInstallButton[hidden],
    #pwaInstallHelp[hidden] { display: none !important; }

    #pwaInstallButton {
        position: fixed;
        right: 1rem;
        bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
        z-index: 1060;
        padding: .75rem 1rem;
        border: 0;
        border-radius: 999px;
        background: #0f2747;
        color: #fff;
        font: 600 14px/1.2 system-ui, sans-serif;
        box-shadow: 0 4px 16px #0f274744;
        cursor: pointer;
    }

    #pwaInstallHelp {
        position: fixed;
        right: 1rem;
        bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px));
        z-index: 1060;
        width: min(24rem, calc(100vw - 2rem));
        padding: 1rem 1.1rem;
        border: 1px solid #dbe3ee;
        border-radius: .85rem;
        background: #fff;
        color: #1f2937;
        box-shadow: 0 12px 36px #0f274733;
        font: 14px/1.5 system-ui, sans-serif;
    }

    #pwaInstallHelpTitle { margin: 0 2rem .35rem 0; font-size: 15px; }
    #pwaInstallHelpMessage { margin: 0; color: #475569; }
    #pwaInstallHelpClose {
        position: absolute;
        top: .55rem;
        right: .65rem;
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 1.4rem;
        line-height: 1;
        cursor: pointer;
    }

    @media (max-width: 480px) {
        #pwaInstallButton { right: .75rem; }
        #pwaInstallHelp { right: .75rem; width: min(24rem, calc(100vw - 1.5rem)); }
    }
</style>

<button id="pwaInstallButton" type="button" aria-expanded="false" aria-controls="pwaInstallHelp">
    Install app
</button>
<section id="pwaInstallHelp" hidden aria-live="polite" aria-atomic="true">
    <button id="pwaInstallHelpClose" type="button" aria-label="Close install help">&times;</button>
    <h2 id="pwaInstallHelpTitle">Install Hotel Management</h2>
    <p id="pwaInstallHelpMessage"></p>
</section>

<script>
    (() => {
        const installButton = document.getElementById('pwaInstallButton');
        const helpPanel = document.getElementById('pwaInstallHelp');
        const helpTitle = document.getElementById('pwaInstallHelpTitle');
        const helpMessage = document.getElementById('pwaInstallHelpMessage');
        const closeHelpButton = document.getElementById('pwaInstallHelpClose');
        let installPrompt = null;
        let serviceWorkerRegistration = null;
        let serviceWorkerError = null;
        let serviceWorkerState = 'not registered';
        let trackedWorker = null;

        const isInstalled = () => window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;

        const showHelp = (message, title = 'Install Hotel Management') => {
            helpTitle.textContent = title;
            helpMessage.textContent = message;
            helpPanel.hidden = false;
            installButton.setAttribute('aria-expanded', 'true');
        };

        const closeHelp = () => {
            helpPanel.hidden = true;
            installButton.setAttribute('aria-expanded', 'false');
        };

        const showBrowserInstructions = () => {
            const userAgent = navigator.userAgent || '';
            const isIos = /iphone|ipad|ipod/i.test(userAgent)
                || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            const isAndroid = /android/i.test(userAgent);
            const isEdge = /edg/i.test(userAgent);
            const isChrome = /chrome|chromium/i.test(userAgent) && !isEdge;
            const isSafari = /safari/i.test(userAgent) && !/chrome|chromium|crios|edg/i.test(userAgent);

            if (isIos) {
                return 'In Safari, tap Share, then choose "Add to Home Screen".';
            }

            if (isAndroid) {
                return 'Open your browser menu, then choose "Install app" or "Add to Home screen".';
            }

            if (isSafari) {
                return 'In Safari, open File and choose "Add to Dock".';
            }

            if (isEdge) {
                return 'In Edge, open the menu, choose Apps, then "Install this site as an app".';
            }

            if (isChrome) {
                return 'In Chrome, open the menu, choose "Cast, save, and share", then "Install page as app". You can also use the install icon at the right side of the address bar.';
            }

            return 'Open the browser menu and choose "Install app" or "Install Hotel Management". In Chrome or Edge, look for the install icon at the right side of the address bar.';
        };

        const waitForActiveServiceWorker = async () => {
            if (!('serviceWorker' in navigator) || serviceWorkerRegistration?.active) {
                return;
            }

            const readyRegistration = await Promise.race([
                navigator.serviceWorker.ready,
                new Promise((resolve) => window.setTimeout(() => resolve(null), 8000)),
            ]);

            if (readyRegistration) {
                serviceWorkerRegistration = readyRegistration;
                serviceWorkerState = readyRegistration.active?.state || 'activated';
            }
        };

        const launchInstallPrompt = async () => {
            const prompt = installPrompt;
            installPrompt = null;

            try {
                await prompt.prompt();
                const choice = await prompt.userChoice;

                if (choice.outcome === 'accepted') {
                    installButton.hidden = true;
                    return;
                }

                showHelp('The install prompt was dismissed. You can install later from your browser menu.');
            } catch (error) {
                console.error('PWA install prompt failed.', error);
                showInstallGuidance();
            }
        };

        const showInstallGuidance = () => {
            if (!window.isSecureContext) {
                showHelp('Installation requires a secure HTTPS connection. HTTP is supported only when you open the app as localhost.');
                return;
            }

            if (!('serviceWorker' in navigator)) {
                showHelp('This browser does not support the service worker needed to install the app. Try a current version of Chrome, Edge, or Safari.');
                return;
            }

            if (serviceWorkerError) {
                showHelp(`The service worker failed to install (${serviceWorkerError.message}). In Chrome or Edge, open DevTools > Application > Service Workers, unregister this app, then reload.`, 'Install service unavailable');
                return;
            }

            if (!serviceWorkerRegistration || !serviceWorkerRegistration.active) {
                showHelp(`The service worker is not active yet (state: ${serviceWorkerState}). Wait a moment while it finishes installing. If it remains here, open DevTools > Application > Service Workers and check the worker status.`, 'Install service is starting');
                return;
            }

            showHelp(showBrowserInstructions());
        };

        if (isInstalled()) {
            installButton.hidden = true;
        }

        if ('serviceWorker' in navigator && window.isSecureContext) {
            const trackWorker = (worker) => {
                if (!worker || worker === trackedWorker) {
                    return;
                }

                trackedWorker = worker;
                serviceWorkerState = worker.state;
                worker.addEventListener('statechange', () => {
                    serviceWorkerState = worker.state;

                    if (worker.state === 'redundant' && !serviceWorkerRegistration?.active) {
                        serviceWorkerError = new Error('The worker became redundant before activation.');
                        console.error('PWA service worker did not activate.', serviceWorkerError);
                    }
                });
            };

            navigator.serviceWorker.register(@json(asset('sw.js')), { updateViaCache: 'none' })
                .then((registration) => {
                    serviceWorkerRegistration = registration;
                    serviceWorkerState = registration.active?.state
                        || registration.installing?.state
                        || registration.waiting?.state
                        || 'registered without an active worker';
                    trackWorker(registration.installing);
                    trackWorker(registration.waiting);
                    registration.addEventListener('updatefound', () => trackWorker(registration.installing));
                })
                .catch((error) => {
                    serviceWorkerError = error;
                    console.error('PWA service worker registration failed.', error);
                });

            navigator.serviceWorker.ready.then((registration) => {
                serviceWorkerRegistration = registration;
                serviceWorkerState = registration.active?.state || 'activated';
            });
        }

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            installPrompt = event;
            installButton.hidden = false;
        });

        installButton.addEventListener('click', async () => {
            closeHelp();

            if (!installPrompt) {
                showHelp('Checking the app install service...', 'Preparing install options');
                await waitForActiveServiceWorker();

                if (installPrompt) {
                    closeHelp();
                    await launchInstallPrompt();
                    return;
                }

                showInstallGuidance();
                return;
            }

            await launchInstallPrompt();
        });

        closeHelpButton.addEventListener('click', closeHelp);

        window.addEventListener('appinstalled', () => {
            installButton.hidden = true;
            closeHelp();
        });
    })();
</script>
