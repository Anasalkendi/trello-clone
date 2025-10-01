/// <reference types="vite/client" />

declare namespace NodeJS {
  interface ProcessEnv {
    VITE_API_URL?: string;
    VITE_PUSHER_KEY?: string;
    VITE_WS_HOST?: string;
    VITE_WS_PORT?: string;
  }
}
