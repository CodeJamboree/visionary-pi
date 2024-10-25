import { FC, HTMLInputTypeAttribute } from 'react';
import DialogInputs from './DialogInputs';

interface DialogInputProps {
  title?: string,
  content?: string,
  label?: string,
  defaultValue?: string,
  type?: HTMLInputTypeAttribute,
  open: boolean,
  isBusy?: boolean,
  onCancel: () => void,
  onSubmit: (value: string) => void,
  onUpload?: (value: File) => void,
  okLabel?: string,
  cancelLabel?: string,
  error?: unknown
};

export const DialogInput: FC<DialogInputProps> = ({
  title = 'Input',
  content = 'Please enter a value.',
  label = 'Value',
  type = '',
  defaultValue = '',
  open = false,
  onCancel = () => { },
  onSubmit = () => { },
  onUpload = () => { },
  okLabel = "OK",
  cancelLabel = "Cancel",
  isBusy = false,
  error = undefined
}) => {

  const handleSubmit = (values: Record<string, string>) => {
    onSubmit(values.value);
  }
  const handleUpload = (values: Record<string, string | File>) => {
    onUpload(values.value as File);
  }

  return <DialogInputs
    title={title}
    content={content}
    open={open}
    inputs={[
      { name: 'value', label, type, defaultValue }
    ]}
    onCancel={onCancel}
    onSubmit={handleSubmit}
    onUpload={handleUpload}
    okLabel={okLabel}
    cancelLabel={cancelLabel}
    isBusy={isBusy}
    error={error} />
}