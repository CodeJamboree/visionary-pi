import CircularProgress from '@mui/material/CircularProgress';
import { FC } from 'react';

export const Spinner: FC<{ isBusy: boolean }> = ({ isBusy }) =>
  isBusy ? <CircularProgress color="secondary" size={20} /> : null
