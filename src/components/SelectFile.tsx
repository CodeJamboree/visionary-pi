import Button from "@mui/material/Button";
import UploadFileIcon from '@mui/icons-material/UploadFile';
import { ChangeEventHandler, FC } from "react";

const SelectFile: FC<{ onChange: (file: File) => void }> = ({
  onChange
}) => {
  const handleUpload: ChangeEventHandler<HTMLInputElement> = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    onChange(file);
  }

  return <Button variant="contained" component="label" startIcon={<UploadFileIcon />}>
    Upload
    <input type="file" hidden onChange={handleUpload} />
  </Button>

}
export default SelectFile;
