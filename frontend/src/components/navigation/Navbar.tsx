import { Link, useLocation, useParams } from 'react-router-dom';
import { useUiStore } from '../../store/uiStore';
import { useBoardStore } from '../../store/boardStore';
import { clsx } from 'clsx';

export const Navbar = () => {
  const toggleSidebar = useUiStore((state) => state.toggleSidebar);
  const { boardId } = useParams();
  const board = useBoardStore((state) => state.currentBoard);
  const location = useLocation();

  const isBoardRoute = location.pathname.includes('/boards/');

  return (
    <header className="flex h-16 items-center justify-between border-b border-slate-800 bg-slate-900/60 px-4 backdrop-blur">
      <button
        type="button"
        className="rounded-md border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-medium hover:bg-slate-700"
        onClick={toggleSidebar}
        aria-label="قائمة التنقل"
      >
        ☰
      </button>
      <div className="flex flex-1 items-center justify-center">
        <Link to="/" className="text-lg font-semibold text-primary">
          كانبان برو
        </Link>
      </div>
      {isBoardRoute && board && boardId ? (
        <div className="flex items-center gap-3 text-sm">
          <span className="font-medium">{board.name}</span>
          <Link
            to={`/boards/${boardId}/settings`}
            className="rounded-md border border-slate-700 px-3 py-2 hover:bg-slate-800"
          >
            الإعدادات
          </Link>
          <span
            className={clsx('inline-flex items-center gap-2 rounded-full bg-slate-800 px-3 py-1')}
            aria-live="polite"
          >
            {board.members.length}
            <span className="text-xs text-slate-400">أعضاء</span>
          </span>
        </div>
      ) : (
        <div aria-hidden className="w-48" />
      )}
    </header>
  );
};
