import type { Board } from '../types';

export const applyCardMovement = (
  board: Board,
  cardId: string,
  fromListId: string,
  toListId: string,
  toIndex: number
) => {
  const sourceList = board.lists.find((list) => list.id === fromListId);
  const targetList = board.lists.find((list) => list.id === toListId);

  if (!sourceList || !targetList) return;

  sourceList.cardIds = sourceList.cardIds.filter((id) => id !== cardId);

  const nextIds = [...targetList.cardIds];
  nextIds.splice(toIndex, 0, cardId);
  targetList.cardIds = nextIds;
};

export const filterCardByState = (
  cardId: string,
  board: Board,
  filters: {
    labelIds?: string[];
    assigneeIds?: string[];
    watcherIds?: string[];
    searchTerm?: string;
    hideCompletedChecklists?: boolean;
  }
) => {
  const card = board.cards[cardId];
  if (!card) return false;

  if (filters.labelIds && filters.labelIds.length) {
    const hasLabel = card.labels.some((label) => filters.labelIds!.includes(label.id));
    if (!hasLabel) return false;
  }

  if (filters.assigneeIds && filters.assigneeIds.length) {
    const hasAssignee = card.assignees.some((assignee) => filters.assigneeIds!.includes(assignee.id));
    if (!hasAssignee) return false;
  }

  if (filters.watcherIds && filters.watcherIds.length) {
    const hasWatcher = card.watchers.some((watcher) => filters.watcherIds!.includes(watcher.id));
    if (!hasWatcher) return false;
  }

  if (filters.searchTerm) {
    const term = filters.searchTerm.trim();
    if (term.length) {
      const matchesTitle = card.title.toLowerCase().includes(term.toLowerCase());
      const matchesDescription = card.description.toLowerCase().includes(term.toLowerCase());
      if (!matchesTitle && !matchesDescription) return false;
    }
  }

  if (filters.hideCompletedChecklists) {
    const hasIncomplete = card.checklists.some((checklist) =>
      checklist.items.some((item) => !item.completed)
    );
    if (!hasIncomplete) return false;
  }

  return true;
};
