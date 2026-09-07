import './bootstrap';
import './conversations-widget-store';
import './blog-editor';

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

    

        // ── Client Portal: conversation widget (same pattern as staff's
    // conversationWidget, but isMe checks sender_type === 'client') ───
    Alpine.data('clientConversationWidget', (conversationUuid) => ({
        init() {
            requestAnimationFrame(() => { this.$el.scrollTop = this.$el.scrollHeight; });

            this.$wire.on('local-message-sent', (event) => { this.appendMessage(event.message); });

            window.Echo.private('conversation.' + conversationUuid).listen('.message.sent', (e) => {
                const isMe = e.sender_type === 'client' && e.sender_id === window.__currentUserId;
                if (isMe) { this.$wire.call('markRead'); return; }
                this.appendMessage(e);
                this.$wire.call('markRead');
            });
        },
        appendMessage(e) {
            const isMe = e.sender_type === 'client' && e.sender_id === window.__currentUserId;
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;margin-bottom:16px;' + (isMe ? 'justify-content:flex-end;' : '');
            const inner = document.createElement('div');
            inner.style.maxWidth = '75%';
            const label = document.createElement('div');
            label.style.cssText = 'font-size:11px;color:#A8A29E;margin-bottom:4px;' + (isMe ? 'text-align:right;' : '');
            label.textContent = e.sender_name + ' \u00B7 ' + e.created_at;
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
                bubble.style.cssText = 'padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;' + (isMe ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;');
                bubble.innerHTML = e.rendered;
                inner.appendChild(bubble);
            }
            if (e.shared_moodboard) {
                const card = document.createElement('a');
                card.href = '/moodboards/' + e.shared_moodboard.id;
                card.style.cssText = 'display:block;text-decoration:none;background:#fff;border:1px solid #E7E5E4;border-radius:10px;overflow:hidden;max-width:260px;margin-top:6px;' + (isMe ? 'margin-left:auto;' : '');
                const coverWrap = document.createElement('div');
                coverWrap.style.cssText = 'width:100%;height:100px;background:#F5F5F4;display:flex;align-items:center;justify-content:center;overflow:hidden;';
                if (e.shared_moodboard.cover) {
                    const img = document.createElement('img');
                    img.src = e.shared_moodboard.cover;
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                    coverWrap.appendChild(img);
                } else {
                    coverWrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';
                }
                const textWrap = document.createElement('div');
                textWrap.style.cssText = 'padding:8px 10px;';
                const label = document.createElement('div');
                label.style.cssText = 'font-size:10px;color:#A8A29E;text-transform:uppercase;';
                label.textContent = 'Moodboard';
                const title = document.createElement('div');
                title.style.cssText = 'font-size:12.5px;font-weight:600;color:#1C1917;';
                title.textContent = e.shared_moodboard.title;
                textWrap.appendChild(label);
                textWrap.appendChild(title);
                card.appendChild(coverWrap);
                card.appendChild(textWrap);
                inner.appendChild(card);
            }
            if (e.attachments && e.attachments.length > 0) {
                e.attachments.forEach(att => {
                    if (att.is_audio) {
                        const voiceWrap = document.createElement('div');
                        voiceWrap.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:18px;margin-top:6px;' + (isMe ? 'background:#7C3AED;' : 'background:#F5F5F4;') + 'max-width:240px;';
                        const icon = document.createElement('span'); icon.textContent = '🎙️';
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'height:32px;flex:1;' + (isMe ? 'filter:invert(1) hue-rotate(180deg);' : '');
                        const source = document.createElement('source');
                        source.src = att.url; source.type = att.mime_type;
                        audio.appendChild(source);
                        voiceWrap.appendChild(icon); voiceWrap.appendChild(audio);
                        inner.appendChild(voiceWrap);
                    } else {
                        const link = document.createElement('a');
                        link.href = att.url; link.target = '_blank';
                        link.style.cssText = 'font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;margin-top:6px;display:inline-block;';
                        link.textContent = '📎 ' + att.name;
                        inner.appendChild(link);
                    }
                });
            }
            wrap.appendChild(inner);
            this.$el.appendChild(wrap);
            this.$el.scrollTop = this.$el.scrollHeight;
        }
    }));

    // ── Vendor Portal: conversation widget (same pattern as staff's) ──
    Alpine.data('vendorConversationWidget', (conversationUuid) => ({
        init() {
            requestAnimationFrame(() => { this.$el.scrollTop = this.$el.scrollHeight; });

            this.$wire.on('local-message-sent', (event) => { this.appendMessage(event.message); });

            window.Echo.private('conversation.' + conversationUuid).listen('.message.sent', (e) => {
                const isMe = e.sender_type === 'vendor_account' && e.sender_id === window.__currentUserId;
                if (isMe) { this.$wire.call('markRead'); return; }
                this.appendMessage(e);
                this.$wire.call('markRead');
            });
        },
        appendMessage(e) {
            const isMe = e.sender_type === 'vendor_account' && e.sender_id === window.__currentUserId;
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;margin-bottom:16px;' + (isMe ? 'justify-content:flex-end;' : '');
            const inner = document.createElement('div');
            inner.style.maxWidth = '75%';
            const label = document.createElement('div');
            label.style.cssText = 'font-size:11px;color:#A8A29E;margin-bottom:4px;' + (isMe ? 'text-align:right;' : '');
            label.textContent = e.sender_name + ' \u00B7 ' + e.created_at;
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
                bubble.style.cssText = 'padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;' + (isMe ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;');
                bubble.innerHTML = e.rendered;
                inner.appendChild(bubble);
            }
            if (e.shared_moodboard) {
                const card = document.createElement('a');
                card.href = '/moodboards/' + e.shared_moodboard.id;
                card.style.cssText = 'display:block;text-decoration:none;background:#fff;border:1px solid #E7E5E4;border-radius:10px;overflow:hidden;max-width:260px;margin-top:6px;' + (isMe ? 'margin-left:auto;' : '');
                const coverWrap = document.createElement('div');
                coverWrap.style.cssText = 'width:100%;height:100px;background:#F5F5F4;display:flex;align-items:center;justify-content:center;overflow:hidden;';
                if (e.shared_moodboard.cover) {
                    const img = document.createElement('img');
                    img.src = e.shared_moodboard.cover;
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                    coverWrap.appendChild(img);
                } else {
                    coverWrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';
                }
                const textWrap = document.createElement('div');
                textWrap.style.cssText = 'padding:8px 10px;';
                const label = document.createElement('div');
                label.style.cssText = 'font-size:10px;color:#A8A29E;text-transform:uppercase;';
                label.textContent = 'Moodboard';
                const title = document.createElement('div');
                title.style.cssText = 'font-size:12.5px;font-weight:600;color:#1C1917;';
                title.textContent = e.shared_moodboard.title;
                textWrap.appendChild(label);
                textWrap.appendChild(title);
                card.appendChild(coverWrap);
                card.appendChild(textWrap);
                inner.appendChild(card);
            }
            if (e.attachments && e.attachments.length > 0) {
                e.attachments.forEach(att => {
                    if (att.is_audio) {
                        const voiceWrap = document.createElement('div');
                        voiceWrap.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:18px;margin-top:6px;' + (isMe ? 'background:#7C3AED;' : 'background:#F5F5F4;') + 'max-width:240px;';
                        const icon = document.createElement('span'); icon.textContent = '🎙️';
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'height:32px;flex:1;' + (isMe ? 'filter:invert(1) hue-rotate(180deg);' : '');
                        const source = document.createElement('source');
                        source.src = att.url; source.type = att.mime_type;
                        audio.appendChild(source);
                        voiceWrap.appendChild(icon); voiceWrap.appendChild(audio);
                        inner.appendChild(voiceWrap);
                    } else {
                        const link = document.createElement('a');
                        link.href = att.url; link.target = '_blank';
                        link.style.cssText = 'font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;margin-top:6px;display:inline-block;';
                        link.textContent = '📎 ' + att.name;
                        inner.appendChild(link);
                    }
                });
            }
            wrap.appendChild(inner);
            this.$el.appendChild(wrap);
            this.$el.scrollTop = this.$el.scrollHeight;
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
                    fetch('/conversations/' + conversationUuid + '/seen-status', {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.message_id) return;
                        const row = document.getElementById('seen-indicator-' + data.message_id);
                        if (row) {
                            row.textContent = data.seen_by.length > 0
                                ? 'Seen by ' + data.seen_by.join(', ')
                                : 'Sent';
                        }
                    });
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

            // Shared moodboard card — mirrors the Blade-rendered version's
            // markup exactly, positioned in the same spot (after the body
            // bubble, before attachments), so the instant local-echo bubble
            // looks identical to what the next real page load would show.
            // Renders the server's pre-rendered HTML string for the
            // moodboard card — same partial that draws it on page-load,
            // so this can NEVER visually drift from the Blade version.
            // The card's actual design lives in exactly one file:
            // resources/views/partials/moodboard-share-card.blade.php
            if (e.shared_moodboard_html) {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = e.shared_moodboard_html;
                inner.appendChild(wrapper.firstElementChild);
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

            // Move the seen-indicator to THIS message if it's mine — it just
            // became the new "most recent own message," so remove any stale
            // indicator from wherever it previously sat (only ever one at a
            // time, matching ConversationDetail::myLastMessageId()'s logic)
            if (isMe) {
                document.querySelectorAll('[id^="seen-indicator-"]').forEach(function (oldEl) {
                    oldEl.remove();
                });
                const seenEl = document.createElement('div');
                seenEl.id = 'seen-indicator-' + e.id;
                seenEl.style.cssText = 'font-size:10px;color:#A8A29E;margin-top:3px;text-align:right;';
                seenEl.textContent = 'Sent';
                inner.appendChild(seenEl);
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

// ── PWA: Service Worker Registration ────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Silent failure — PWA install is a progressive enhancement,
            // never block the app if registration fails.
        });
    });
}

