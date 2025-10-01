import { afterEach, describe, expect, it } from 'vitest';
import { useBoardStore } from '../store/boardStore';
import type { Board } from '../types';

const mockBoard = (): Board => ({
  id: '1',
  name: 'Test',
  lists: [
    { id: 'todo', title: 'To Do', cardIds: ['a', 'b'] },
    { id: 'doing', title: 'Doing', cardIds: [] }
  ],
  cards: {
    a: {
      id: 'a',
      title: 'A',
      description: '',
      assignees: [],
      watchers: [],
      labels: [],
      comments: [],
      attachments: [],
      checklists: []
    },
    b: {
      id: 'b',
      title: 'B',
      description: '',
      assignees: [],
      watchers: [],
      labels: [],
      comments: [],
      attachments: [],
      checklists: []
    }
  },
  labels: [],
  members: []
});

afterEach(() => {
  useBoardStore.setState((state) => {
    state.currentBoard = undefined;
    state.projects = [];
    state.filters = {
      labelIds: [],
      assigneeIds: [],
      watcherIds: [],
      searchTerm: '',
      hideCompletedChecklists: false
    };
    state.loading = false;
    state.error = undefined;
  });
});

describe('board store', () => {
  it('moves a card optimistically and rolls back', () => {
    const board = mockBoard();
    useBoardStore.setState({ currentBoard: board });

    const rollback = useBoardStore.getState().moveCardOptimistic('a', 'todo', 'doing', 0);

    expect(useBoardStore.getState().currentBoard?.lists[0].cardIds).toEqual(['b']);
    expect(useBoardStore.getState().currentBoard?.lists[1].cardIds).toEqual(['a']);

    rollback();

    expect(useBoardStore.getState().currentBoard?.lists[0].cardIds).toEqual(['a', 'b']);
    expect(useBoardStore.getState().currentBoard?.lists[1].cardIds).toEqual([]);
  });
});
