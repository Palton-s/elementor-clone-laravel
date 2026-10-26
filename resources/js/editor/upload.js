import state from './state.js';
import { showToast, toastSuccess, toastError, apiFetch } from './utils.js';

export function uploadImageFile(file, callback) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('page_id', state.pageId);
    showToast('Enviando imagem...', 'info');
    apiFetch('/page-builder/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
        body: formData,
    })
    .then(data => {
        if (data.url) { toastSuccess('Imagem enviada!'); callback(data.url); }
        else toastError('Falha ao enviar imagem');
    })
    .catch(() => toastError('Falha ao enviar imagem'));
}

// Same limit as the server validation (ElementController::uploadVideo, max:51200 KB)
const MAX_VIDEO_MB = 50;

export function uploadVideoFile(file, callback) {
    if (file.size > MAX_VIDEO_MB * 1024 * 1024) {
        toastError(`O vídeo tem ${(file.size / 1048576).toFixed(1)} MB; o limite é ${MAX_VIDEO_MB} MB. Comprima o vídeo ou use um link do YouTube/Vimeo.`);
        return;
    }
    const formData = new FormData();
    formData.append('video', file);
    formData.append('page_id', state.pageId);
    showToast('Enviando vídeo...', 'info');
    apiFetch('/page-builder/upload-video', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
        body: formData,
    })
    .then(data => {
        if (data.url) { toastSuccess('Vídeo enviado!'); callback(data.url); }
        else toastError('Falha ao enviar vídeo');
    })
    .catch(err => toastError('Falha ao enviar vídeo: ' + (err.message || err)));
}
