import { FetchBaseQueryError } from "@/types/FetchBaseQueryError";

export const logError = (fetchError: FetchBaseQueryError) => {
  if (!fetchError) return;
  if (hasApiError(fetchError)) {
    const { error, stack } = fetchError.data;
    console.log(error);
    if (stack) console.log(stack);
    return;
  }
  if ('error' in fetchError) {
    console.error(
      fetchError.status,
      fetchError.error,
      fetchError.data
    );
    return;
  }
  console.error(fetchError.status, fetchError.data);
}


const hasApiError = (error: FetchBaseQueryError):
  error is FetchBaseQueryError & { data: { error: any, stack?: any } } =>
  !!error.data &&
  (typeof error.data === 'object') &&
  ('error' in error.data);
