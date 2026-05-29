import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ExportController::csv
* @see app/Http/Controllers/ExportController.php:12
* @route '/export/csv'
*/
export const csv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: csv.url(options),
    method: 'get',
})

csv.definition = {
    methods: ["get","head"],
    url: '/export/csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ExportController::csv
* @see app/Http/Controllers/ExportController.php:12
* @route '/export/csv'
*/
csv.url = (options?: RouteQueryOptions) => {
    return csv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExportController::csv
* @see app/Http/Controllers/ExportController.php:12
* @route '/export/csv'
*/
csv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: csv.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ExportController::csv
* @see app/Http/Controllers/ExportController.php:12
* @route '/export/csv'
*/
csv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: csv.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ExportController::pdf
* @see app/Http/Controllers/ExportController.php:46
* @route '/export/pdf'
*/
export const pdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/export/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ExportController::pdf
* @see app/Http/Controllers/ExportController.php:46
* @route '/export/pdf'
*/
pdf.url = (options?: RouteQueryOptions) => {
    return pdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExportController::pdf
* @see app/Http/Controllers/ExportController.php:46
* @route '/export/pdf'
*/
pdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ExportController::pdf
* @see app/Http/Controllers/ExportController.php:46
* @route '/export/pdf'
*/
pdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(options),
    method: 'head',
})

const ExportController = { csv, pdf }

export default ExportController