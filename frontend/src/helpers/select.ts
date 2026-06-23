export interface SelectOption {
  value: number | string
  text: string
}

export function toSelectOptions<T> (
  items: T[],
  valueKey: keyof T,
  textKey: keyof T,
): SelectOption[] {
  return items.map(item => ({
    value: item[valueKey] as unknown as number | string,
    text: item[textKey] as unknown as string,
  }))
}
