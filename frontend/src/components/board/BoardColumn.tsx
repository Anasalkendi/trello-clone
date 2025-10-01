import { useDroppable, SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { useMemo } from 'react';
import type { BoardList } from '../../types';
import { BoardCard } from './BoardCard';
import { filterCardByState } from '../../utils/dnd';
import { useBoardStore } from '../../store/boardStore';

interface BoardColumnProps {
  list: BoardList;
}

export const BoardColumn = ({ list }: BoardColumnProps) => {
  const { setNodeRef } = useDroppable({ id: list.id });
  const board = useBoardStore((state) => state.currentBoard);
  const filters = useBoardStore((state) => state.filters);

  const cards = useMemo(() => {
    if (!board) return [];
    return list.cardIds
      .filter((cardId) => filterCardByState(cardId, board, filters))
      .map((cardId) => board.cards[cardId]);
  }, [board, list.cardIds, filters]);

  return (
    <div className="flex w-80 shrink-0 flex-col rounded-3xl border border-slate-800 bg-slate-900/60 p-4" ref={setNodeRef}>
      <div className="flex items-center justify-between">
        <h2 className="text-sm font-semibold text-slate-200">{list.title}</h2>
        <span className="text-xs text-slate-500">{cards.length}</span>
      </div>
      <div className="mt-4 flex flex-col gap-3">
        <SortableContext items={cards.map((card) => card.id)} strategy={verticalListSortingStrategy}>
          {cards.map((card) => (
            <BoardCard key={card.id} card={card} />
          ))}
        </SortableContext>
      </div>
    </div>
  );
};
