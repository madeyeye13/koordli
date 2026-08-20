import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Attach the current WebSocket connection's socket ID to every outgoing
// request, so Laravel's broadcast(...)->toOthers() knows which connection
// to exclude (otherwise it silently has no effect and the sender receives
// their own broadcast back, causing visible duplicate messages).
window.Echo.connector.pusher.connection.bind('connected', () => {
    window.axios.defaults.headers.common['X-Socket-Id'] = window.Echo.socketId();
});// Attach the current WebSocket connection's socket ID to every outgoing
// Livewire request, so broadcast(...)->toOthers() knows which connection to
// exclude. Livewire 3 uses native fetch() internally, NOT axios, for its own
// component update requests — so this must hook into Livewire's own request
// lifecycle rather than axios interceptors, which have no effect on Livewire.
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ options }) => {
        if (window.Echo?.socketId()) {
            options.headers['X-Socket-Id'] = window.Echo.socketId();
        }
    });
});