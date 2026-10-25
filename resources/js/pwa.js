import { ref, onMounted } from 'vue';

const deferredPrompt = ref(null);
const isInstallable = ref(false);
const isInstalled = ref(false);
const isIos = ref(false);
const isDismissed = ref(false);

export function usePwa() {
    const checkIfInstalled = () => {
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true ||
            document.referrer.includes('android-app://');
        
        isInstalled.value = isStandalone;
        return isStandalone;
    };

    const checkIfIos = () => {
        const userAgent = window.navigator.userAgent.toLowerCase();
        return /iphone|ipad|ipod/.test(userAgent) && !window.MSStream;
    };

    const initPwa = () => {
        if (typeof window === 'undefined') return;

        if (checkIfInstalled()) {
            isInstallable.value = false;
            return;
        }

        isIos.value = checkIfIos();

        // Check if previously dismissed recently
        const dismissedAt = localStorage.getItem('tera_pwa_dismissed_at');
        if (dismissedAt) {
            const timeDiff = Date.now() - parseInt(dismissedAt, 10);
            // 3 days cooldown
            if (timeDiff < 3 * 24 * 60 * 60 * 1000) {
                isDismissed.value = true;
            }
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            deferredPrompt.value = e;
            if (!isDismissed.value && !isInstalled.value) {
                isInstallable.value = true;
            }
        });

        window.addEventListener('appinstalled', () => {
            deferredPrompt.value = null;
            isInstallable.value = false;
            isInstalled.value = true;
            console.log('[PWA] TERA R&D Workspace berhasil dipasang.');
        });
    };

    const promptInstall = async () => {
        if (!deferredPrompt.value) return false;

        deferredPrompt.value.prompt();
        const choiceResult = await deferredPrompt.value.userChoice;

        if (choiceResult.outcome === 'accepted') {
            console.log('[PWA] Pengguna menerima pemasangan aplikasi');
            isInstallable.value = false;
            isInstalled.value = true;
        } else {
            console.log('[PWA] Pengguna menolak pemasangan aplikasi');
        }

        deferredPrompt.value = null;
        return choiceResult.outcome === 'accepted';
    };

    const dismissPrompt = () => {
        isDismissed.value = true;
        isInstallable.value = false;
        localStorage.setItem('tera_pwa_dismissed_at', Date.now().toString());
    };

    return {
        deferredPrompt,
        isInstallable,
        isInstalled,
        isIos,
        isDismissed,
        initPwa,
        promptInstall,
        dismissPrompt,
    };
}
