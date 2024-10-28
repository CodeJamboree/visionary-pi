import { FC, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { clearSelection, selectSelectedIds } from "./mediaSlice";
import Button from "@mui/material/Button";
import CircularProgress from "@mui/material/CircularProgress";
import DeleteIcon from '@mui/icons-material/Delete';
import { useDeleteMutation } from "./mediaApi";

const DeleteButton: FC = () => {
  const dispatch = useDispatch();
  const selectedIds = useSelector(selectSelectedIds);
  const [deleteFiles, { isLoading, isError }] = useDeleteMutation();
  const isDisabled = isLoading || selectedIds.length === 0;

  const handleClick = () => {
    deleteFiles(selectedIds);
  }
  useEffect(() => {
    if (isLoading) return;
    if (isError) return;
    if (selectedIds.length === 0) return;
    dispatch(clearSelection());
  }, [isLoading, isError])
  return <Button
    onClick={handleClick}
    variant="contained" disabled={isDisabled} component="label" startIcon={
      isLoading ? <CircularProgress size={20} /> : <DeleteIcon />
    }>
    Delete Selected
  </Button>
}
export default DeleteButton;