// ── PWA: White-Label Reinstall Detection ────────────────────────────
// An already-installed home-screen icon typically won't auto-update when
// a tenant's white-label status changes (especially on iOS). This detects
// the false→true transition and prompts the person to reinstall so they
// pick up their new branding.
(function () {
    if (typeof window.__tenantWhiteLabel === 'undefined') return;

    const storageKey = 'krd-white-label-status-' + (window.__tenantSlug || 'koordli');
    const dismissedKey = storageKey + '-prompted';

    const previousStatus = localStorage.getItem(storageKey);
    const currentStatus = window.__tenantWhiteLabel ? 'true' : 'false';

    const justEnabled = previousStatus === 'false' && currentStatus === 'true';
    const alreadyPrompted = localStorage.getItem(dismissedKey) === 'true';

    localStorage.setItem(storageKey, currentStatus);

    if (justEnabled && !alreadyPrompted) {
        showReinstallBanner();
        localStorage.setItem(dismissedKey, 'true');
    }

    function showReinstallBanner() {
        const banner = document.createElement('div');
        banner.style.cssText = 'position:fixed;bottom:16px;left:16px;right:16px;max-width:420px;margin:0 auto;background:#1C1917;color:#fff;padding:14px 16px;border-radius:8px;font-size:13px;z-index:9999;display:flex;align-items:center;gap:12px;box-shadow:0 4px 16px rgba(0,0,0,0.2);';
        banner.innerHTML = `
            <span style="flex:1;line-height:1.5;">Your workspace now has custom branding. If you've already installed this app, remove it and re-add it to your home screen to see your updated icon and name.</span>
            <button style="background:#7C3AED;border:none;color:#fff;padding:6px 12px;border-radius:5px;font-size:12px;cursor:pointer;flex-shrink:0;">Got it</button>
        `;
        banner.querySelector('button').addEventListener('click', () => banner.remove());
        document.body.appendChild(banner);
    }
})();

