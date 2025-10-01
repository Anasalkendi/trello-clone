import { create } from 'zustand';
import { immer } from 'zustand/middleware/immer';
import { persist } from 'zustand/middleware';
import type {
  Attachment,
  Board,
  BoardList,
  Card,
  Comment,
  FilterState,
  ProjectSummary
} from '../types';
import { applyCardMovement } from '../utils/dnd';

interface BoardState {
  projects: ProjectSummary[];
  currentBoard?: Board;
  filters: FilterState;
  loading: boolean;
  error?: string;
  setProjects: (projects: ProjectSummary[]) => void;
  setBoard: (board: Board) => void;
  upsertCard: (card: Card) => void;
  addComment: (cardId: string, comment: Comment) => void;
  addAttachment: (cardId: string, attachment: Attachment) => void;
  moveCardOptimistic: (
    cardId: string,
    fromListId: string,
    toListId: string,
    toIndex: number
  ) => () => void;
  reorderLists: (lists: BoardList[]) => void;
  setFilters: (filters: Partial<FilterState>) => void;
  resetFilters: () => void;
  setLoading: (loading: boolean) => void;
  setError: (error?: string) => void;
}

const defaultFilters: FilterState = {
  labelIds: [],
  assigneeIds: [],
  watcherIds: [],
  searchTerm: '',
  hideCompletedChecklists: false
};

export const useBoardStore = create<BoardState>()(
  persist(
    immer((set, get) => ({
      projects: [],
      currentBoard: undefined,
      filters: defaultFilters,
      loading: false,
      error: undefined,
      setProjects: (projects) => set({ projects }),
      setBoard: (board) => set({ currentBoard: board }),
      upsertCard: (card) =>
        set((state) => {
          if (!state.currentBoard) return;
          state.currentBoard.cards[card.id] = card;
        }),
      addComment: (cardId, comment) =>
        set((state) => {
          const card = state.currentBoard?.cards[cardId];
          if (card) {
            card.comments = [comment, ...card.comments];
          }
        }),
      addAttachment: (cardId, attachment) =>
        set((state) => {
          const card = state.currentBoard?.cards[cardId];
          if (card) {
            card.attachments = [attachment, ...card.attachments];
          }
        }),
      moveCardOptimistic: (cardId, fromListId, toListId, toIndex) => {
        const previous = get().currentBoard;
        if (!previous) return () => undefined;

        const snapshot: Board = JSON.parse(JSON.stringify(previous));
        set((state) => {
          if (!state.currentBoard) return;
          applyCardMovement(state.currentBoard, cardId, fromListId, toListId, toIndex);
        });

        return () => set({ currentBoard: snapshot });
      },
      reorderLists: (lists) =>
        set((state) => {
          if (!state.currentBoard) return;
          state.currentBoard.lists = lists;
        }),
      setFilters: (filters) =>
        set((state) => ({ filters: { ...state.filters, ...filters } })),
      resetFilters: () => set({ filters: defaultFilters }),
      setLoading: (loading) => set({ loading }),
      setError: (error) => set({ error })
    })),
    {
      name: 'board-state',
      partialize: (state) => ({ filters: state.filters })
    }
  )
);
