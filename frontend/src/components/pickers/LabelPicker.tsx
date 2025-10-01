import type { Label } from '../../types';

interface LabelPickerProps {
  labels: Label[];
  selectedIds: string[];
  onToggle: (labelId: string) => void;
}

export const LabelPicker = ({ labels, selectedIds, onToggle }: LabelPickerProps) => (
  <div className="space-y-2" role="group" aria-label="اختيار التصنيفات">
    {labels.map((label) => {
      const selected = selectedIds.includes(label.id);
      return (
        <button
          key={label.id}
          type="button"
          onClick={() => onToggle(label.id)}
          className="flex w-full items-center justify-between rounded-lg px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
          style={{ backgroundColor: selected ? label.color : 'rgba(30,41,59,0.7)' }}
          aria-pressed={selected}
        >
          <span>{label.name}</span>
          {selected && <span>✓</span>}
        </button>
      );
    })}
  </div>
);
