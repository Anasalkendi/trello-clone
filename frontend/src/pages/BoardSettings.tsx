import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useBoardStore } from '../store/boardStore';
import { useMutation } from '@tanstack/react-query';
import { updateBoardSettings } from '../api/boards';
import { ColorPicker } from '../components/pickers/ColorPicker';
import toast from 'react-hot-toast';

export const BoardSettings = () => {
  const { boardId = '' } = useParams();
  const board = useBoardStore((state) => state.currentBoard);
  const setBoard = useBoardStore((state) => state.setBoard);
  const [name, setName] = useState(board?.name ?? '');
  const [description, setDescription] = useState(board?.description ?? '');
  const [backgroundColor, setBackgroundColor] = useState(board?.backgroundColor ?? '#2563eb');

  useEffect(() => {
    if (board) {
      setName(board.name);
      setDescription(board.description ?? '');
      setBackgroundColor(board.backgroundColor ?? '#2563eb');
    }
  }, [board]);

  const mutation = useMutation({
    mutationFn: () => updateBoardSettings(boardId, { name, description, backgroundColor }),
    onSuccess: (updated) => {
      setBoard(updated);
      toast.success('تم حفظ إعدادات اللوحة');
    }
  });

  if (!board) {
    return <p className="text-center text-slate-400">جاري التحميل...</p>;
  }

  return (
    <section className="mx-auto max-w-3xl space-y-6 rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
      <header className="space-y-2">
        <h1 className="text-xl font-semibold text-slate-100">إعدادات اللوحة</h1>
        <p className="text-sm text-slate-400">قم بتحديث التفاصيل العامة ولوحة الألوان والخيارات الافتراضية.</p>
      </header>
      <form
        className="space-y-5"
        onSubmit={(event) => {
          event.preventDefault();
          mutation.mutate();
        }}
      >
        <div className="grid gap-2 text-sm">
          <label htmlFor="name" className="font-medium text-slate-300">
            اسم اللوحة
          </label>
          <input
            id="name"
            className="rounded-lg border border-slate-700 bg-slate-800 px-4 py-2"
            value={name}
            onChange={(event) => setName(event.target.value)}
            dir="rtl"
          />
        </div>
        <div className="grid gap-2 text-sm">
          <label htmlFor="description" className="font-medium text-slate-300">
            الوصف
          </label>
          <textarea
            id="description"
            className="rounded-lg border border-slate-700 bg-slate-800 px-4 py-2"
            rows={4}
            value={description}
            onChange={(event) => setDescription(event.target.value)}
            dir="rtl"
          />
        </div>
        <div className="space-y-3">
          <span className="text-sm font-medium text-slate-300">لون الخلفية</span>
          <ColorPicker value={backgroundColor} onChange={setBackgroundColor} />
        </div>
        <button
          type="submit"
          className="rounded-full bg-primary px-6 py-2 text-sm text-primary-foreground hover:bg-primary/90"
          disabled={mutation.isPending}
        >
          حفظ التغييرات
        </button>
      </form>
    </section>
  );
};
