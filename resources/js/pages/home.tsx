import { Head } from '@inertiajs/react';
import CatalogueLayout from '@/layouts/CatalogueLayout';
import ProductGrid from '@/components/ProductGrid';
import { Product } from '@/types/catalogue';

export default function Home({ featured }: { featured: Product[] }) {
    return (
        <CatalogueLayout>
            <Head title="Home" />
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">
                Top rated products
            </h1>
            <ProductGrid products={featured} />
        </CatalogueLayout>
    );
}
