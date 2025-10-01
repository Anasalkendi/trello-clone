import 'react-quill/dist/quill.snow.css';
import dynamicImport from '../../utils/dynamicImport';
import { useMemo } from 'react';

const ReactQuill = dynamicImport(() => import('react-quill'));

interface RichTextEditorProps {
  value: string;
  onChange: (value: string) => void;
}

export const RichTextEditor = ({ value, onChange }: RichTextEditorProps) => {
  const modules = useMemo(
    () => ({
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link', 'image'],
        ['clean']
      ]
    }),
    []
  );

  return (
    <div className="rich-text-editor" dir="rtl">
      <ReactQuill value={value} onChange={onChange} modules={modules} theme="snow" />
    </div>
  );
};