// ── PWA: Push Notification Subscribe Prompt ─────────────────────────
(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
    if (typeof window.__vapidPublicKey === 'undefined' || !window.__vapidPublicKey) return;
    if (typeof window.__currentUserId === 'undefined' || window.__currentUserId === null) return;

    const previewPrompts = new URLSearchParams(window.location.search).get('preview-prompts') === '1';
    const identity = [window.__tenantSlug || 'koordli', window.__currentUserId].join('-');
    const dismissKey = 'krd-push-prompt-dismissed-v2-' + identity;
    if (!previewPrompts && sessionStorage.getItem(dismissKey) === 'true') return;

    navigator.serviceWorker.ready.then((registration) => {
        registration.pushManager.getSubscription().then((existing) => {
            if (existing && !previewPrompts) return; // already subscribed, nothing to prompt
            if (Notification.permission === 'denied') return; // respect a prior explicit "no"

            showPushPrompt(registration, existing);
        });
    });

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
    }

    function showPushPrompt(registration, existing) {
        const banner = document.createElement('div');
        banner.id = 'krd-push-prompt';
        banner.style.cssText = 'position:fixed;bottom:16px;left:16px;right:16px;max-width:420px;margin:0 auto;background:#1C1917;color:#fff;padding:14px 16px;border-radius:8px;font-size:13px;z-index:9999;display:flex;align-items:center;gap:12px;box-shadow:0 4px 16px rgba(0,0,0,0.2);';
        banner.innerHTML = `
            <span style="flex:1;line-height:1.5;">Enable notifications to get instant updates for tasks, messages, and reminders.</span>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                <button id="krd-push-enable" style="background:#7C3AED;border:none;color:#fff;padding:6px 12px;border-radius:5px;font-size:12px;cursor:pointer;">Enable</button>
                <button id="krd-push-dismiss" style="background:transparent;border:1px solid #57534E;color:#A8A29E;padding:6px 12px;border-radius:5px;font-size:12px;cursor:pointer;">Not now</button>
            </div>
        `;
        document.body.appendChild(banner);

        banner.querySelector('#krd-push-dismiss').addEventListener('click', () => {
            sessionStorage.setItem(dismissKey, 'true');
            banner.remove();
        });

        banner.querySelector('#krd-push-enable').addEventListener('click', () => {
            if (!existing) subscribe(registration);
            sessionStorage.setItem(dismissKey, 'true');
            banner.remove();
        });
    }

    function subscribe(registration) {
        registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(window.__vapidPublicKey),
        }).then((subscription) => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify(subscription),
            });
        }).catch(() => {
            // Permission denied or subscribe failed — fail silently, this
            // is a progressive enhancement, never block the app.
        });
    }
})();

