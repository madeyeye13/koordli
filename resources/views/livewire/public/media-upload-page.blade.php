<div style="max-width:640px;margin:0 auto;padding:32px 20px;min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    @if(!$isUsable)
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:32px;margin-bottom:12px;">🔒</div>
            <h2 style="font-size:18px;font-weight:600;color:#1C1917;margin-bottom:8px;">This link is no longer available</h2>
            <p style="font-size:13px;color:#78716C;">Please reach out to {{ $companyName }} if you believe this is an error.</p>
        </div>
    @else
        <div style="text-align:center;margin-bottom:24px;">
            <h2 style="font-size:18px;font-weight:600;color:#1C1917;">Upload files for {{ $link->event->name }}</h2>
            <p style="font-size:13px;color:#78716C;margin-top:4px;">Shared by {{ $companyName }} — no login required.</p>
        </div>

        <div style="border:2px dashed #DDD6FE;border-radius:8px;padding:32px;text-align:center;margin-bottom:20px;">
            <button type="button" onclick="document.getElementById('guest-file-input').click()"
                style="background:#7C3AED;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:13px;cursor:pointer;">
                Choose Files
            </button>
            <input type="file" id="guest-file-input" multiple style="display:none;"
                onchange="window.GuestUploader.enqueue(this.files)">
            <p style="font-size:11px;color:#A8A29E;margin-top:10px;">Up to 20 files per batch, 750MB each.</p>
        </div>

        <div id="guest-upload-progress" style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;"></div>

        <div id="guest-upload-done" style="margin-top:20px;text-align:center;color:#10B981;font-size:13px;display:none;">
            ✓ Files uploaded successfully.
        </div>

        <script>
            window.GuestUploader = (function () {
                const CHUNK_SIZE = 8 * 1024 * 1024;
                const MAX_BATCH = 20;
                const shareToken = @js($link->token);

                function uuid() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
                        const r = (Math.random() * 16) | 0;
                        return (c === 'x' ? r : (r & 0x3) | 0x8).toString(16);
                    });
                }

                                const pendingFiles = {}; // sessionId -> File object, kept for retry

                function csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                }

                function renderCard(sessionId, name) {
                    const container = document.getElementById('guest-upload-progress');
                    const card = document.createElement('div');
                    card.id = 'guest-upload-' + sessionId;
                    card.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:6px;';
                    card.innerHTML = `
                        <svg width="56" height="56" viewBox="0 0 56 56">
                            <circle cx="28" cy="28" r="24" fill="none" stroke="#E7E5E4" stroke-width="4"/>
                            <circle class="ring" cx="28" cy="28" r="24" fill="none" stroke="#7C3AED" stroke-width="4"
                                stroke-dasharray="150.8" stroke-dashoffset="150.8" transform="rotate(-90 28 28)"/>
                            <text x="28" y="32" text-anchor="middle" font-size="12" fill="#1C1917" class="pct">0%</text>
                        </svg>
                        <span style="font-size:10px;color:#78716C;max-width:70px;text-align:center;word-break:break-word;">${name}</span>
                        <button class="retry-btn" style="display:none;background:none;border:1px solid #DC2626;color:#DC2626;font-size:10px;padding:2px 8px;border-radius:4px;cursor:pointer;">Retry</button>
                    `;
                    container.appendChild(card);
                    card.querySelector('.retry-btn').addEventListener('click', () => retryUpload(sessionId));
                    return card;
                }

                function updateCard(card, percent) {
                    const ring = card.querySelector('.ring');
                    const text = card.querySelector('.pct');
                    ring.setAttribute('stroke-dashoffset', 150.8 - (150.8 * percent) / 100);
                    text.textContent = Math.round(percent) + '%';
                }

                function markCardSuccess(card, sessionId) {
                    const ring = card.querySelector('.ring');
                    const text = card.querySelector('.pct');
                    ring.setAttribute('stroke', '#10B981');
                    ring.setAttribute('stroke-dashoffset', 0);
                    text.textContent = '✓';
                    delete pendingFiles[sessionId];
                    setTimeout(() => card.remove(), 1500);
                }

                function markCardFailed(card) {
                    const ring = card.querySelector('.ring');
                    const text = card.querySelector('.pct');
                    ring.setAttribute('stroke', '#DC2626');
                    text.textContent = '✕';
                    card.querySelector('.retry-btn').style.display = 'inline-block';
                }

                function retryUpload(sessionId) {
                    const file = pendingFiles[sessionId];
                    if (!file) return;
                    const card = document.getElementById('guest-upload-' + sessionId);
                    card.querySelector('.retry-btn').style.display = 'none';
                    const ring = card.querySelector('.ring');
                    ring.setAttribute('stroke', '#7C3AED');
                    uploadFile(file, sessionId, card);
                }

                async function uploadFile(file, existingSessionId, existingCard) {
                    // existingSessionId/existingCard are only passed on retry —
                    // a fresh upload generates a new session and card as before.
                    const sessionId = existingSessionId || uuid();
                    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                    const card = existingCard || renderCard(sessionId, file.name);
                    pendingFiles[sessionId] = file;

                    for (let i = 0; i < totalChunks; i++) {
                        const chunk = file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE);
                        const fd = new FormData();
                        fd.append('upload_session', sessionId);
                        fd.append('share_token', shareToken);
                        fd.append('chunk_index', i);
                        fd.append('total_chunks', totalChunks);
                        fd.append('chunk', chunk);

                        let attempt = 0, ok = false;
                        while (attempt < 3 && !ok) {
                            try {
                                const res = await fetch('/media/upload/chunk', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': csrfToken() },
                                    body: fd,
                                });
                                if (!res.ok) throw new Error();
                                ok = true;
                            } catch (e) {
                                attempt++;
                                if (attempt >= 3) {
                                    showToast('Upload failed: ' + file.name);
                                    markCardFailed(card);
                                    return;
                                }
                                await new Promise((r) => setTimeout(r, 1000 * attempt));
                            }
                        }
                        updateCard(card, ((i + 1) / totalChunks) * 100);
                    }

                    let res, result;
                    try {
                        res = await fetch('/media/upload/finalize', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken(),
                            },
                            body: JSON.stringify({
                                upload_session: sessionId,
                                share_token: shareToken,
                                file_name: file.name,
                                total_chunks: totalChunks,
                            }),
                        });

                        // The server can return a non-JSON body (e.g. Laravel's
                        // full HTML error page on a 500) — parsing that as JSON
                        // would throw uncaught and freeze the card forever
                        // instead of showing a clean failure state. Read as
                        // text first, then attempt JSON parsing separately.
                        const text = await res.text();
                        try {
                            result = JSON.parse(text);
                        } catch (e) {
                            result = { error: 'Something went wrong on the server. Please try again.' };
                        }
                    } catch (networkError) {
                        showToast('Network error while finishing upload: ' + file.name);
                        markCardFailed(card);
                        return;
                    }

                    if (!res.ok) {
                        showToast(result.error || 'Upload failed.');
                        markCardFailed(card);
                        return;
                    }

                    markCardSuccess(card, sessionId);
                    document.getElementById('guest-upload-done').style.display = 'block';
                }

                // Fully self-contained, inline-styled toast — rsvp.blade.php
                // does NOT load Tailwind/app.css (unlike the tenant/client/
                // vendor layouts), so the shared #krd-toast-container's
                // fixed/flex utility classes do nothing here. Positioning
                // and layout are done entirely via inline styles instead,
                // so this never depends on any external stylesheet being
                // present on this particular public layout.
                function showToast(msg) {
                    let container = document.getElementById('guest-toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'guest-toast-container';
                        container.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:320px;';
                        document.body.appendChild(container);
                    }

                    const t = document.createElement('div');
                    t.style.cssText = 'background:#DC2626;color:#fff;padding:10px 14px;border-radius:6px;font-size:13px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
                    t.textContent = msg;
                    container.appendChild(t);
                    setTimeout(() => t.remove(), 4000);
                }

                function enqueue(fileList) {
                    Array.from(fileList).slice(0, MAX_BATCH).forEach((file) => uploadFile(file));
                }

                return { enqueue };
            })();
        </script>
    @endif
</div>