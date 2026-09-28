import * as pdfjsLib from 'pdfjs-dist';
import workerSrc from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc;

/* Merender halaman pertama PDF ke <canvas> sebagai thumbnail daftar
   factsheet. Hanya dipakai untuk berkas yang diunggah lewat CMS
   (satu origin); tautan luar tidak dirender karena CORS. */
async function renderThumb(canvas) {
    const url = canvas.dataset.pdfThumb;
    try {
        const pdf = await pdfjsLib.getDocument({
            url,
            withCredentials: false,
        }).promise;
        const page = await pdf.getPage(1);
        const scale = 360 / page.getViewport({ scale: 1 }).width;
        const viewport = page.getViewport({ scale });
        canvas.width = Math.floor(viewport.width);
        canvas.height = Math.floor(viewport.height);
        // Rasio asli halaman menggantikan placeholder aspect.
        canvas.classList.remove('aspect-[210/297]');
        await page.render({
            canvasContext: canvas.getContext('2d'),
            viewport,
        }).promise;
        await pdf.destroy();
    } catch (err) {
        // Gagal dimuat (CORS, berkas hilang/korup): placeholder generik.
        console.warn('[pdf-thumb] gagal merender', url, err);
        const fallback = document.createElement('div');
        fallback.className =
            'flex aspect-[210/297] w-full items-center justify-center bg-gray-100 text-xs font-medium text-gray-400';
        fallback.textContent = 'PDF';
        canvas.replaceWith(fallback);
    }
}

export function initPdfThumbs(nodes) {
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(
            (entries, obs) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        obs.unobserve(entry.target);
                        renderThumb(entry.target);
                    }
                }
            },
            { rootMargin: '200px' },
        );
        nodes.forEach((canvas) => io.observe(canvas));
    } else {
        nodes.forEach(renderThumb);
    }
}
