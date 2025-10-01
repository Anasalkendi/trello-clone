import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
  }
}

interface EchoContextValue {
  echo?: Echo;
}

const EchoContext = createContext<EchoContextValue>({});

interface EchoProviderProps {
  children: ReactNode;
}

export const EchoProvider = ({ children }: EchoProviderProps) => {
  const [echo, setEcho] = useState<Echo>();

  useEffect(() => {
    window.Pusher = Pusher;
    const instance = new Echo({
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_KEY ?? 'local',
      wsHost: import.meta.env.VITE_WS_HOST ?? 'localhost',
      wsPort: Number(import.meta.env.VITE_WS_PORT ?? 6001),
      forceTLS: false,
      disableStats: true,
      enabledTransports: ['ws', 'wss']
    });

    setEcho(instance);

    return () => {
      instance.disconnect();
    };
  }, []);

  const value = useMemo(() => ({ echo }), [echo]);

  return <EchoContext.Provider value={value}>{children}</EchoContext.Provider>;
};

export const useEcho = () => useContext(EchoContext);