// ── PWA: Add to Home Screen Prompt ──────────────────────────────────
// Two genuinely different paths, since browsers don't agree on this:
// Chrome/Android/Desktop fire a real beforeinstallprompt event we can
// hook a custom-styled button into. iOS Safari NEVER fires this event
// at all — Apple provides no programmatic install API — so iOS gets a
// separate banner with manual "Tap Share, then Add to Home Screen"
// instructions instead. Both share the same dismiss-once pattern
// already used by the push-notification prompt above.
(function () {
    const previewPrompts = new URLSearchParams(window.location.search).get('preview-prompts') === '1';
    const dismissKey = 'krd-pwa-install-dismissed';
    if (!previewPrompts && localStorage.getItem(dismissKey) === 'true') return;

    // Already installed? The app runs in standalone display mode —
    // nothing to prompt for.
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    if (isStandalone && !previewPrompts) return;

    const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;

    let deferredPrompt = null;
    let bannerShown = false;

    if (!isIOS) {
        // Chrome/Android/Desktop path — wait for the real browser event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallBanner(false);
        });
    } else {
        // iOS path — no event to wait for, just show instructions once
        // the page has settled, so it doesn't compete with the initial
        // page load.
        setTimeout(() => showInstallBanner(true), previewPrompts ? 300 : 2500);
    }

    if (!isIOS && previewPrompts) {
        setTimeout(() => showInstallBanner(false), 300);
    }

    function showInstallBanner(isIOSInstructions) {
        if (bannerShown || document.getElementById('krd-pwa-install-prompt')) return;
        bannerShown = true;
        const banner = document.createElement('div');
        banner.id = 'krd-pwa-install-prompt';
        banner.style.cssText = 'position:fixed;bottom:96px;left:16px;right:16px;max-width:420px;margin:0 auto;background:#1C1917;color:#fff;padding:14px 16px;border-radius:8px;font-size:13px;z-index:9999;display:flex;align-items:center;gap:12px;box-shadow:0 4px 16px rgba(0,0,0,0.2);';

        if (isIOSInstructions) {
            banner.innerHTML = `
                <span style="flex:1;line-height:1.5;">Install Koordli on your home screen: tap <strong>Share</strong> ⬆️, then <strong>Add to Home Screen</strong>.</span>
                <button id="krd-pwa-dismiss" style="background:transparent;border:1px solid #57534E;color:#A8A29E;padding:6px 12px;border-radius:5px;font-size:12px;cursor:pointer;flex-shrink:0;">Got it</button>
            `;
        } else {
            banner.innerHTML = `
                <span style="flex:1;line-height:1.5;">Install Koordli as an app for quicker access and a full-screen experience.</span>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button id="krd-pwa-install" style="background:#7C3AED;border:none;color:#fff;padding:6px 12px;border-radius:5px;font-size:12px;cursor:pointer;">Install</button>
                    <button id="krd-pwa-dismiss" style="background:transparent;border:1px solid #57534E;color:#A8A29E;padding:6px 12px;border-radius:5px;font-size:12px;cursor:pointer;">Not now</button>
                </div>
            `;
        }

        document.body.appendChild(banner);

        banner.querySelector('#krd-pwa-dismiss').addEventListener('click', () => {
            localStorage.setItem(dismissKey, 'true');
            banner.remove();
        });

        const installBtn = banner.querySelector('#krd-pwa-install');
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                banner.remove();
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                // Whether accepted or dismissed, don't ask again this
                // session — the native browser prompt already gave them
                // the real choice.
                localStorage.setItem(dismissKey, 'true');
                deferredPrompt = null;
            });
        }
    }
})();

