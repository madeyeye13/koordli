<div x-data="floatingConversationsData()" @if(!$hasAnyConversation) style="display:none;" @endif>
@if($hasAnyConversation)
<script type="application/json" id="floating-conversations-initial-data">{!! json_encode($initialList) !!}</script>

    <div wire:ignore>
        <button x-on:click="togglePanel()" style="position:fixed;bottom:24px;right:24px;z-index:70;width:56px;height:56px;border-radius:50%;background:#1C1917;color:#fff;border:none;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;font-size:22px;">
            💬
            <span x-show="$store.conversationsWidget.localUnread > 0" x-cloak
                style="position:absolute;top:-4px;right:-4px;background:#EF4444;color:#fff;font-size:11px;font-weight:700;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;"
                x-text="$store.conversationsWidget.localUnread > 9 ? '9+' : $store.conversationsWidget.localUnread"></span>
        </button>

        <div x-show="$store.conversationsWidget.panelOpen" x-cloak
            style="position:fixed;bottom:88px;right:24px;z-index:70;width:320px;max-height:460px;background:#fff;border:1px solid #E7E5E4;border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,0.2);overflow:hidden;display:flex;flex-direction:column;">

            <div x-show="!$store.conversationsWidget.activeUuid">
                <div style="padding:14px 16px;border-bottom:1px solid #E7E5E4;background:#1C1917;">
                    <span style="color:#fff;font-size:14px;font-weight:600;">Chats</span>
                </div>
                <div style="overflow-y:auto;max-height:400px;">
                    <template x-for="conv in $store.conversationsWidget.list" :key="conv.uuid">
                        <button x-on:click="openChat(conv.uuid, conv.name)"
                            style="display:block;width:100%;text-align:left;padding:12px 16px;border:none;background:none;cursor:pointer;border-bottom:1px solid #F5F5F4;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <span style="font-size:13px;font-weight:600;color:#1C1917;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="conv.name"></span>
                                <span x-show="conv.unread_count > 0" style="background:#7C3AED;color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;flex-shrink:0;" x-text="conv.unread_count"></span>
                            </div>
                            <div style="font-size:11px;color:#78716C;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="conv.event_name"></div>
                            <div style="font-size:12px;color:#A8A29E;margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="conv.preview"></div>
                        </button>
                    </template>
                    <div x-show="$store.conversationsWidget.listLoading" style="padding:20px;text-align:center;font-size:12px;color:#A8A29E;">Loading...</div>
                    <div x-show="!$store.conversationsWidget.listLoading && $store.conversationsWidget.list.length === 0" style="padding:20px;text-align:center;font-size:12px;color:#A8A29E;">No conversations yet.</div>
                </div>
            </div>

            <div x-show="$store.conversationsWidget.activeUuid" x-cloak>
                <div style="padding:10px 16px;border-bottom:1px solid #E7E5E4;background:#1C1917;display:flex;align-items:center;gap:8px;">
                    <button x-on:click="backToList()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;">←</button>
                    <span style="color:#fff;font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="$store.conversationsWidget.activeName"></span>
                    <a :href="'/conversations/' + $store.conversationsWidget.activeUuid" wire:navigate x-on:click="$store.conversationsWidget.panelOpen = false; $store.conversationsWidget.activeUuid = null;" style="margin-left:auto;color:#A8A29E;font-size:11px;text-decoration:none;">Open Full →</a>
                </div>

                <div id="mini-chat-scroll" style="height:320px;overflow-y:scroll;padding:12px;"></div>

                <div style="padding:10px;border-top:1px solid #E7E5E4;position:relative;">
                    <div x-show="emojiOpen" x-cloak x-on:click.outside="emojiOpen = false"
                        style="position:absolute;bottom:100%;left:10px;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:8px;width:240px;max-height:160px;overflow-y:auto;z-index:60;display:grid;grid-template-columns:repeat(8, 1fr);gap:3px;margin-bottom:4px;">
                        <template x-for="emoji in emojiList" :key="emoji">
                            <button type="button" x-on:click="pickEmoji(emoji)" style="background:none;border:none;cursor:pointer;font-size:16px;padding:3px;border-radius:4px;" x-text="emoji"></button>
                        </template>
                    </div>

                    <div x-show="attachedFileName" x-cloak style="font-size:11px;color:#7C3AED;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                        <span>📎</span>
                        <span x-text="attachedFileName" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;"></span>
                        <button type="button" x-on:click="clearAttachment()" style="background:none;border:none;color:#EF4444;cursor:pointer;">×</button>
                    </div>

                    <div x-show="recording" x-cloak style="font-size:11px;color:#EF4444;font-weight:600;margin-bottom:6px;" x-text="'Recording ' + recordSeconds + 's...'"></div>

                    <div style="display:flex;gap:6px;align-items:center;">
                        <button type="button" x-on:click="emojiOpen = !emojiOpen" style="background:none;border:1px solid #E7E5E4;border-radius:6px;width:30px;height:30px;flex-shrink:0;cursor:pointer;font-size:14px;">😊</button>
                        <button type="button" x-on:click="pickFile()" style="background:none;border:1px solid #E7E5E4;border-radius:6px;width:30px;height:30px;flex-shrink:0;cursor:pointer;font-size:13px;">📎</button>
                        <button type="button" x-on:click="toggleRecording()" :style="recording ? 'background:#FEE2E2;border:1px solid #EF4444;color:#EF4444;' : 'background:none;border:1px solid #E7E5E4;color:#57534E;'" style="border-radius:6px;width:30px;height:30px;flex-shrink:0;cursor:pointer;font-size:13px;">
                            <span x-text="recording ? '⏹' : '🎙️'"></span>
                        </button>
                        <input x-model="$store.conversationsWidget.miniInput" x-on:keydown.enter="sendMini()" type="text" placeholder="Quick reply..." style="flex:1;border:1px solid #E7E5E4;border-radius:6px;padding:8px 10px;font-size:12.5px;outline:none;min-width:0;" />
                        <button x-on:click="sendMini()" style="background:#7C3AED;color:#fff;border:none;border-radius:6px;padding:0 12px;height:30px;font-size:12px;font-weight:600;cursor:pointer;flex-shrink:0;">Send</button>
                    </div>
                    <input type="file" id="quick-reply-file-input" x-on:change="onFileSelected($event)" style="display:none;" />
                </div>
            </div>
        </div>
    </div>

