import { useMemo, useState, type ChangeEvent } from 'react';
import type { Card } from '../../types';
import { Modal } from '../modals/Modal';
import { RichTextEditor } from '../editor/RichTextEditor';
import { LabelPicker } from '../pickers/LabelPicker';
import { UserPicker } from '../pickers/UserPicker';
import { useBoardStore } from '../../store/boardStore';
import { useParams } from 'react-router-dom';
import { createComment, updateCard, uploadAttachment } from '../../api/boards';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';

interface BoardCardProps {
  card: Card;
}

export const BoardCard = ({ card }: BoardCardProps) => {
  const [open, setOpen] = useState(false);
  const board = useBoardStore((state) => state.currentBoard);
  const upsertCard = useBoardStore((state) => state.upsertCard);
  const { boardId = '' } = useParams();
  const queryClient = useQueryClient();

  const commentMutation = useMutation({
    mutationFn: (body: { content: string; mentions: string[] }) =>
      createComment(boardId, card.id, { body: body.content, mentions: body.mentions }),
    onSuccess: (comment) => {
      useBoardStore.getState().addComment(card.id, comment);
      toast.success('تمت إضافة التعليق');
    }
  });

  const updateCardMutation = useMutation({
    mutationFn: (payload: Partial<Card>) => updateCard(boardId, card.id, payload),
    onSuccess: (updated) => {
      upsertCard(updated);
      queryClient.invalidateQueries({ queryKey: ['board', boardId] });
      toast.success('تم حفظ البطاقة');
    }
  });

  const uploadMutation = useMutation({
    mutationFn: (formData: FormData) => uploadAttachment(boardId, card.id, formData),
    onSuccess: (attachment) => {
      useBoardStore.getState().addAttachment(card.id, attachment);
      toast.success('تم رفع الملف');
    }
  });

  const checklistProgress = useMemo(() => {
    const items = card.checklists.flatMap((list) => list.items);
    if (!items.length) return null;
    const completed = items.filter((item) => item.completed).length;
    return Math.round((completed / items.length) * 100);
  }, [card.checklists]);

  const handleFileInput = async (event: ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('file', file);
    uploadMutation.mutate(formData);
  };

  return (
    <>
      <article
        className="cursor-grab space-y-2 rounded-xl border border-slate-800 bg-slate-900/80 p-4 text-sm shadow-card transition hover:border-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
        onClick={() => setOpen(true)}
        role="button"
        tabIndex={0}
        aria-label={`فتح بطاقة ${card.title}`}
        onKeyDown={(event) => event.key === 'Enter' && setOpen(true)}
      >
        <div className="flex items-center justify-between">
          <h3 className="font-semibold text-slate-100">{card.title}</h3>
          {checklistProgress !== null && (
            <span className="text-xs text-slate-400" aria-label="نسبة الإنجاز">
              {checklistProgress}%
            </span>
          )}
        </div>
        <div className="flex flex-wrap gap-2">
          {card.labels.map((label) => (
            <span
              key={label.id}
              className="rounded-full px-3 py-1 text-xs text-slate-900"
              style={{ backgroundColor: label.color }}
            >
              {label.name}
            </span>
          ))}
        </div>
        <div className="flex items-center gap-2 text-xs text-slate-400">
          <span>{card.comments.length} تعليقات</span>
          <span>·</span>
          <span>{card.attachments.length} مرفقات</span>
        </div>
      </article>
      <Modal open={open} onClose={() => setOpen(false)} title={card.title}>
        <div className="space-y-6">
          <section aria-label="الوصف">
            <RichTextEditor
              value={card.description}
              onChange={(value) => updateCardMutation.mutate({ description: value })}
            />
          </section>
          <section aria-label="إدارة التصنيفات" className="space-y-3">
            <h4 className="text-sm font-semibold text-slate-200">التصنيفات</h4>
            {board && (
              <LabelPicker
                labels={board.labels}
                selectedIds={card.labels.map((label) => label.id)}
                onToggle={(labelId) => {
                  const selected = card.labels.find((label) => label.id === labelId);
                  const next = selected
                    ? card.labels.filter((label) => label.id !== labelId)
                    : [...card.labels, board.labels.find((l) => l.id === labelId)!];
                  updateCardMutation.mutate({ labels: next });
                }}
              />
            )}
          </section>
          {board && (
            <section aria-label="إدارة المستخدمين" className="space-y-3">
              <UserPicker
                users={board.members}
                selectedIds={card.assignees.map((user) => user.id)}
                onToggle={(userId) => {
                  const exists = card.assignees.some((user) => user.id === userId);
                  const next = exists
                    ? card.assignees.filter((user) => user.id !== userId)
                    : [...card.assignees, board.members.find((user) => user.id === userId)!];
                  updateCardMutation.mutate({ assignees: next });
                }}
                label="المكلفون"
              />
              <UserPicker
                users={board.members}
                selectedIds={card.watchers.map((user) => user.id)}
                onToggle={(userId) => {
                  const exists = card.watchers.some((user) => user.id === userId);
                  const next = exists
                    ? card.watchers.filter((user) => user.id !== userId)
                    : [...card.watchers, board.members.find((user) => user.id === userId)!];
                  updateCardMutation.mutate({ watchers: next });
                }}
                label="المتابعون"
              />
            </section>
          )}
          <section aria-label="المرفقات" className="space-y-3">
            <div className="flex items-center justify-between">
              <h4 className="text-sm font-semibold text-slate-200">المرفقات</h4>
              <label className="cursor-pointer rounded-full border border-primary px-4 py-2 text-xs text-primary">
                رفع ملف
                <input type="file" className="hidden" onChange={handleFileInput} />
              </label>
            </div>
            <ul className="space-y-2">
              {card.attachments.map((attachment) => (
                <li key={attachment.id} className="flex items-center justify-between text-xs">
                  <a href={attachment.url} className="text-primary underline" target="_blank" rel="noreferrer">
                    {attachment.name}
                  </a>
                  <span className="text-slate-500">
                    {new Date(attachment.uploadedAt).toLocaleString('ar')}
                  </span>
                </li>
              ))}
            </ul>
          </section>
          <section aria-label="إضافة تعليق" className="space-y-3">
            <h4 className="text-sm font-semibold text-slate-200">التعليقات</h4>
            <form
              className="space-y-2"
              onSubmit={(event) => {
                event.preventDefault();
                const form = new FormData(event.currentTarget);
                const content = String(form.get('content') ?? '');
                const mentions = content.match(/@([\w-]+)/g)?.map((mention) => mention.slice(1)) ?? [];
                commentMutation.mutate({ content, mentions });
                event.currentTarget.reset();
              }}
            >
              <textarea
                name="content"
                rows={3}
                className="w-full rounded-lg border border-slate-700 bg-slate-800 p-3 text-sm"
                placeholder="@اذكر أحد أعضاء الفريق..."
                dir="rtl"
              />
              <button
                type="submit"
                className="rounded-full bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
                disabled={commentMutation.isPending}
              >
                إرسال
              </button>
            </form>
            <ul className="space-y-3 text-sm">
              {card.comments.map((comment) => (
                <li key={comment.id} className="rounded-lg border border-slate-800 bg-slate-900/60 p-3">
                  <div className="flex items-center justify-between text-xs text-slate-500">
                    <span>{comment.author.name}</span>
                    <time dateTime={comment.createdAt}>
                      {new Date(comment.createdAt).toLocaleString('ar')}
                    </time>
                  </div>
                  <p className="mt-2 whitespace-pre-wrap text-slate-200" dir="rtl">
                    {comment.body}
                  </p>
                </li>
              ))}
            </ul>
          </section>
        </div>
      </Modal>
    </>
  );
};
