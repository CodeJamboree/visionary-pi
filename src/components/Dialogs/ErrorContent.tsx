import { FC } from "react";
import DialogContent from "@mui/material/DialogContent";
import { parseErrorMessage } from "@/utils/parseErrorMessage";

export const ErrorContent: FC<{
  error?: unknown
}> = ({
  error
}) => {
    if (!error) return null;
    return <DialogContent>{parseErrorMessage(error)}</DialogContent>
  }

