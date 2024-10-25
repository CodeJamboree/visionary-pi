import { FC } from "react";
import SelectFile from "@/components/SelectFile";

const UploadMediaButton: FC = () => {
  const handleChange = (file: File) => {
    console.log('i want to upload', file);
  }
  return <SelectFile onChange={handleChange} />
}
export default UploadMediaButton;
