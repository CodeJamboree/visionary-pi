export const toQueryParams = (params: object, prefix: boolean = true): string => {
  const serialized: Record<string, string> = Object.fromEntries(
    Object.entries(params)
      .map(serializeEntry)
      .filter(([, value]) => value !== '')
      .sort(([key1], [key2]) => key1.localeCompare(key2))
  );
  const text = new URLSearchParams(serialized).toString();
  if (text.length === 0) return text;
  return prefix ? `?${text}` : text;
}

function serializeEntry([key, value]: [string, any]): [string, string] {
  if (isSerializable(value)) {
    return [key, serializeValue(value)];
  }
  if (typeof value !== 'object') return [key, ''];

  if (Array.isArray(value)) {
    if (value.length === 0) return [key, ''];
    if (
      value.every(isSerializable) &&
      !value.filter(isString).some(v => v.includes(','))
    ) {
      return [key, value.map(serializeValue).join(',')]
    }
  }
  if (Object.keys(value).length === 0) return [key, ''];
  return [key, serializeValue(value)];
}
const isString = (value: any): value is string => typeof value === 'string';

const isSerializable = (value: any) => {
  switch (typeof value) {
    case 'object': return value === null || value instanceof Date;
    case 'undefined':
    case 'string':
    case 'bigint':
    case 'boolean':
    case 'number':
      return true;
    default:
      return false;
  }
}
const serializeValue = (value: any): string => {
  switch (typeof value) {
    case 'string': return value.trim();
    case 'undefined': return '';
    case 'object':
      if (value === null) return '';
      if (value instanceof Date) return value.toISOString();
      return JSON.stringify(value);
    default:
      return value.toString();
  }
}