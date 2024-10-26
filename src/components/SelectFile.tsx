import Button from "@mui/material/Button";
import UploadFileIcon from '@mui/icons-material/UploadFile';
import { ChangeEventHandler, FC } from "react";
import CircularProgress from '@mui/material/CircularProgress';

const SelectFile: FC<{
  onChange: (file: File) => void,
  isUploading: boolean
}> = ({
  onChange,
  isUploading = false
}) => {
    const handleUpload: ChangeEventHandler<HTMLInputElement> = (event) => {
      const file = event.target.files?.[0];
      if (!file) return;
      onChange(file);
    }

    return <Button variant="contained" component="label" startIcon={
      isUploading ? <CircularProgress size={20} /> : <UploadFileIcon />
    }>
      Upload
      <input type="file" hidden onChange={handleUpload} />
    </Button>

  }
export default SelectFile;
