import { useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { fetchProjects } from '../api/projects';
import { useBoardStore } from '../store/boardStore';
import { Link } from 'react-router-dom';

export const ProjectsDashboard = () => {
  const setProjects = useBoardStore((state) => state.setProjects);
  const { data, isLoading, isError } = useQuery({ queryKey: ['projects'], queryFn: fetchProjects });

  useEffect(() => {
    if (data) {
      setProjects(data);
    }
  }, [data, setProjects]);

  if (isLoading) {
    return <p className="text-center text-slate-400">يتم تحميل المشاريع...</p>;
  }

  if (isError) {
    return <p className="text-center text-rose-400">حدث خطأ أثناء جلب البيانات</p>;
  }

  if (!data?.length) {
    return <p className="text-center text-slate-400">لا توجد مشاريع بعد.</p>;
  }

  return (
    <section className="mx-auto grid max-w-5xl gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="قائمة المشاريع">
      {data.map((project) => (
        <Link
          key={project.id}
          to={`/boards/${project.id}`}
          className="group flex flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-card transition hover:-translate-y-1 hover:border-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
        >
          <div className="space-y-3">
            <h2 className="text-lg font-semibold text-slate-100">{project.name}</h2>
            <p className="text-sm text-slate-400">
              {project.boardCount} لوحات نشطة · مالك المشروع {project.owner.name}
            </p>
          </div>
          <div className="mt-6 flex items-center justify-between text-xs text-slate-500">
            <span>آخر تحديث {new Date(project.updatedAt).toLocaleString('ar')}</span>
            <span className="text-primary opacity-0 transition group-hover:opacity-100">فتح اللوحة →</span>
          </div>
        </Link>
      ))}
    </section>
  );
};
