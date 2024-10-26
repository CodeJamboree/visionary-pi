import { apiSlice } from "@/features/api/apiSlice";

const mediaApi = apiSlice.injectEndpoints({
  endpoints: (build) => ({
    uploadFile: build.mutation<void, File>({
      query: (file) => {
        const formData = new FormData();
        formData.append('file', file);
        return ({
          url: `/media/files/upload`,
          method: 'POST',
          body: formData,
          headers: {
            'Content-Type': `multipart/form-data`
          }
        })
      },
    })
  })
});

export const {
  useUploadFileMutation
} = mediaApi;

export default mediaApi;