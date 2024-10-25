import { FC, FormEvent, HTMLInputTypeAttribute, MouseEventHandler, useMemo } from 'react';

import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';

import { Spinner } from '../Spinner';
import { ErrorContent } from '../ErrorContent';
import SlideTransition from '../SlideTransition';

interface Input<Name extends string> {
  name: Name,
  label?: string,
  defaultValue?: string,
  type?: HTMLInputTypeAttribute,
  autoComplete?: string,
  required?: boolean,
  autoFocus?: boolean
}
interface DialogInputsProps<T extends string[]> {
  title?: string,
  content?: string,
  inputs?: Input<T[number]>[],
  open: boolean,
  isBusy?: boolean,
  onCancel: () => void,
  onSubmit: (value: Record<T[number], string>) => void,
  onUpload?: (value: Record<T[number], string | File>) => void,
  okLabel?: string,
  cancelLabel?: string,
  error?: unknown
};

const DialogInputs: FC<DialogInputsProps<string[]>> = ({
  title = 'Input',
  content = 'Please enter a value.',
  inputs = [],
  open = false,
  onCancel = () => { },
  onSubmit = () => { },
  onUpload = () => { },
  okLabel = "OK",
  cancelLabel = "Cancel",
  isBusy = false,
  error = undefined
}) => {

  const handleClose: MouseEventHandler<HTMLElement> = (event) => {
    event.preventDefault();
    event.stopPropagation();
    onCancel();
  }

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    event.stopPropagation();
    const data = new FormData(event.currentTarget);
    const currentValue = inputs.reduce(
      (values, input) => (
        {
          [input.name]: data.get(input.name)!,
          ...values
        }), {}) as Record<string, string | File>
    const hasFiles = Object.values(currentValue)
      .some(value => typeof value !== 'string');

    if (hasFiles) {
      onUpload(currentValue);
    } else {
      onSubmit(currentValue as Record<string, string>);
    }
  }
  const autoFocusIndex = useMemo(() => {
    const focusable = inputs.filter(input => input.type !== 'hidden');
    if (focusable.length === 0) return -1;
    let focused = focusable.find(input => input.autoFocus);
    return focused ? inputs.indexOf(focused) : inputs.indexOf(focusable[0]);
  }, [inputs]);

  return (
    <Dialog
      onClose={handleClose}
      onClick={e => { e.stopPropagation(); }}
      TransitionComponent={SlideTransition}
      open={open}
      PaperProps={{ component: 'form', onSubmit: handleSubmit }}
      aria-describedby="dialog-content"
      aria-labelledby='dialog-title'
    >
      <DialogTitle id="dialog-title">{title}</DialogTitle>
      <DialogContent id="dialog-content">{content}</DialogContent>
      {inputs.map((input, index) =>
        <TextField
          key={input.name}
          name={input.name}
          label={input.label}
          sx={input.type === 'hidden' ? { display: 'none' } : {}}
          type={input.type === 'hidden' ? 'text' : input.type}
          defaultValue={input.defaultValue}
          autoComplete={input.autoComplete}
          autoFocus={autoFocusIndex === index}
          required={input.required}
          margin="dense"
          fullWidth
          variant='standard' />
      )}
      <ErrorContent error={error} />
      <DialogActions>
        <Button variant="outlined" onClick={handleClose}>{cancelLabel}</Button>
        <Button
          disabled={isBusy}
          variant="contained"
          color="primary"
          type="submit"
          autoFocus={autoFocusIndex === -1}
        >
          {okLabel}
          <Spinner isBusy={isBusy} />
        </Button>
      </DialogActions>
    </Dialog>
  )
}
export default DialogInputs;
