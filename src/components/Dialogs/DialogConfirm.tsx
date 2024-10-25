import { FC } from 'react';
import DialogInputs from './DialogInputs';

interface DialogConfirmProps {
  title?: string,
  content?: string,
  open: boolean,
  isBusy?: boolean,
  onCancel: () => void,
  onSubmit: () => void,
  okLabel?: string,
  cancelLabel?: string,
  error?: unknown
};

export const DialogConfirm: FC<DialogConfirmProps> = ({
  title = 'Confirm',
  content = 'Are you sure?',
  open = false,
  onCancel = () => { },
  onSubmit = () => { },
  okLabel = "OK",
  cancelLabel = "Cancel",
  isBusy = false,
  error = undefined
}) => {

  const handleSubmit = () => onSubmit();

  return <DialogInputs
    title={title}
    content={content}
    open={open}
    onCancel={onCancel}
    onSubmit={handleSubmit}
    okLabel={okLabel}
    cancelLabel={cancelLabel}
    isBusy={isBusy}
    error={error} />
}