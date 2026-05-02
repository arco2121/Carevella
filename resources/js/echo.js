import Echo from 'laravel-echo';
import { env } from './bridge.js';
import Pusher from 'pusher-js';
export const pusher = Pusher;

export const echo = new Echo({
    broadcaster: 'reverb',
    key: env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: env.VITE_REVERB_PORT ?? 80,
    wssPort: env.VITE_REVERB_PORT ?? 443,
    forceTLS: (env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
