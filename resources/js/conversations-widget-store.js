document.addEventListener('alpine:init', () => {
    Alpine.store('conversationsWidget', {
        panelOpen: false,
        activeUuid: null,
        activeName: '',
        miniInput: '',
        list: [],
        listLoading: true,
        subscribedUuids: [],
        localUnread: 0,
        audioUnlocked: false,
        audioCtx: null,

        unlockAudio() {
            if (this.audioUnlocked) return;
            try {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                this.audioUnlocked = true;
            } catch (e) {}
        },

        playPing() {
            if (!this.audioUnlocked || !this.audioCtx) return;
            try {
                var osc = this.audioCtx.createOscillator();
                var gain = this.audioCtx.createGain();
                osc.connect(gain); gain.connect(this.audioCtx.destination);
                osc.frequency.value = 880;
                gain.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.3);
                osc.start(); osc.stop(this.audioCtx.currentTime + 0.3);
            } catch (e) {}
        },

        recalcTotalUnread() {
            var sum = 0;
            for (var i = 0; i < this.list.length; i++) sum += this.list[i].unread_count;
            this.localUnread = sum;
        },

        subscribeTo(uuid) {
            if (this.subscribedUuids.includes(uuid)) return; // dedupes across every remount
            this.subscribedUuids.push(uuid);
            var self = this;
            window.Echo.private('conversation.' + uuid).listen('.message.sent', function (e) {
                var isMine = e.sender_type === 'tenant_user' && e.sender_id === window.__currentUserId;
                var isCurrentlyViewing = self.activeUuid === uuid && self.panelOpen;

                if (!isMine) {
                    if (!isCurrentlyViewing) {
                        var row = self.list.find(function (c) { return c.uuid === uuid; });
                        if (row) row.unread_count++;
                        self.recalcTotalUnread();
                        self.playPing();
                    }
                    if (self.activeUuid === uuid) self.appendToMini(e);
                }

                var row2 = self.list.find(function (c) { return c.uuid === uuid; });
                if (row2) row2.preview = (e.body && e.body.trim() !== '') ? e.body.slice(0, 40) : '📎 Attachment';
            });
        },

        // Called by the component's x-data with data it fetched via $wire
        setList(data) {
            this.list = data;
            this.listLoading = false;
            var self = this;
            data.forEach(function (c) { self.subscribeTo(c.uuid); });
            this.recalcTotalUnread();
        },

        togglePanel() {
            this.unlockAudio();
            this.panelOpen = !this.panelOpen;
            if (!this.panelOpen) this.activeUuid = null;
        },

        openChatUI(uuid, name) {
            this.activeUuid = uuid;
            this.activeName = name;
            var row = this.list.find(function (c) { return c.uuid === uuid; });
            if (row) { row.unread_count = 0; this.recalcTotalUnread(); }
            var container = document.getElementById('mini-chat-scroll');
            if (container) container.innerHTML = '';
        },

        backToList() {
            this.activeUuid = null;
        },

                appendToMini(e) {
            var container = document.getElementById('mini-chat-scroll');
            if (!container) return;
            var isMe = e.sender_type === 'tenant_user' && e.sender_id === window.__currentUserId;

            var wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;flex-direction:column;margin-bottom:10px;' + (isMe ? 'align-items:flex-end;' : 'align-items:flex-start;');

            var label = document.createElement('div');
            label.style.cssText = 'font-size:10px;color:#A8A29E;margin-bottom:2px;';
            label.textContent = e.sender_name || (isMe ? 'You' : 'Them');
            wrap.appendChild(label);

            if (e.body && e.body.trim() !== '' && !(e.body.indexOf('📎') === 0)) {
                var bubble = document.createElement('div');
                bubble.style.cssText = 'max-width:80%;padding:8px 12px;border-radius:8px;font-size:12.5px;line-height:1.5;' + (isMe ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;');
                bubble.textContent = e.body;
                wrap.appendChild(bubble);
            }

            // Deliberately a compact text link, NOT the full image card —
            // this popup is a lightweight quick-glance surface (same
            // treatment as plain attachment links below), not a place for
            // a rich preview. "Open Full →" is how someone sees the real card.
            if (e.shared_moodboard_title) {
                var moodboardLink = document.createElement('a');
                moodboardLink.href = '/moodboards/' + e.shared_moodboard_id;
                moodboardLink.style.cssText = 'font-size:11px;background:#F5F3FF;padding:4px 10px;border-radius:6px;color:#7C3AED;text-decoration:none;display:inline-block;margin-top:2px;';
                moodboardLink.textContent = '📋 Shared: ' + e.shared_moodboard_title;
                wrap.appendChild(moodboardLink);
            }

            if (e.attachments && e.attachments.length > 0) {
                e.attachments.forEach(function (att) {
                    if (att.is_audio) {
                        var voiceWrap = document.createElement('div');
                        voiceWrap.style.cssText = 'display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:14px;max-width:220px;margin-top:2px;' +
                            (isMe ? 'background:#7C3AED;' : 'background:#F5F5F4;');

                        var icon = document.createElement('span');
                        icon.style.fontSize = '13px';
                        icon.textContent = '🎙️';

                        var audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'height:28px;flex:1;' + (isMe ? 'filter:invert(1) hue-rotate(180deg);' : '');
                        var source = document.createElement('source');
                        source.src = att.url;
                        source.type = att.mime_type;
                        audio.appendChild(source);

                        voiceWrap.appendChild(icon);
                        voiceWrap.appendChild(audio);
                        wrap.appendChild(voiceWrap);
                    } else {
                        var link = document.createElement('a');
                        link.href = att.url;
                        link.target = '_blank';
                        link.style.cssText = 'font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;margin-top:2px;';
                        link.textContent = '📎 ' + att.name;
                        wrap.appendChild(link);
                    }
                });
            }

            container.appendChild(wrap);
            container.scrollTop = container.scrollHeight;
        },
    });
});