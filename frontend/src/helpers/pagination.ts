export interface DataTableOptions {
  page: number
  itemsPerPage: number
}

export interface ApiPaginationParams {
  page: number
  per_page: number
}

export function toPaginationParams (options: DataTableOptions): ApiPaginationParams {
  return {
    page: options.page,
    per_page: options.itemsPerPage,
  }
}
