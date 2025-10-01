import { apiClient } from './client';
import type { ProjectSummary } from '../types';

export const fetchProjects = async () => {
  const { data } = await apiClient.get<ProjectSummary[]>('/projects');
  return data;
};
