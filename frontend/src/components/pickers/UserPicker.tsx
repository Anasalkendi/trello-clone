import type { UserSummary } from '../../types';

interface UserPickerProps {
  users: UserSummary[];
  selectedIds: string[];
  onToggle: (userId: string) => void;
  label: string;
}

export const UserPicker = ({ users, selectedIds, onToggle, label }: UserPickerProps) => (
  <fieldset className="space-y-2">
    <legend className="text-sm font-medium text-slate-300">{label}</legend>
    <div className="grid gap-2">
      {users.map((user) => {
        const selected = selectedIds.includes(user.id);
        return (
          <button
            key={user.id}
            type="button"
            className="flex items-center justify-between rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm hover:border-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
            onClick={() => onToggle(user.id)}
            aria-pressed={selected}
          >
            <span className="flex items-center gap-3">
              {user.avatarUrl ? (
                <img src={user.avatarUrl} alt="" className="h-8 w-8 rounded-full" />
              ) : (
                <span className="grid h-8 w-8 place-items-center rounded-full bg-slate-700 text-xs">
                  {user.name.slice(0, 2)}
                </span>
              )}
              <span>{user.name}</span>
            </span>
            {selected && <span className="text-primary">✓</span>}
          </button>
        );
      })}
    </div>
  </fieldset>
);
