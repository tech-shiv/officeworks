import { Link, usePage } from '@inertiajs/react';
import { FormEvent, PropsWithChildren, useState } from 'react';

export default function CatalogueLayout({ children }: PropsWithChildren) {
    const { categories, name } = usePage().props;
    const [query, setQuery] = useState('');

    function handleSearch(e: FormEvent) {
        e.preventDefault();
        window.location.href = `/search?q=${encodeURIComponent(query)}`;
    }

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href="/"
                        className="text-lg font-semibold text-slate-900"
                    >
                        {name}
                    </Link>

                    <form
                        onSubmit={handleSearch}
                        role="search"
                        className="flex w-full max-w-sm gap-2 sm:w-auto"
                    >
                        <label htmlFor="search" className="sr-only">
                            Search products
                        </label>
                        <input
                            id="search"
                            type="search"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search products…"
                            className="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
                        />
                        <button
                            type="submit"
                            className="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <nav
                    aria-label="Categories"
                    className="mx-auto flex max-w-6xl flex-wrap gap-4 px-6 pb-3 text-sm"
                >
                    {categories.map((category) => (
                        <Link
                            key={category}
                            href={`/categories/${encodeURIComponent(category)}`}
                            className="text-slate-600 capitalize hover:text-blue-600"
                        >
                            {category}
                        </Link>
                    ))}
                </nav>
            </header>

            <main className="mx-auto max-w-6xl px-6 py-8">{children}</main>
        </div>
    );
}
