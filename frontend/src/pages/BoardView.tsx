import { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { DndContext, DragEndEvent, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { fetchBoard, reorderCard } from '../api/boards';
import { useBoardStore } from '../store/boardStore';
import { BoardColumn } from '../components/board/BoardColumn';
import { BoardFilters } from '../components/board/BoardFilters';
import { useBoardRealtime } from '../hooks/useBoardRealtime';
import toast from 'react-hot-toast';

export const BoardView = () => {
  const { boardId = '' } = useParams();
  const setBoard = useBoardStore((state) => state.setBoard);
  const moveCardOptimistic = useBoardStore((state) => state.moveCardOptimistic);
  const board = useBoardStore((state) => state.currentBoard);
  const queryClient = useQueryClient();

  const { data, isLoading, isError } = useQuery({
    queryKey: ['board', boardId],
    queryFn: () => fetchBoard(boardId),
    enabled: Boolean(boardId)
  });

  useBoardRealtime(boardId);

  useEffect(() => {
    if (data) {
      setBoard(data);
    }
  }, [data, setBoard]);

  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 8 } }));

  const reorderMutation = useMutation({
    mutationFn: (body: { cardId: string; fromListId: string; toListId: string; toIndex: number }) =>
      reorderCard(boardId, body.cardId, body),
    onMutate: async (variables) => {
      await queryClient.cancelQueries({ queryKey: ['board', boardId] });
      const rollback = moveCardOptimistic(
        variables.cardId,
        variables.fromListId,
        variables.toListId,
        variables.toIndex
      );
      return rollback;
    },
    onError: (_error, _variables, rollback) => {
      rollback?.();
      toast.error('تعذر حفظ الترتيب، تمت إعادة الحالة السابقة');
    },
    onSuccess: (nextBoard) => {
      setBoard(nextBoard);
    },
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: ['board', boardId] });
    }
  });

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (!over || !board) return;

    const activeCardId = String(active.id);
    const fromListId = String(
      active.data.current?.sortable?.containerId ?? active.data.current?.containerId ?? ''
    );
    const toListId = String(over.data.current?.sortable?.containerId ?? over.id ?? '');

    if (!fromListId || !toListId) return;

    const toIndex = over.data.current?.sortable?.index ?? 0;

    reorderMutation.mutate({ cardId: activeCardId, fromListId, toListId, toIndex });
  };

  if (isLoading || !board) {
    return <p className="text-center text-slate-400">يتم تحميل اللوحة...</p>;
  }

  if (isError) {
    return <p className="text-center text-rose-400">حدث خطأ أثناء تحميل اللوحة</p>;
  }

  return (
    <section className="space-y-4">
      <BoardFilters />
      <DndContext sensors={sensors} onDragEnd={handleDragEnd}>
        <div className="flex items-start gap-4 overflow-x-auto pb-6" aria-label="أعمدة اللوحة" dir="rtl">
          {board.lists.map((list) => (
            <BoardColumn key={list.id} list={list} />
          ))}
          {board.lists.length === 0 && (
            <p className="text-center text-slate-400">لا توجد قوائم بعد.</p>
          )}
        </div>
      </DndContext>
    </section>
  );
};