<script>
function floatingConversationsData() {
    return {
        emojiOpen: false,
        emojiList: ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😋','🤔','😐','😑','🙄','😴','😷','🥳','😎','🤓','😕','🥺','😢','😭','😡','👍','👎','👏','🙌','🙏','💪','🤝','✌️','❤️','💛','💚','💙','💜','💯','🎉','🔥','✨','⭐','✅','❌','⚠️'],
        attachedFile: null,
        attachedFileName: '',
        recording: false,
        recordSeconds: 0,
        recordTimer: null,
        mediaRecorder: null,
        audioChunks: [],

        pickEmoji: function (emoji) {
            var store = Alpine.store('conversationsWidget');
            store.miniInput = (store.miniInput || '') + emoji;
            this.emojiOpen = false;
        },

        pickFile: function () {
            document.getElementById('quick-reply-file-input').click();
        },

        onFileSelected: function (event) {
            var file = event.target.files[0];
            if (!file) return;
            this.attachedFile = file;
            this.attachedFileName = file.name;
        },

        clearAttachment: function () {
            this.attachedFile = null;
            this.attachedFileName = '';
            document.getElementById('quick-reply-file-input').value = '';
        },

        toggleRecording: function () {
            if (this.recording) {
                this.stopRecording();
            } else {
                this.startRecording();
            }
        },

        startRecording: function () {
            var self = this;
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
                self.audioChunks = [];
                self.mediaRecorder = new MediaRecorder(stream);

                self.mediaRecorder.ondataavailable = function (e) {
                    if (e.data.size > 0) self.audioChunks.push(e.data);
                };

                self.mediaRecorder.onstop = function () {
                    stream.getTracks().forEach(function (track) { track.stop(); });
                    var blob = new Blob(self.audioChunks, { type: 'audio/webm' });
                    var fileName = 'voice-note-' + Date.now() + '.webm';
                    self.attachedFile = new File([blob], fileName, { type: 'audio/webm' });
                    self.attachedFileName = 'Voice note (' + self.recordSeconds + 's)';
                    self.sendMini();
                };

                self.mediaRecorder.start();
                self.recording = true;
                self.recordSeconds = 0;

                self.recordTimer = setInterval(function () {
                    self.recordSeconds++;
                    if (self.recordSeconds >= 120) self.stopRecording();
                }, 1000);
            }).catch(function () {
                if (window.KrdToast) KrdToast.warning('Could not access microphone.');
            });
        },

        stopRecording: function () {
            if (this.mediaRecorder && this.recording) {
                this.mediaRecorder.stop();
            }
            this.recording = false;
            clearInterval(this.recordTimer);
        },

        init: function () {
            var dataEl = document.getElementById('floating-conversations-initial-data');
            var initialList = [];
            if (dataEl) {
                try { initialList = JSON.parse(dataEl.textContent); } catch (e) { initialList = []; }
            }
            Alpine.store('conversationsWidget').setList(initialList);
        },
        togglePanel: function () {
            Alpine.store('conversationsWidget').togglePanel();
        },
        backToList: function () {
            Alpine.store('conversationsWidget').backToList();
        },
        openChat: function (uuid, name) {
            Alpine.store('conversationsWidget').openChatUI(uuid, name);

            fetch('/conversations/' + uuid + '/quick-messages?_t=' + Date.now(), {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            })
                .then(function (r) { return r.json(); })
                .then(function (messages) {
                    messages.forEach(function (m) {
                        Alpine.store('conversationsWidget').appendToMini(m);
                    });
                })
                .catch(function () {
                    if (window.KrdToast) {
                        KrdToast.warning('Could not load messages right now.');
                    }
                });
        },
        sendMini: function () {
            var self = this;
            var store = Alpine.store('conversationsWidget');
            var text = store.miniInput;
            var uuid = store.activeUuid;
            var file = this.attachedFile;

            if ((!text || !text.trim()) && !file) return;
            if (!uuid) return;

            store.miniInput = '';
            store.appendToMini({
                sender_type: 'tenant_user',
                sender_id: window.__currentUserId,
                sender_name: window.__currentUserName,
                body: text || (file ? '📎 ' + file.name : '')
            });

            this.attachedFile = null;
            this.attachedFileName = '';
            var fileInputEl = document.getElementById('quick-reply-file-input');
            if (fileInputEl) fileInputEl.value = '';

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var formData = new FormData();
            formData.append('body', text || '');
            if (file) formData.append('attachment', file);

            fetch('/conversations/' + uuid + '/quick-send', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            }).catch(function () {
                if (window.KrdToast) {
                    KrdToast.warning('Message may not have sent — please check the full conversation.');
                }
            });
        }
    };
}
</script>
@endif
</div>