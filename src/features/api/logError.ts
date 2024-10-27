import { FetchBaseQueryError } from "@/types/FetchBaseQueryError";
import { parseErrorMessage } from "@/utils/parseErrorMessage";
export const logError = (fetchError: FetchBaseQueryError) => {
  if (!fetchError) return;
  if (hasApiError(fetchError)) {
    const { error, stack } = fetchError.data;
    console.log(error);
    if (stack) console.log(stack);
    return;

  }
  const message = parseErrorMessage(fetchError);
  if (message && message !== '') console.error(message);
}


const hasApiError = (error: FetchBaseQueryError):
  error is FetchBaseQueryError & { data: { error: any, stack?: any } } =>
  !!error.data &&
  (typeof error.data === 'object') &&
  ('error' in error.data);