// ── Global Chunked Upload Engine ────────────────────────────────────
// Deliberately NOT a Livewire component — lives as a plain global object
// so it survives wire:navigate page transitions (Livewire's SPA-style
// navigation keeps the JS runtime alive), matching the requirement that
// an upload must keep working if the user navigates elsewhere in the app.
window.KoordliUploader = (function () {
    const CHUNK_SIZE = 8 * 1024 * 1024; // 8MB
    const MAX_BATCH = 20; // client-side UX limit only, not a security boundary
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let onComplete = null;

    function uuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            return (c === 'x' ? r : (r & 0x3) | 0x8).toString(16);
        });
    }

    const pendingFiles = {}; // sessionId -> { file, eventId, folderId, uploadType } — kept for retry

    function renderProgressCard(sessionId, fileName) {
        const container = document.getElementById('krd-upload-progress');
        if (!container) return null;

        const card = document.createElement('div');
        card.id = 'krd-upload-' + sessionId;
        card.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:6px;';
        card.innerHTML = `
            <svg width="56" height="56" viewBox="0 0 56 56">
                <circle cx="28" cy="28" r="24" fill="none" stroke="#E7E5E4" stroke-width="4"/>
                <circle class="krd-progress-ring" cx="28" cy="28" r="24" fill="none" stroke="#7C3AED" stroke-width="4"
                    stroke-dasharray="150.8" stroke-dashoffset="150.8" transform="rotate(-90 28 28)"/>
                <text x="28" y="32" text-anchor="middle" font-size="12" fill="#1C1917" class="krd-progress-text">0%</text>
            </svg>
            <span style="font-size:10px;color:#78716C;max-width:70px;text-align:center;word-break:break-word;">${fileName}</span>
            <button class="krd-retry-btn" style="display:none;background:none;border:1px solid #DC2626;color:#DC2626;font-size:10px;padding:2px 8px;border-radius:4px;cursor:pointer;">Retry</button>
        `;
        container.appendChild(card);
        card.querySelector('.krd-retry-btn').addEventListener('click', () => retryUpload(sessionId));
        return card;
    }

    function updateProgressCard(card, percent) {
        if (!card) return;
        const ring = card.querySelector('.krd-progress-ring');
        const text = card.querySelector('.krd-progress-text');
        const circumference = 150.8;
        ring.setAttribute('stroke-dashoffset', circumference - (circumference * percent) / 100);
        text.textContent = Math.round(percent) + '%';
    }

    function markCardSuccess(card, sessionId) {
        if (!card) return;
        const ring = card.querySelector('.krd-progress-ring');
        const text = card.querySelector('.krd-progress-text');
        ring.setAttribute('stroke', '#10B981');
        ring.setAttribute('stroke-dashoffset', 0);
        text.textContent = '✓';
        delete pendingFiles[sessionId];
        setTimeout(() => card.remove(), 1500);
    }

    function markCardFailed(card) {
        if (!card) return;
        const ring = card.querySelector('.krd-progress-ring');
        const text = card.querySelector('.krd-progress-text');
        ring.setAttribute('stroke', '#DC2626');
        text.textContent = '✕';
        card.querySelector('.krd-retry-btn').style.display = 'inline-block';
    }

    function retryUpload(sessionId) {
        const entry = pendingFiles[sessionId];
        if (!entry) return;
        const card = document.getElementById('krd-upload-' + sessionId);
        card.querySelector('.krd-retry-btn').style.display = 'none';
        card.querySelector('.krd-progress-ring').setAttribute('stroke', '#7C3AED');
        uploadFile(entry.file, entry.eventId, entry.folderId, entry.uploadType, sessionId, card);
    }

    async function uploadFile(file, eventId, folderId, uploadType, existingSessionId, existingCard) {
        const sessionId = existingSessionId || uuid();
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        const card = existingCard || renderProgressCard(sessionId, file.name);
        pendingFiles[sessionId] = { file, eventId, folderId, uploadType };

        try {
            for (let i = 0; i < totalChunks; i++) {
                const start = i * CHUNK_SIZE;
                const chunk = file.slice(start, start + CHUNK_SIZE);

                const formData = new FormData();
                formData.append('upload_session', sessionId);
                formData.append('event_id', eventId);
                formData.append('chunk_index', i);
                formData.append('total_chunks', totalChunks);
                formData.append('chunk', chunk);

                let attempt = 0;
                let success = false;
                while (attempt < 3 && !success) {
                    try {
                        const res = await fetch('/media/upload/chunk', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken() },
                            body: formData,
                        });
                        if (!res.ok) throw new Error('Chunk upload failed');
                        success = true;
                    } catch (e) {
                        attempt++;
                        if (attempt >= 3) throw e;
                        await new Promise((r) => setTimeout(r, 1000 * attempt));
                    }
                }

                updateProgressCard(card, ((i + 1) / totalChunks) * 100);
            }

            const finalizeRes = await fetch('/media/upload/finalize', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    upload_session: sessionId,
                    event_id: eventId,
                    folder_id: folderId,
                    file_name: file.name,
                    total_chunks: totalChunks,
                    upload_type: uploadType || 'file',
                }),
            });

            const result = await finalizeRes.json();

            if (!finalizeRes.ok) {
                if (window.showToast) window.showToast(result.error || 'Upload failed.', 'error');
                markCardFailed(card);
                return;
            }

            markCardSuccess(card, sessionId);
            if (onComplete) onComplete(result.document);
        } catch (e) {
            if (window.showToast) window.showToast('Upload failed: ' + file.name, 'error');
            markCardFailed(card);
        }
    }

    function enqueue(fileList, eventId, folderId, uploadType) {
        const files = Array.from(fileList).slice(0, MAX_BATCH);
        if (fileList.length > MAX_BATCH && window.showToast) {
            window.showToast(`Only the first ${MAX_BATCH} files were queued (batch limit).`, 'error');
        }
        files.forEach((file) => uploadFile(file, eventId, folderId, uploadType));
    }

    return {
        enqueue,
        set onComplete(fn) { onComplete = fn; },
        openLightbox: null, // bound per-page by media-library.blade.php's Alpine component
    };
})();