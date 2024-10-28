import { apiSlice } from "@/features/api/apiSlice";
import { toQueryParams } from "../../utils/toQueryParams";

export enum MediaTypes {
  image = 'image',
  video = 'video',
  audio = 'audio',
  other = 'other',
  unknown = 'unknown'
}
export interface MediaListItem {
  id: number,
  url: string,
  thumbnailUrl?: string,
  displayName: string,
  dimesions?: string,
  duration?: number,
  createdAt: Date,
  mediaType: MediaTypes,
  hasAudio: boolean
}
interface MediaList {
  total: number,
  rows: MediaListItem[]
}
interface MediaListRaw {
  total: number,
  rows: MediaListItemRaw[]
}
interface MediaListItemRaw {
  id: number,
  url: string,
  thumbnailUrl?: string,
  displayName: string,
  dimesions?: string,
  duration?: number,
  createdAt: number,
  mediaType: MediaTypes,
  hasAudio: boolean
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
      transformResponse: ({ total, rows }: MediaListRaw) => ({
        total,
        rows: rows.map(item => ({
          ...item,
          createdAt: new Date(item.createdAt * 1000)
        }))
      })
    })
  })
});

export const {
  useUploadFileMutation,
  useListQuery
} = mediaApi;

export default mediaApi;