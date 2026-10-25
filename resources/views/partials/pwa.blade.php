<style>
    #pwaInstallButton[hidden] { display: none !important; }
</style>
<button id="pwaInstallButton" type="button" hidden style="position: fixed; right: 1rem; bottom: 1rem; z-index: 1060; padding: .7rem 1rem; border: 0; border-radius: 999px; background: #0f2747; color: #fff; font: 600 14px/1.2 system-ui, sans-serif; box-shadow: 0 4px 16px #0f274744; cursor: pointer;">Install app</button>
<script>
    (() => {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(@json(asset('sw.js'))).catch(() => {});
            });
        }

        const installButton = document.getElementById('pwaInstallButton');
        let installPrompt = null;

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            installPrompt = event;

            if (installButton) {
                installButton.hidden = false;
            }
        });

        installButton?.addEventListener('click', async () => {
            if (!installPrompt) {
                return;
            }

            installPrompt.prompt();
            await installPrompt.userChoice;
            installPrompt = null;
            installButton.hidden = true;
        });

        window.addEventListener('appinstalled', () => {
            if (installButton) {
                installButton.hidden = true;
            }
        });
    })();
</script>
