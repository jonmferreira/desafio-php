export function unmaskCpf (value: string): string {
  return value.replace(/\D/g, '')
}

export function formatCpf (value: string): string {
  const digits = unmaskCpf(value).slice(0, 11)
  return digits
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}
