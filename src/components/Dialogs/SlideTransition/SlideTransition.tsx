import { ReactElement, Ref, forwardRef } from "react";
import { type TransitionProps } from '@mui/material/transitions';
import Slide from '@mui/material/Slide';

const SlideTransition = forwardRef((
  props: TransitionProps & {
    children: ReactElement<any, any>;
  },
  ref: Ref<unknown>,
) => <Slide direction="up" ref={ref} {...props} />
);
export default SlideTransition;