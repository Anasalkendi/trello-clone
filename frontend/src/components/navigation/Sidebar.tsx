import { Link, useLocation } from 'react-router-dom';
import { useBoardStore } from '../../store/boardStore';
import { clsx } from 'clsx';

interface SidebarProps {
  open: boolean;
}

export const Sidebar = ({ open }: SidebarProps) => {
  const projects = useBoardStore((state) => state.projects);
  const location = useLocation();

  return (
    <aside
      className={clsx(
        'w-72 border-l border-slate-800 bg-slate-900/80 backdrop-blur transition-all duration-300',
        open ? 'translate-x-0' : 'translate-x-full',
        'lg:translate-x-0'
      )}
      aria-label="قائمة المشاريع"
    >
      <div className="flex h-16 items-center justify-center border-b border-slate-800 text-lg font-semibold">
        مشاريعك
      </div>
      <nav className="space-y-1 p-4">
        {projects.map((project) => (
          <Link
            key={project.id}
            to={`/boards/${project.id}`}
            className={clsx(
              'flex flex-col rounded-lg border border-transparent bg-slate-800/60 px-4 py-3 text-sm hover:border-primary hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60',
              location.pathname.includes(project.id) && 'border-primary text-primary'
            )}
          >
            <span className="font-semibold">{project.name}</span>
            <span className="text-xs text-slate-400">
              {project.boardCount} لوحات · آخر تحديث {new Date(project.updatedAt).toLocaleDateString('ar')}
            </span>
          </Link>
        ))}
      </nav>
    </aside>
  );
};
