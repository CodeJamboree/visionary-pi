import { apiSlice } from "@/features/api/apiSlice";
import { toQueryParams } from "../../utils/toQueryParams";

export interface MediaListItem {
  id: number,
  url: string,
  displayName: string,
  width?: number,
  height?: number,
  duration: number,
  fileFormat: string,
  audioFormat: string,
  videoFormat: string,
  createdAt: number
}

interface MediaList {
  total: number,
  rows: MediaListItem[]
}

export interface MediaListParams {
  offset?: number,
  limit?: number
}
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
    }),
    list: build.query<MediaList, MediaListParams>({
      query: (params) => {
        return ({
          url: `/media/files/list${toQueryParams(params)}`,
          method: 'GET'
        })
      },
    })
  })
});

export const {
  useUploadFileMutation,
  useListQuery
} = mediaApi;

export default mediaApi;