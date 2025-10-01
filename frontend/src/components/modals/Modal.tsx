import { Dialog, DialogPanel, Transition } from '@headlessui/react';
import { Fragment, type ReactNode } from 'react';

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
}

export const Modal = ({ open, onClose, title, children }: ModalProps) => (
  <Transition appear show={open} as={Fragment}>
    <Dialog as="div" className="relative z-50" onClose={onClose} dir="rtl">
      <Transition.Child
        as={Fragment}
        enter="ease-out duration-300"
        enterFrom="opacity-0"
        enterTo="opacity-100"
        leave="ease-in duration-200"
        leaveFrom="opacity-100"
        leaveTo="opacity-0"
      >
        <div className="fixed inset-0 bg-black/60" />
      </Transition.Child>

      <div className="fixed inset-0 overflow-y-auto">
        <div className="flex min-h-full items-center justify-center p-4">
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-300"
            enterFrom="opacity-0 scale-95"
            enterTo="opacity-100 scale-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100 scale-100"
            leaveTo="opacity-0 scale-95"
          >
            <DialogPanel className="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-slate-900 p-6 text-right align-middle text-slate-100 shadow-xl transition-all">
              <Dialog.Title className="text-lg font-medium text-slate-100">{title}</Dialog.Title>
              <div className="mt-4 space-y-4">{children}</div>
            </DialogPanel>
          </Transition.Child>
        </div>
      </div>
    </Dialog>
  </Transition>
);
