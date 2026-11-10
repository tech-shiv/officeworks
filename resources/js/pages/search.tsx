import { Head, Link } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import CatalogueLayout from '@/layouts/CatalogueLayout';
import ProductGrid from '@/components/ProductGrid';
import { Product } from '@/types/catalogue';

export default function Search({
    query,
    results,
}: {
    query: string;
    results: Product[];
}) {
    const [value, setValue] = useState(query);

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        window.location.href = `/search?q=${encodeURIComponent(value)}`;
    }

    return (
        <CatalogueLayout>
            <Head title={query ? `Search: ${query}` : 'Search'} />

            <form
                onSubmit={handleSubmit}
                role="search"
                className="mb-6 flex max-w-md gap-2"
            >
                <label htmlFor="page-search" className="sr-only">
                    Search products
                </label>
                <input
                    id="page-search"
                    type="search"
                    value={value}
                    onChange={(e) => setValue(e.target.value)}
                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                />
                <button
                    type="submit"
                    className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Search
                </button>
            </form>

            {query ? (
                <>
                    <h1 className="mb-6 text-lg text-slate-700">
                        {results.length} result{results.length === 1 ? '' : 's'}{' '}
                        for <span className="font-semibold">"{query}"</span>
                    </h1>
                    <ProductGrid
                        products={results}
                        emptyMessage="No products matched your search."
                    />
                </>
            ) : (
                <p className="text-sm text-slate-500">
                    Enter a search term above, or browse the{' '}
                    <Link href="/" className="text-blue-600 underline">
                        home page
                    </Link>
                    .
                </p>
            )}
        </CatalogueLayout>
    );
}
