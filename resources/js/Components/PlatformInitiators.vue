<script setup>
defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div :class="compact ? 'space-y-2 text-center' : 'space-y-3'">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
            Inisiator Platform
        </div>

        <div class="flex flex-wrap items-center justify-center gap-5 sm:gap-7">
            <!-- PT Yota Inovasi Nusantara (YOIN) -->
            <a
                href="https://yotainovasi.id/"
                target="_blank"
                rel="noopener noreferrer"
                title="PT Yota Inovasi Nusantara (YOIN) | Transformasi Digital, Jasa Website & Ekosistem Teknologi"
                class="inline-flex items-center transition-transform hover:scale-105 opacity-90 hover:opacity-100"
            >
                <img
                    src="https://yotainovasi.id/logotulisan.png"
                    alt="PT Yota Inovasi Nusantara (YOIN) | Transformasi Digital, Jasa Website & Ekosistem Teknologi"
                    class="h-6 sm:h-7 w-auto object-contain max-w-[140px]"
                    loading="lazy"
                />
            </a>

            <div class="w-px h-5 bg-slate-200"></div>

            <!-- Yota Adiwidya Center (SIYOTA) -->
            <a
                href="https://siyota.org/"
                target="_blank"
                rel="noopener noreferrer"
                title="Beranda | Yota Adiwidya Center"
                class="inline-flex items-center transition-transform hover:scale-105 opacity-90 hover:opacity-100"
            >
                <img
                    src="https://siyota.org/image/logo.png"
                    alt="Beranda | Yota Adiwidya Center"
                    class="h-6 sm:h-7 w-auto object-contain max-w-[130px]"
                    loading="lazy"
                />
            </a>
        </div>
    </div>
</template>
