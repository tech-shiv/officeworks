import { Link } from '@inertiajs/react';
import { Product } from '@/types/catalogue';

function formatUsd(value: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
}

export default function ProductCard({ product }: { product: Product }) {
    return (
        <Link
            href={`/products/${product.id}`}
            className="flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:shadow-md"
        >
            <div className="flex h-40 items-center justify-center bg-white p-4">
                <img
                    src={product.image}
                    alt={product.title}
                    className="h-full object-contain"
                />
            </div>
            <div className="flex flex-1 flex-col gap-1 border-t border-slate-100 p-4">
                <h3 className="line-clamp-2 text-sm font-medium text-slate-900">
                    {product.title}
                </h3>
                <p className="text-sm text-slate-500 capitalize">
                    {product.category}
                </p>
                <div className="mt-auto flex items-center justify-between pt-2">
                    <span className="font-semibold text-slate-900">
                        {formatUsd(product.price)}
                    </span>
                    <span className="text-xs text-slate-500">
                        ★ {product.rating.rate.toFixed(1)} (
                        {product.rating.count})
                    </span>
                </div>
            </div>
        </Link>
    );
}
