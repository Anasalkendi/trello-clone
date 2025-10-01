import { useEffect } from 'react';
import { useEcho } from '../providers/EchoProvider';
import { useBoardStore } from '../store/boardStore';
import type { Attachment, Board, Card, Comment } from '../types';

interface BoardEventPayload {
  board: Board;
}

export const useBoardRealtime = (boardId?: string) => {
  const { echo } = useEcho();
  const setBoard = useBoardStore((state) => state.setBoard);
  const upsertCard = useBoardStore((state) => state.upsertCard);
  const addComment = useBoardStore((state) => state.addComment);
  const addAttachment = useBoardStore((state) => state.addAttachment);

  useEffect(() => {
    if (!echo || !boardId) return;

    const channel = echo.channel(`boards.${boardId}`);

    channel.listen('BoardUpdated', (event: BoardEventPayload) => {
      setBoard(event.board);
    });

    channel.listen('CardUpdated', (event: { card: Card }) => {
      upsertCard(event.card);
    });

    channel.listen('CommentCreated', (event: { cardId: string; comment: Comment }) => {
      addComment(event.cardId, event.comment);
    });

    channel.listen('AttachmentUploaded', (event: { cardId: string; attachment: Attachment }) => {
      addAttachment(event.cardId, event.attachment);
    });

    return () => {
      echo.leave(`boards.${boardId}`);
    };
  }, [echo, boardId, setBoard, upsertCard, addComment, addAttachment]);
};
