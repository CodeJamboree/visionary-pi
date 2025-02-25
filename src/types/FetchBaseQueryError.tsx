export type FetchBaseQueryError = {
  status: number;
  data: unknown;
} | {
  status: "FETCH_ERROR";
  data: undefined;
  error: string;
} | {
  status: "PARSING_ERROR";
  originalStatus: number;
  data: string;
  error: string;
} | {
  status: "TIMEOUT_ERROR";
  data: undefined;
  error: string;
} | {
  status: "CUSTOM_ERROR";
  data: unknown;
  error: string;
};
