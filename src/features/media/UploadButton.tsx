import { FC, useEffect } from "react";
import SelectFile from "@/components/SelectFile";
import { useUploadFileMutation } from "./mediaApi";

const UploadButton: FC = () => {
  const [uploadFile, {
    isError,
    error,
    isLoading
  }] = useUploadFileMutation();

  useEffect(() => {
    if (!isError) return;
    console.log(error);
  }, [isError, error])

  const handleChange = (file: File) => {
    uploadFile(file);
  }
  return <SelectFile isUploading={isLoading} onChange={handleChange} />
}
export default UploadButton;
