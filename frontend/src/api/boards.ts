import { apiClient } from './client';
import type { Board, Card, Comment, Attachment } from '../types';

export const fetchBoard = async (boardId: string) => {
  const { data } = await apiClient.get<Board>(`/boards/${boardId}`);
  return data;
};

export const updateBoardSettings = async (boardId: string, payload: Partial<Board>) => {
  const { data } = await apiClient.patch<Board>(`/boards/${boardId}`, payload);
  return data;
};

export const createComment = async (boardId: string, cardId: string, payload: Partial<Comment>) => {
  const { data } = await apiClient.post<Comment>(`/boards/${boardId}/cards/${cardId}/comments`, payload);
  return data;
};

export const uploadAttachment = async (
  boardId: string,
  cardId: string,
  formData: FormData
) => {
  const { data } = await apiClient.post<Attachment>(
    `/boards/${boardId}/cards/${cardId}/attachments`,
    formData,
    {
      headers: { 'Content-Type': 'multipart/form-data' }
    }
  );
  return data;
};

export const updateCard = async (boardId: string, cardId: string, payload: Partial<Card>) => {
  const { data } = await apiClient.patch<Card>(`/boards/${boardId}/cards/${cardId}`, payload);
  return data;
};

export const reorderCard = async (
  boardId: string,
  cardId: string,
  body: { fromListId: string; toListId: string; toIndex: number }
) => {
  const { data } = await apiClient.post<Board>(`/boards/${boardId}/reorder-card`, {
    cardId,
    ...body
  });
  return data;
};
