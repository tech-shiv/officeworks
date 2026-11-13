import { Product } from '@/types/catalogue';
import ProductCard from '@/components/ProductCard';

export default function ProductGrid({
    products,
    emptyMessage,
}: {
    products: Product[];
    emptyMessage?: string;
}) {
    if (products.length === 0) {
        return (
            <p className="text-sm text-slate-500">
                {emptyMessage ?? 'No products found.'}
            </p>
        );
    }

    return (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            {products.map((product) => (
                <ProductCard key={product.id} product={product} />
            ))}
        </div>
    );
}
