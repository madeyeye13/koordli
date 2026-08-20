<div>
@if($hasAnyConversation)
<script type="application/json" id="vendor-fcw-initial-data">{!! json_encode($initialList) !!}</script>

<div x-data="vendorFloatingConversationsData()">
    <div wire:ignore>
        <button x-on:click="togglePanel()" style="position:fixed;bottom:24px;right:24px;z-index:70;width:56px;height:56px;border-radius:50%;background:#1C1917;color:#fff;border:none;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;font-size:22px;">
            💬
            <span x-show="localUnread > 0" x-cloak style="position:absolute;top:-4px;right:-4px;background:#EF4444;color:#fff;font-size:11px;font-weight:700;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;" x-text="localUnread > 9 ? '9+' : localUnread"></span>
        </button>

        <div x-show="panelOpen" x-cloak style="position:fixed;bottom:88px;right:24px;z-index:70;width:320px;max-height:460px;background:#fff;border:1px solid #E7E5E4;border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,0.2);overflow:hidden;display:flex;flex-direction:column;">

            <div x-show="!activeUuid">
                <div style="padding:14px 16px;border-bottom:1px solid #E7E5E4;background:#1C1917;">
                    <span style="color:#fff;font-size:14px;font-weight:600;">Chats</span>
                </div>
                <div style="overflow-y:auto;max-height:400px;">
                    <template x-for="conv in list" :key="conv.uuid">
                        <button x-on:click="openChat(conv.uuid, conv.name)" style="display:block;width:100%;text-align:left;padding:12px 16px;border:none;background:none;cursor:pointer;border-bottom:1px solid #F5F5F4;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <span style="font-size:13px;font-weight:600;color:#1C1917;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="conv.name"></span>
                                <span x-show="conv.unread_count > 0" style="background:#7C3AED;color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;flex-shrink:0;" x-text="conv.unread_count"></span>
                            </div>
                            <div style="font-size:11px;color:#78716C;margin-top:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="conv.preview"></div>
                        </button>
                    </template>
                    <div x-show="list.length === 0" style="padding:20px;text-align:center;font-size:12px;color:#A8A29E;">No conversations yet.</div>
                </div>
            </div>

            <div x-show="activeUuid" x-cloak>
                <div style="padding:10px 16px;border-bottom:1px solid #E7E5E4;background:#1C1917;display:flex;align-items:center;gap:8px;">
                    <button x-on:click="backToList()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;">←</button>
                    <span style="color:#fff;font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="activeName"></span>
                    <a :href="'/conversations/' + activeUuid" wire:navigate x-on:click="panelOpen = false; activeUuid = null;" style="margin-left:auto;color:#A8A29E;font-size:11px;text-decoration:none;">Open Full →</a>
                </div>

                <div id="vendor-fcw-scroll" style="height:320px;overflow-y:scroll;padding:12px;"></div>

                <div style="padding:10px;border-top:1px solid #E7E5E4;position:relative;">
                    <div x-show="emojiOpen" x-cloak x-on:click.outside="emojiOpen = false"
                        style="position:absolute;bottom:100%;left:10px;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:8px;width:220px;max-height:150px;overflow-y:auto;z-index:60;display:grid;grid-template-columns:repeat(7, 1fr);gap:3px;margin-bottom:4px;">
                        <template x-for="emoji in miniEmojiList" :key="emoji">
                            <button type="button" x-on:click="pickMiniEmoji(emoji)" style="background:none;border:none;cursor:pointer;font-size:15px;padding:3px;" x-text="emoji"></button>
                        </template>
                    </div>
                    <div x-show="miniRecording" x-cloak style="font-size:10px;color:#EF4444;font-weight:600;margin-bottom:4px;" x-text="'Recording ' + miniRecordSeconds + 's...'"></div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <button type="button" x-on:click="emojiOpen = !emojiOpen" style="background:none;border:1px solid #E7E5E4;border-radius:6px;width:28px;height:28px;flex-shrink:0;cursor:pointer;font-size:13px;">😊</button>
                        <button type="button" x-on:click="toggleMiniRecording()" :style="miniRecording ? 'background:#FEE2E2;border:1px solid #EF4444;color:#EF4444;' : 'background:none;border:1px solid #E7E5E4;color:#57534E;'" style="border-radius:6px;width:28px;height:28px;flex-shrink:0;cursor:pointer;font-size:12px;">
                            <span x-text="miniRecording ? '⏹' : '🎙️'"></span>
                        </button>
                        <input x-model="miniInput" x-on:keydown.enter="sendMini()" type="text" placeholder="Quick reply..." style="flex:1;border:1px solid #E7E5E4;border-radius:6px;padding:8px 10px;font-size:12.5px;outline:none;min-width:0;" />
                        <button x-on:click="sendMini()" style="background:#7C3AED;color:#fff;border:none;border-radius:6px;padding:0 12px;height:28px;font-size:12px;font-weight:600;cursor:pointer;flex-shrink:0;">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function vendorFloatingConversationsData() {
    return {
        panelOpen: false,
        activeUuid: null,
        activeName: '',
        miniInput: '',
        localUnread: 0,
        list: [],
        subscribedUuids: [],
        audioUnlocked: false,
        audioCtx: null,
        emojiOpen: false,
        miniEmojiList: ['😀','😂','🙂','😍','🤔','👍','🙏','❤️','🎉','🔥','✅','❌','😢','😡','🥳','😴'],
        miniRecording: false,
        miniRecordSeconds: 0,
        miniRecordTimer: null,
        miniMediaRecorder: null,
        miniAudioChunks: [],

        unlockAudio: function () {
            if (this.audioUnlocked) return;
            try {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                this.audioUnlocked = true;
            } catch (e) {}
        },

        playPing: function () {
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

        recalcTotalUnread: function () {
            var sum = 0;
            for (var i = 0; i < this.list.length; i++) sum += this.list[i].unread_count;
            this.localUnread = sum;
        },

        subscribeTo: function (uuid) {
            if (this.subscribedUuids.indexOf(uuid) !== -1) return;
            this.subscribedUuids.push(uuid);
            var self = this;
            window.Echo.private('conversation.' + uuid).listen('.message.sent', function (e) {
                var isMine = e.sender_type === 'vendor_account' && e.sender_id === window.__currentUserId;
                var isViewing = self.activeUuid === uuid && self.panelOpen;
                if (!isMine) {
                    if (!isViewing) {
                        var row = self.list.find(function (c) { return c.uuid === uuid; });
                        if (row) row.unread_count++;
                        self.recalcTotalUnread();
                        self.playPing();
                    }
                    if (self.activeUuid === uuid) {
                        self.appendToMini(e);
                    }
                }
                var row2 = self.list.find(function (c) { return c.uuid === uuid; });
                if (row2) row2.preview = (e.body && e.body.trim() !== '') ? e.body.slice(0, 40) : '📎 Attachment';
            });
        },

        togglePanel: function () {
            this.unlockAudio();
            this.panelOpen = !this.panelOpen;
            if (!this.panelOpen) this.activeUuid = null;
        },

        openChat: function (uuid, name) {
            this.activeUuid = uuid;
            this.activeName = name;
            var self = this;
            var container = document.getElementById('vendor-fcw-scroll');
            if (container) container.innerHTML = '';
            var row = this.list.find(function (c) { return c.uuid === uuid; });
            if (row) { row.unread_count = 0; this.recalcTotalUnread(); }

            fetch('/vendor/conversations/' + uuid + '/quick-messages', { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (messages) { messages.forEach(function (m) { self.appendToMini(m); }); })
                .catch(function () { if (window.KrdToast) KrdToast.warning('Could not load messages.'); });
        },

        backToList: function () { this.activeUuid = null; },

        pickMiniEmoji: function (emoji) {
            this.miniInput = (this.miniInput || '') + emoji;
            this.emojiOpen = false;
        },

        sendMini: function () {
            var text = this.miniInput;
            var uuid = this.activeUuid;
            if (!text || !text.trim() || !uuid) return;
            this.miniInput = '';
            this.appendToMini({ sender_type: 'vendor_account', sender_id: window.__currentUserId, sender_name: window.__currentUserName, body: text });

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var formData = new FormData();
            formData.append('body', text);

            fetch('/vendor/conversations/' + uuid + '/quick-send', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: formData
            }).catch(function () { if (window.KrdToast) KrdToast.warning('Message may not have sent.'); });
        },

        toggleMiniRecording: function () {
            if (this.miniRecording) { this.stopMiniRecording(); } else { this.startMiniRecording(); }
        },

        startMiniRecording: function () {
            var self = this;
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
                self.miniAudioChunks = [];
                self.miniMediaRecorder = new MediaRecorder(stream);
                self.miniMediaRecorder.ondataavailable = function (e) {
                    if (e.data.size > 0) self.miniAudioChunks.push(e.data);
                };
                self.miniMediaRecorder.onstop = function () {
                    stream.getTracks().forEach(function (track) { track.stop(); });
                    self.sendMiniVoiceNote();
                };
                self.miniMediaRecorder.start();
                self.miniRecording = true;
                self.miniRecordSeconds = 0;
                self.miniRecordTimer = setInterval(function () {
                    self.miniRecordSeconds++;
                    if (self.miniRecordSeconds >= 120) self.stopMiniRecording();
                }, 1000);
            }).catch(function () {
                if (window.KrdToast) KrdToast.warning('Could not access microphone.');
            });
        },

        stopMiniRecording: function () {
            if (this.miniMediaRecorder && this.miniRecording) this.miniMediaRecorder.stop();
            this.miniRecording = false;
            clearInterval(this.miniRecordTimer);
        },

        sendMiniVoiceNote: function () {
            var uuid = this.activeUuid;
            if (!uuid) return;
            var blob = new Blob(this.miniAudioChunks, { type: 'audio/webm' });
            var fileName = 'voice-note-' + Date.now() + '.webm';
            var file = new File([blob], fileName, { type: 'audio/webm' });

            // Use a local blob URL so the SENDER can play back their own
            // voice note immediately, instead of seeing an empty bubble
            // until the server round-trip completes.
            var localUrl = URL.createObjectURL(blob);
            this.appendToMini({
                sender_type: 'vendor_account', sender_id: window.__currentUserId, sender_name: window.__currentUserName,
                body: '',
                attachments: [{ url: localUrl, name: fileName, mime_type: 'audio/webm', is_audio: true }]
            });

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var formData = new FormData();
            formData.append('body', '');
            formData.append('attachment', file);

            fetch('/vendor/conversations/' + uuid + '/quick-send', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: formData
            }).catch(function () { if (window.KrdToast) KrdToast.warning('Voice note may not have sent.'); });
        },

        appendToMini: function (e) {
            var container = document.getElementById('vendor-fcw-scroll');
            if (!container) return;
            var isMe = e.sender_type === 'vendor_account' && e.sender_id === window.__currentUserId;

            var wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;flex-direction:column;margin-bottom:10px;' + (isMe ? 'align-items:flex-end;' : 'align-items:flex-start;');

            var label = document.createElement('div');
            label.style.cssText = 'font-size:10px;color:#A8A29E;margin-bottom:2px;';
            label.textContent = e.sender_name || (isMe ? 'You' : 'Them');
            wrap.appendChild(label);

            if (e.body && e.body.trim() !== '') {
                var bubble = document.createElement('div');
                bubble.style.cssText = 'max-width:80%;padding:8px 12px;border-radius:8px;font-size:12.5px;line-height:1.5;' + (isMe ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;');
                bubble.textContent = e.body;
                wrap.appendChild(bubble);
            }

            if (e.attachments && e.attachments.length > 0) {
                e.attachments.forEach(function (att) {
                    if (att.is_audio) {
                        var voiceWrap = document.createElement('div');
                        voiceWrap.style.cssText = 'display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:14px;max-width:220px;margin-top:2px;' + (isMe ? 'background:#7C3AED;' : 'background:#F5F5F4;');
                        var icon = document.createElement('span'); icon.style.fontSize = '13px'; icon.textContent = '🎙️';
                        var audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'height:28px;flex:1;' + (isMe ? 'filter:invert(1) hue-rotate(180deg);' : '');
                        var source = document.createElement('source');
                        source.src = att.url; source.type = att.mime_type;
                        audio.appendChild(source);
                        voiceWrap.appendChild(icon); voiceWrap.appendChild(audio);
                        wrap.appendChild(voiceWrap);
                    } else {
                        var link = document.createElement('a');
                        link.href = att.url; link.target = '_blank';
                        link.style.cssText = 'font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;margin-top:2px;';
                        link.textContent = '📎 ' + att.name;
                        wrap.appendChild(link);
                    }
                });
            }

            container.appendChild(wrap);
            container.scrollTop = container.scrollHeight;
        },

        init: function () {
            var dataEl = document.getElementById('vendor-fcw-initial-data');
            var initialList = [];
            if (dataEl) { try { initialList = JSON.parse(dataEl.textContent); } catch (e) {} }
            this.list = initialList;
            var self = this;
            initialList.forEach(function (c) { self.subscribeTo(c.uuid); });
            this.recalcTotalUnread();
        }
    };
}
</script>
@endif
</div>