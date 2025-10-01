import { lazy, Suspense } from 'react';

const Loading = () => (
  <div className="rounded-md border border-dashed border-slate-700 p-4 text-sm text-slate-400">
    جارٍ تحميل المحرر...
  </div>
);

export default function dynamicImport<T extends React.ComponentType<any>>(factory: () => Promise<{ default: T }>) {
  const Component = lazy(factory);

  return (props: React.ComponentProps<T>) => (
    <Suspense fallback={<Loading />}>
      <Component {...props} />
    </Suspense>
  );
}
