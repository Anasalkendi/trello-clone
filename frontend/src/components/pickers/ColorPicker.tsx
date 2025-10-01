interface ColorPickerProps {
  value?: string;
  onChange: (color: string) => void;
}

const COLORS = ['#F97316', '#3B82F6', '#10B981', '#A855F7', '#EC4899', '#6366F1'];

export const ColorPicker = ({ value, onChange }: ColorPickerProps) => (
  <div className="flex flex-wrap gap-3" role="radiogroup" aria-label="اختيار اللون">
    {COLORS.map((color) => (
      <button
        key={color}
        type="button"
        onClick={() => onChange(color)}
        className="h-10 w-10 rounded-full border-4 border-transparent transition hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
        style={{ backgroundColor: color, borderColor: value === color ? '#22d3ee' : 'transparent' }}
        aria-pressed={value === color}
        aria-label={`لون ${color}`}
      />
    ))}
  </div>
);
