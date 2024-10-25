import { FetchBaseQueryError } from "@/types/FetchBaseQueryError";
import { SerializedError } from "@/types/SerializedError";

export const parseErrorMessage = (error: unknown): string => {
  if (error === undefined) return '';
  if (error === null) return '';
  if (typeof error === 'string') return error;
  if (typeof error !== 'object') return 'Error';

  if (isDataError(error)) return error.data.error;

  if (isFetchError(error)) {
    if ('error' in error && typeof error.error === 'string') {
      if (error.error.startsWith('TypeError: ')) {
        return error.error.substring(11);
      }
      return error.error;
    }
    let message: string | number = error.status;
    switch (error.status) {
      case 'CUSTOM_ERROR':
        message = 'Error';
        break;
      case 'FETCH_ERROR':
        message = 'API call failed';
        break;
      case 'PARSING_ERROR':
        message = 'Unable to parse API response';
        break;
      case 'TIMEOUT_ERROR':
        message = 'Timed out';
        break;
      default:
        message = `Fetch Error: ${error.status}`;
    }
    return message;
  }

  const { name, code, message } = error as SerializedError;

  return `${name ?? 'Error'}${code ? `[${code}]` : ''}: ${message}`;

}
const isDataError = (error: unknown): error is { data: { error: string } } =>
  !!error &&
  typeof error === 'object' &&
  'data' in error &&
  !!error.data &&
  typeof error.data === 'object' &&
  'error' in error.data &&
  typeof error.data.error === 'string';

const isFetchError = (error: unknown): error is FetchBaseQueryError => {
  if (typeof error !== 'object') return false;
  if (error === null) return false;
  if (!('data' in error)) return false;
  if (!('status' in error)) return false;
  if (typeof error.status !== 'number' && typeof error.status !== 'string') return false;
  if (typeof error.status === 'string' && ![
    'FETCH_ERROR', 'PARSING_ERROR', 'TIMEOUT_ERROR', 'CUSTOM_ERROR'
  ].includes(error.status)) return false;
  if (typeof error.status === 'string' &&
    (!('error' in error) || typeof error.error !== 'string'))
    return false;
  return true;
}