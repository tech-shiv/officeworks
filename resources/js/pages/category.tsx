import { Head } from '@inertiajs/react';
import CatalogueLayout from '@/layouts/CatalogueLayout';
import ProductGrid from '@/components/ProductGrid';
import { Product } from '@/types/catalogue';

interface CategoryPageProps {
    category: string;
    topRated: Product[];
    additional: Product[];
}

export default function Category({
    category,
    topRated,
    additional,
}: CategoryPageProps) {
    return (
        <CatalogueLayout>
            <Head title={category} />
            <h1 className="mb-6 text-2xl font-semibold text-slate-900 capitalize">
                {category}
            </h1>

            <section className="mb-10">
                <h2 className="mb-4 text-lg font-medium text-slate-800">
                    Top rated
                </h2>
                <ProductGrid
                    products={topRated}
                    emptyMessage="No top rated products in this category yet."
                />
            </section>

            <section>
                <h2 className="mb-4 text-lg font-medium text-slate-800">
                    More in {category}
                </h2>
                <ProductGrid
                    products={additional}
                    emptyMessage="No additional products in this category."
                />
            </section>
        </CatalogueLayout>
    );
}
