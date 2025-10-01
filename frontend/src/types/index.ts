export interface UserSummary {
  id: string;
  name: string;
  avatarUrl?: string;
}

export interface Label {
  id: string;
  name: string;
  color: string;
}

export interface ChecklistItem {
  id: string;
  title: string;
  completed: boolean;
}

export interface Checklist {
  id: string;
  title: string;
  items: ChecklistItem[];
}

export interface Comment {
  id: string;
  body: string;
  author: UserSummary;
  createdAt: string;
  mentions: string[];
}

export interface Attachment {
  id: string;
  name: string;
  url: string;
  uploadedAt: string;
}

export interface Card {
  id: string;
  title: string;
  description: string;
  assignees: UserSummary[];
  watchers: UserSummary[];
  labels: Label[];
  dueDate?: string;
  checklists: Checklist[];
  comments: Comment[];
  attachments: Attachment[];
}

export interface BoardList {
  id: string;
  title: string;
  cardIds: string[];
}

export interface Board {
  id: string;
  name: string;
  description?: string;
  backgroundColor?: string;
  lists: BoardList[];
  cards: Record<string, Card>;
  labels: Label[];
  members: UserSummary[];
}

export interface ProjectSummary {
  id: string;
  name: string;
  boardCount: number;
  updatedAt: string;
  owner: UserSummary;
}

export interface FilterState {
  labelIds: string[];
  assigneeIds: string[];
  watcherIds: string[];
  searchTerm: string;
  hideCompletedChecklists: boolean;
}
