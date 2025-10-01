import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface UiState {
  sidebarOpen: boolean;
  toggleSidebar: () => void;
  modals: Record<string, boolean>;
  openModal: (name: string) => void;
  closeModal: (name: string) => void;
}

export const useUiStore = create<UiState>()(
  persist(
    (set) => ({
      sidebarOpen: true,
      toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
      modals: {},
      openModal: (name) => set((state) => ({ modals: { ...state.modals, [name]: true } })),
      closeModal: (name) =>
        set((state) => {
          const next = { ...state.modals };
          delete next[name];
          return { modals: next };
        })
    }),
    {
      name: 'ui-preferences'
    }
  )
);
