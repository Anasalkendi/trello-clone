import { Navigate, Route, Routes } from 'react-router-dom';
import { AppLayout } from '../ui/AppLayout';
import { ProjectsDashboard } from '../pages/ProjectsDashboard';
import { BoardView } from '../pages/BoardView';
import { BoardSettings } from '../pages/BoardSettings';

const App = () => (
  <Routes>
    <Route path="/" element={<AppLayout />}>
      <Route index element={<ProjectsDashboard />} />
      <Route path="boards/:boardId" element={<BoardView />} />
      <Route path="boards/:boardId/settings" element={<BoardSettings />} />
    </Route>
    <Route path="*" element={<Navigate to="/" replace />} />
  </Routes>
);

export default App;
