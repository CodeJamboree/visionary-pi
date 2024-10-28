import UploadButton from "@/features/media/UploadButton";
import MediaList from "@/features/media/MediaList";
import DeleteButton from "@/features/media/DeleteButton";

const Media = () => {

  return (
    <>
      <UploadButton />
      <DeleteButton />
      <MediaList />

    </>
  );
};

export default Media;