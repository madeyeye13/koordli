import './bootstrap';
import './conversations-widget-store';

document.addEventListener('alpine:init', () => {

    // ── Dark mode store ───────────────────────────────────────
    Alpine.store('theme', {
        dark: localStorage.getItem('krd-dark') === 'true',
        init() { this.apply(); },
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('krd-dark', this.dark);
            this.apply();
        },
        apply() {
            if (this.dark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });

    // ── Feature toggle ────────────────────────────────────────
    Alpine.data('featureToggle', (key, initialValue) => ({
        key: key,
        val: initialValue,
        set(newVal) {
            this.val = newVal;
            this.$wire.setFeature(this.key, newVal);
        }
    }));

    // ── Universal dropdown ────────────────────────────────────
    Alpine.data('krdDropdown', (config) => ({
        open: false,
        selected: config.selected ?? config.placeholder ?? 'Select',
        value: config.value ?? null,
        placeholder: config.placeholder ?? 'Select',

        select(label, value) {
            this.selected = label;
            this.value    = value;
            this.open     = false;
            if (config.wire) {
                this.$wire.set(config.wire, value);
            }
        },

        clear() {
            this.selected = this.placeholder;
            this.value    = null;
            this.open     = false;
            if (config.wire) {
                this.$wire.set(config.wire, '');
            }
        }
    }));

    // ── Support System: tenant-side live chat ──────────────────────
    Alpine.data('tenantChatWidget', (ticketUuid) => ({
        init() {
            this.$el.scrollTop = this.$el.scrollHeight;

            window.Echo.private('support-ticket.' + ticketUuid).listen('.message.sent', (e) => {
                const wrap = document.createElement('div');

                if (e.sender_type === 'system') {
                    wrap.style.textAlign = 'center';
                    wrap.style.margin = '4px 0';
                    const span = document.createElement('span');
                    span.className = 'chat-bubble-system';
                    span.textContent = e.message;
                    wrap.appendChild(span);
                } else if (e.sender_type === 'tenant') {
                    wrap.className = 'chat-msg-tenant';
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble-tenant';
                    bubble.textContent = e.message;
                    wrap.appendChild(bubble);
                } else if (e.sender_type === 'agent') {
                    wrap.className = 'chat-msg-bot';
                    const label = document.createElement('div');
                    label.style.cssText = 'font-size:11px;color:#A8A29E;margin-bottom:3px;';
                    label.textContent = e.sender_name;
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble-agent';
                    bubble.innerHTML = e.rendered;
                    wrap.appendChild(label);
                    wrap.appendChild(bubble);
                } else {
                    wrap.className = 'chat-msg-bot';
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble-bot';
                    bubble.innerHTML = e.rendered;
                    wrap.appendChild(bubble);
                }

                this.$el.appendChild(wrap);
                this.$el.scrollTop = this.$el.scrollHeight;

                if (e.sender_type !== 'tenant') {
                    this.$wire.call('markCurrentChatRead');
                }
            });

            window.Echo.private('support-ticket.' + ticketUuid).listen('.chat.ended', () => {
                this.$wire.call('handleChatEnded');
            });

            window.Echo.private('support-ticket.' + ticketUuid).listen('.chat.accepted', () => {
                this.$wire.call('agentJoined');
            });

            window.__tenantPresenceChannel = window.Echo.join('support-ticket.' + ticketUuid)
                .listenForWhisper('agent-typing', () => {
                    const el = document.getElementById('tenant-typing-indicator');
                    if (el) el.style.display = 'block';
                    clearTimeout(window.__tenantTypingTimeout);
                    window.__tenantTypingTimeout = setTimeout(() => {
                        if (el) el.style.display = 'none';
                    }, 2500);
                });
        }
    }));

    // ── Support System: platform-side live chat ─────────────────────
    Alpine.data('platformChatWidget', (ticketUuid) => ({
        init() {
            this.$el.scrollTop = this.$el.scrollHeight;

            window.Echo.private('support-ticket.' + ticketUuid).listen('.message.sent', (e) => {
                const wrap = document.createElement('div');

                if (e.sender_type === 'system') {
                    wrap.style.textAlign = 'center';
                    wrap.style.margin = '8px 0';
                    const span = document.createElement('span');
                    span.style.cssText = 'font-size:11px;color:#78716C;background:#F5F5F4;padding:4px 12px;border-radius:12px;display:inline-block;';
                    span.textContent = e.message;
                    wrap.appendChild(span);
                    this.$el.appendChild(wrap);
                    this.$el.scrollTop = this.$el.scrollHeight;
                    return;
                }

                const isAgent = e.sender_type === 'agent';
                wrap.style.cssText = 'display:flex;margin-bottom:16px;' + (isAgent ? 'justify-content:flex-end;' : '');

                const inner = document.createElement('div');
                inner.style.maxWidth = '75%';

                const label = document.createElement('div');
                label.style.cssText = 'font-size:11px;color:#A8A29E;margin-bottom:4px;' + (isAgent ? 'text-align:right;' : '');
                label.textContent = e.sender_name + ' \u00B7 ' + e.created_at;

                const bubble = document.createElement('div');
                bubble.style.cssText = 'padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;' +
                    (isAgent ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;');
                bubble.innerHTML = e.rendered;

                inner.appendChild(label);
                inner.appendChild(bubble);
                wrap.appendChild(inner);
                this.$el.appendChild(wrap);
                this.$el.scrollTop = this.$el.scrollHeight;
            });

            window.__platformPresenceChannel = window.Echo.join('support-ticket.' + ticketUuid)
                .listenForWhisper('tenant-typing', () => {
                    const el = document.getElementById('platform-typing-indicator');
                    if (el) el.style.display = 'block';
                    clearTimeout(window.__platformTypingTimeout);
                    window.__platformTypingTimeout = setTimeout(() => {
                        if (el) el.style.display = 'none';
                    }, 2500);
                });
        }
    }));

    // ── Support System: topbar help widget (background listener) ────
    Alpine.data('helpWidgetListener', (ticketUuid) => ({
        init() {
            window.Echo.private('support-ticket.' + ticketUuid).listen('.message.sent', (e) => {
                if (e.sender_type !== 'tenant') {
                    this.$wire.call('refreshActiveChat');
                    if (window.showToast) {
                        window.showToast('New message from ' + e.sender_name, 'info');
                    }
                }
            }).listen('.chat.ended', () => {
                this.$wire.call('refreshActiveChat');
            });
        }
    }));

    // ── Shared: emoji picker (used by both Support Chat and Conversations) ──
    Alpine.data('emojiPicker', (targetProperty) => ({
        open: false,
        emojis: [
            '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇',
            '🥰','😍','🤩','😘','😗','😚','😙','😋','😛','😜','🤪','😝','🤑',
            '🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬',
            '🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵',
            '🥶','🥴','😵','🤯','🤠','🥳','😎','🤓','🧐','😕','😟','🙁','😮',
            '😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖',
            '😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀',
            '👍','👎','👏','🙌','🙏','💪','🤝','✌️','🤞','🤟','🤘','👌','🤙',
            '❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💯',
            '🎉','🎊','🔥','✨','⭐','🎂','🎁','📌','📍','✅','❌','⚠️','💡',
        ],
        toggle() { this.open = !this.open; },
        pick(emoji) {
            this.$wire.set(targetProperty, (this.$wire.get(targetProperty) || '') + emoji);
            this.open = false;
        },
    }));

    // ── Shared: voice note recorder (feeds recorded audio into an
    // existing Livewire file input via the standard DataTransfer trick,
    // so it rides the SAME upload/broadcast pipeline as any other
    // attachment — no new backend code needed at all) ──────────────
    Alpine.data('voiceRecorder', (fileInputId) => ({
        recording: false,
        mediaRecorder: null,
        audioChunks: [],
        seconds: 0,
        timerInterval: null,
        maxSeconds: 120, // 2-minute cap

        get formattedTime() {
            const m = Math.floor(this.seconds / 60);
            const s = this.seconds % 60;
            return `${m}:${s.toString().padStart(2, '0')}`;
        },

        async toggleRecording() {
            if (this.recording) {
                this.stopRecording();
            } else {
                await this.startRecording();
            }
        },

        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.audioChunks = [];
                this.mediaRecorder = new MediaRecorder(stream);

                this.mediaRecorder.ondataavailable = (e) => {
                    if (e.data.size > 0) this.audioChunks.push(e.data);
                };

                this.mediaRecorder.onstop = () => {
                    stream.getTracks().forEach(track => track.stop());
                    this.buildAndAttachFile();
                };

                this.mediaRecorder.start();
                this.recording = true;
                this.seconds = 0;

                this.timerInterval = setInterval(() => {
                    this.seconds++;
                    if (this.seconds >= this.maxSeconds) {
                        this.stopRecording();
                    }
                }, 1000);

            } catch (err) {
                if (window.KrdToast) {
                    KrdToast.error('Could not access microphone. Please check your browser permissions.');
                }
            }
        },

        stopRecording() {
            if (this.mediaRecorder && this.recording) {
                this.mediaRecorder.stop();
            }
            this.recording = false;
            clearInterval(this.timerInterval);
        },

        uploading: false,

        buildAndAttachFile() {
            const blob = new Blob(this.audioChunks, { type: 'audio/webm' });
            const fileName = `voice-note-${Date.now()}.webm`;
            const file = new File([blob], fileName, { type: 'audio/webm' });

            const input = document.getElementById(fileInputId);
            if (!input) return;

            const dataTransfer = new DataTransfer();
            for (const existingFile of input.files) {
                dataTransfer.items.add(existingFile);
            }
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;

            this.uploading = true;
            input.dispatchEvent(new Event('change', { bubbles: true }));

            // Auto-send once Livewire finishes uploading the file — matches
            // how voice notes actually work in messaging apps: record, stop,
            // it sends. No separate manual "click Send" step needed.
            // Uploading itself takes a couple seconds (real Livewire temp-
            // upload latency, not fixable) — the "uploading" flag shows a
            // spinner during this gap so it reads as active, not broken.
            const checkUploadedAndSend = setInterval(() => {
                if (this.$wire.get('attachments').length > 0) {
                    clearInterval(checkUploadedAndSend);
                    this.uploading = false;
                    this.$wire.call('sendMessage');
                }
            }, 100);

            setTimeout(() => {
                clearInterval(checkUploadedAndSend);
                this.uploading = false;
            }, 15000);
        }
    }));

    // ── Conversations: group/direct event conversations ─────────────
    Alpine.data('conversationWidget', (conversationUuid) => ({
        init() {
            // Defer to the next paint cycle so the browser has fully laid out
            // all message content (variable-height reply quotes, wrapped text,
            // etc.) BEFORE we measure scrollHeight — measuring too early
            // (synchronously on init) can capture a shorter, not-yet-final
            // height, landing the scroll partway up instead of truly at bottom.
            requestAnimationFrame(() => {
                this.$el.scrollTop = this.$el.scrollHeight;
            });

            this.$wire.on('local-message-sent', (event) => {
                this.appendMessage(event.message);
            });

            window.Echo.private('conversation.' + conversationUuid)
                .listen('.message.deleted', (e) => {
                    const row = this.$el.querySelector(`[data-message-id="${e.message_id}"]`);
                    if (row) {
                        const bubble = row.querySelector('div > div:last-child, div > div');
                        if (bubble) bubble.innerHTML = '<em style="color:#A8A29E;">This message was deleted.</em>';
                    }
                })
                .listen('.message.sent', (e) => {
                    // Skip appending if this broadcast is an echo of our OWN message —
                    // we already show it instantly via the 'local-message-sent' dispatch.
                    // This makes duplicate suppression deterministic regardless of
                    // whether toOthers()/socket-ID exclusion is working correctly.
                    const isMe = e.sender_type === 'tenant_user' && e.sender_id === window.__currentUserId;
                    if (isMe) {
                        this.$wire.call('markRead');
                        return;
                    }
                    this.appendMessage(e);
                    this.$wire.call('markRead');
                })
                .listen('.participants.changed', () => {
                    this.$wire.call('refreshParticipants');
                });

            const presence = window.Echo.join('conversation.' + conversationUuid);
            window.__convWhisper = () => {
                presence.whisper('typing', { name: window.__currentUserName || 'Someone' });
            };

            presence.listenForWhisper('typing', (e) => {
                const el = document.getElementById('conv-typing-indicator');
                if (el) el.textContent = e.name + ' is typing...';
                if (el) el.style.display = 'block';
                clearTimeout(window.__convTypingTimeout);
                window.__convTypingTimeout = setTimeout(() => {
                    if (el) el.style.display = 'none';
                }, 2500);
            });
        },
        appendMessage(e) {
            const isMe = e.sender_type === 'tenant_user' && e.sender_id === window.__currentUserId;
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;margin-bottom:16px;gap:8px;' + (isMe ? 'justify-content:flex-end;' : '');
            wrap.className = 'conv-msg-row';
            wrap.dataset.messageId = e.id;
            wrap.dataset.isMine = isMe ? '1' : '0';
            // A freshly-arrived message is always within the 30-minute window
            // and, if it's mine, always eligible for "delete for everyone"
            wrap.dataset.canDeleteEveryone = isMe ? '1' : '0';

            // Checkbox — mirrors the Blade-rendered version's x-show binding.
            // Uses Alpine's $data via the closest x-data scope rather than a
            // local x-show, since this node is injected outside Livewire's
            // own render cycle and needs to react to the SAME selectModeLocal
            // state the rest of the page uses.
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.style.cssText = 'accent-color:#7C3AED;margin-top:4px;flex-shrink:0;';
            checkbox.style.display = window.__convSelectModeActive ? '' : 'none';
            checkbox.addEventListener('click', () => {
                if (window.__convToggleId) window.__convToggleId(e.id);
            });
            wrap.appendChild(checkbox);
            wrap.dataset.hasCheckbox = '1';

            const inner = document.createElement('div');
            inner.style.maxWidth = '75%';

            const label = document.createElement('div');
            label.style.cssText = 'font-size:11px;color:#A8A29E;margin-bottom:4px;' + (isMe ? 'text-align:right;' : '');
            label.textContent = e.sender_name + ' \u00B7 ' + e.created_at + ' ';

            const replyBtn = document.createElement('button');
            replyBtn.type = 'button';
            replyBtn.textContent = '↩ Reply';
            replyBtn.style.cssText = 'background:none;border:none;color:#7C3AED;cursor:pointer;font-size:10px;margin-left:6px;';
            replyBtn.addEventListener('click', () => {
                this.$wire.call('replyToMessage', e.id);
            });
            label.appendChild(replyBtn);

            inner.appendChild(label);

            if (e.reply_to) {
                const quote = document.createElement('div');
                quote.style.cssText = 'font-size:11px;color:#78716C;background:#F5F5F4;border-left:2px solid #7C3AED;padding:4px 8px;margin-bottom:4px;border-radius:4px;' + (isMe ? 'margin-left:auto;' : '');
                const strong = document.createElement('strong');
                strong.textContent = e.reply_to.sender_name;
                quote.appendChild(strong);
                quote.append(': ' + e.reply_to.snippet);
                inner.appendChild(quote);
            }

            if (e.body && e.body.trim() !== '') {
                const bubble = document.createElement('div');
                bubble.style.cssText = 'padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;' +
                    (isMe ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;');
                bubble.innerHTML = e.rendered;
                inner.appendChild(bubble);
            }

            if (e.attachments && e.attachments.length > 0) {
                const attWrap = document.createElement('div');
                attWrap.style.cssText = 'margin-top:6px;display:flex;flex-direction:column;gap:6px;' + (isMe ? 'align-items:flex-end;' : '');

                e.attachments.forEach(att => {
                    if (att.is_audio) {
                        const voiceWrap = document.createElement('div');
                        voiceWrap.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:18px;' +
                            (isMe ? 'background:#7C3AED;' : 'background:#F5F5F4;') + 'max-width:240px;';

                        const icon = document.createElement('span');
                        icon.style.fontSize = '16px';
                        icon.textContent = '🎙️';

                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'height:32px;flex:1;' + (isMe ? 'filter:invert(1) hue-rotate(180deg);' : '');
                        const source = document.createElement('source');
                        source.src = att.url;
                        source.type = att.mime_type;
                        audio.appendChild(source);

                        voiceWrap.appendChild(icon);
                        voiceWrap.appendChild(audio);
                        attWrap.appendChild(voiceWrap);
                    } else {
                        const link = document.createElement('a');
                        link.href = att.url;
                        link.target = '_blank';
                        link.style.cssText = 'font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;';
                        link.textContent = '📎 ' + att.name;
                        attWrap.appendChild(link);
                    }
                });

                inner.appendChild(attWrap);
            }

            wrap.appendChild(inner);
            this.$el.appendChild(wrap);
            this.$el.scrollTop = this.$el.scrollHeight;
        }
    }));

});

// ── Apply dark mode before Alpine loads ───────────────────────
(function() {
    if (localStorage.getItem('krd-dark') === 'true') {
        document.documentElement.classList.add('dark');
    }
})();

// ── Re-apply dark mode on Livewire navigation ─────────────────
document.addEventListener('livewire:navigated', () => {
    if (localStorage.getItem('krd-dark') === 'true') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    KrdToast.container = document.getElementById('krd-toast-container');
});

// ── Toast system ──────────────────────────────────────────────
window.KrdToast = {
    container: null,
    init() { this.container = document.getElementById('krd-toast-container'); },
    show(message, type = 'success', title = null, duration = 4000) {
        if (!this.container) this.init();
        if (!this.container) return;
        const icons = {
            success: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
            error:   `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
            warning: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
            info:    `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
        };
        const defaultTitles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };
        const toast = document.createElement('div');
        toast.className = `krd-toast krd-toast-${type}`;
        toast.innerHTML = `
            <div class="krd-toast-icon">${icons[type] ?? icons.info}</div>
            <div class="krd-toast-body">
                <div class="krd-toast-title">${title ?? defaultTitles[type]}</div>
                <div class="krd-toast-message">${message}</div>
            </div>
            <button class="krd-toast-close" onclick="this.closest('.krd-toast').remove()">×</button>
        `;
        this.container.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'krd-toast-out 200ms ease forwards';
            setTimeout(() => toast.remove(), 200);
        }, duration);
    },
    success(message, title = null) { this.show(message, 'success', title); },
    error(message, title = null)   { this.show(message, 'error',   title); },
    warning(message, title = null) { this.show(message, 'warning', title); },
    info(message, title = null)    { this.show(message, 'info',    title); },
};

document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (events) => {
        events.forEach(event => {
            KrdToast.show(event.message, event.type ?? 'success', event.title ?? null, event.duration ?? 4000);
        });
    });
});