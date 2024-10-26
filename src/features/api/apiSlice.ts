import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import emoji from '@lewismoten/emoji';
import { injectReducer } from '@/app/injectReducer';

import { logError } from './logError';

const baseUrl = 'http://kiosk.local/api';

export const apiSlice = createApi({
  reducerPath: `api ${emoji.wireless}` as const,
  baseQuery: async (args, api, extraOptions) => {
    const baseQuery = fetchBaseQuery({
      baseUrl,
      prepareHeaders(headers) {
        if (!headers.has('Content-Type'))
          headers.set('Content-Type', 'application/json');
        else if (headers.get('Content-Type') === 'multipart/form-data') {
          headers.delete('Content-Type');
        }
        return headers;
      }
    });
    let result = await baseQuery(args, api, extraOptions);

    if (result.error) {
      logError(result.error);
    }
    return result;
  },
  endpoints: _build => ({})
});

injectReducer(
  apiSlice.reducerPath,
  apiSlice.reducer,
  apiSlice.middleware
);
