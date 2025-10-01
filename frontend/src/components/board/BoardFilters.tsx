import { useBoardStore } from '../../store/boardStore';

export const BoardFilters = () => {
  const filters = useBoardStore((state) => state.filters);
  const setFilters = useBoardStore((state) => state.setFilters);
  const resetFilters = useBoardStore((state) => state.resetFilters);

  return (
    <form className="flex flex-wrap items-center gap-3 rounded-3xl border border-slate-800 bg-slate-900/70 p-4" aria-label="مرشحات البطاقات">
      <label className="flex items-center gap-2 text-xs text-slate-400">
        <span>بحث</span>
        <input
          type="search"
          className="rounded-full border border-slate-700 bg-slate-800 px-4 py-1 text-sm"
          placeholder="عنوان البطاقة"
          value={filters.searchTerm}
          onChange={(event) => setFilters({ searchTerm: event.target.value })}
        />
      </label>
      <label className="flex items-center gap-2 text-xs text-slate-400">
        <input
          type="checkbox"
          checked={filters.hideCompletedChecklists}
          onChange={(event) => setFilters({ hideCompletedChecklists: event.target.checked })}
          className="h-4 w-4 rounded border-slate-700 bg-slate-800"
        />
        إخفاء البطاقات المكتملة
      </label>
      <button
        type="button"
        className="ml-auto rounded-full border border-slate-700 px-4 py-1 text-xs text-slate-300 hover:border-primary"
        onClick={resetFilters}
      >
        إعادة التعيين
      </button>
    </form>
  );
};
