import { Outlet } from 'react-router-dom';
import { Navbar } from '../components/navigation/Navbar';
import { Sidebar } from '../components/navigation/Sidebar';
import { useUiStore } from '../store/uiStore';
import { Toaster } from 'react-hot-toast';

export const AppLayout = () => {
  const sidebarOpen = useUiStore((state) => state.sidebarOpen);

  return (
    <div className="flex min-h-screen bg-slate-950 text-slate-100" dir="rtl">
      <Sidebar open={sidebarOpen} />
      <div className="flex min-h-screen flex-1 flex-col">
        <Navbar />
        <main className="flex-1 overflow-y-auto p-4 lg:p-6">
          <Outlet />
        </main>
      </div>
      <Toaster position="top-center" reverseOrder />
    </div>
  );
};
