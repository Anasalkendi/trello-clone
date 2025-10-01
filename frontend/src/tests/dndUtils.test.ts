import { describe, expect, it } from 'vitest';
import { applyCardMovement, filterCardByState } from '../utils/dnd';
import type { Board } from '../types';

const createBoard = (): Board => ({
  id: 'board',
  name: 'Board',
  lists: [
    { id: 'todo', title: 'To Do', cardIds: ['card-1', 'card-2'] },
    { id: 'done', title: 'Done', cardIds: [] }
  ],
  cards: {
    'card-1': {
      id: 'card-1',
      title: 'First task',
      description: 'A simple card',
      assignees: [{ id: 'u1', name: 'Sara' }],
      watchers: [{ id: 'u2', name: 'Omar' }],
      labels: [{ id: 'l1', name: 'Urgent', color: '#f00' }],
      comments: [],
      attachments: [],
      checklists: [
        {
          id: 'check-1',
          title: 'Checklist',
          items: [
            { id: 'i1', title: 'Item 1', completed: true },
            { id: 'i2', title: 'Item 2', completed: false }
          ]
        }
      ]
    },
    'card-2': {
      id: 'card-2',
      title: 'Second task',
      description: 'Another card',
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

describe('dnd utils', () => {
  it('applies card movement between lists', () => {
    const board = createBoard();
    applyCardMovement(board, 'card-1', 'todo', 'done', 0);
    expect(board.lists[0].cardIds).toEqual(['card-2']);
    expect(board.lists[1].cardIds).toEqual(['card-1']);
  });

  it('filters cards according to state', () => {
    const board = createBoard();
    const result = filterCardByState('card-1', board, {
      labelIds: ['l1'],
      assigneeIds: ['u1'],
      watcherIds: ['u2'],
      searchTerm: 'first',
      hideCompletedChecklists: false
    });
    expect(result).toBe(true);

    const noLabel = filterCardByState('card-1', board, { labelIds: ['l99'] });
    expect(noLabel).toBe(false);

    const hideCompleted = filterCardByState('card-1', board, { hideCompletedChecklists: true });
    expect(hideCompleted).toBe(true);
  });
});
